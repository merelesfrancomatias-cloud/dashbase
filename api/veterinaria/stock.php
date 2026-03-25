<?php
/**
 * Stock de medicamentos e insumos veterinarios.
 * GET    ?alerta=1          → ítems bajo stock mínimo
 * GET    ?id=X              → detalle + últimos movimientos
 * GET                       → listado completo
 * POST                      → crear ítem
 * PUT                       → editar ítem o registrar movimiento (?mov=1)
 * DELETE ?id=X              → baja lógica
 */
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();
$pdo    = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

try {

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = intval($_GET['id'] ?? 0);

    if ($id) {
        $st = $pdo->prepare("SELECT * FROM vet_stock WHERE id=? AND negocio_id=? AND activo=1");
        $st->execute([$id, $negocioId]);
        $item = $st->fetch(PDO::FETCH_ASSOC);
        if (!$item) { Response::error('Ítem no encontrado', 404); exit; }

        $stM = $pdo->prepare("SELECT * FROM vet_stock_movimientos WHERE item_id=? ORDER BY created_at DESC LIMIT 20");
        $stM->execute([$id]);
        $item['movimientos'] = $stM->fetchAll(PDO::FETCH_ASSOC);

        Response::success('ok', $item);
        exit;
    }

    // Alerta de stock bajo
    $soloAlerta = !empty($_GET['alerta']);
    $cat        = $_GET['categoria'] ?? '';

    $where  = ['negocio_id = ?', 'activo = 1'];
    $params = [$negocioId];

    if ($soloAlerta) { $where[] = 'stock_actual <= stock_minimo'; }
    if ($cat)        { $where[] = 'categoria = ?'; $params[] = $cat; }

    $st = $pdo->prepare("SELECT * FROM vet_stock WHERE " . implode(' AND ', $where) . " ORDER BY categoria, nombre");
    $st->execute($params);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    // KPIs globales siempre
    $stK = $pdo->prepare("SELECT
        COUNT(*) AS total,
        SUM(stock_actual <= stock_minimo) AS alertas,
        ROUND(SUM(stock_actual * precio_costo),2) AS valor_costo,
        ROUND(SUM(stock_actual * precio_venta),2) AS valor_venta
        FROM vet_stock WHERE negocio_id=? AND activo=1");
    $stK->execute([$negocioId]);
    $kpis = $stK->fetch(PDO::FETCH_ASSOC);

    Response::success('ok', ['items' => $items, 'kpis' => $kpis]);
    exit;
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?: [];
    $nombre = trim($d['nombre'] ?? '');
    if (!$nombre) { Response::error('Nombre requerido', 400); exit; }

    $st = $pdo->prepare("INSERT INTO vet_stock
        (negocio_id, nombre, descripcion, categoria, unidad, stock_actual, stock_minimo, precio_costo, precio_venta)
        VALUES (?,?,?,?,?,?,?,?,?)");
    $st->execute([
        $negocioId,
        $nombre,
        trim($d['descripcion'] ?? '') ?: null,
        trim($d['categoria']   ?? '') ?: null,
        trim($d['unidad']      ?? 'unidad'),
        max(0, (float)($d['stock_actual']  ?? 0)),
        max(0, (float)($d['stock_minimo']  ?? 0)),
        max(0, (float)($d['precio_costo']  ?? 0)),
        max(0, (float)($d['precio_venta']  ?? 0)),
    ]);
    $newId = $pdo->lastInsertId();

    // Si hay stock inicial, registrar entrada
    if ((float)($d['stock_actual'] ?? 0) > 0) {
        $pdo->prepare("INSERT INTO vet_stock_movimientos (negocio_id, item_id, tipo, cantidad, motivo) VALUES (?,?,?,?,?)")
            ->execute([$negocioId, $newId, 'entrada', (float)$d['stock_actual'], 'Stock inicial']);
    }

    Response::success('Ítem creado', ['id' => $newId], 201);
    exit;
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $d  = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = intval($d['id'] ?? 0);
    if (!$id) { Response::error('id requerido', 400); exit; }

    // Verificar pertenece al negocio
    $stV = $pdo->prepare("SELECT stock_actual FROM vet_stock WHERE id=? AND negocio_id=?");
    $stV->execute([$id, $negocioId]);
    $item = $stV->fetch(PDO::FETCH_ASSOC);
    if (!$item) { Response::error('Ítem no encontrado', 404); exit; }

    // Movimiento de stock (entrada/salida/ajuste)
    if (!empty($d['mov'])) {
        $tipo     = in_array($d['tipo'], ['entrada','salida','ajuste']) ? $d['tipo'] : 'salida';
        $cantidad = (float)($d['cantidad'] ?? 0);
        if ($cantidad <= 0) { Response::error('Cantidad inválida', 400); exit; }

        $delta = match($tipo) {
            'entrada' =>  $cantidad,
            'salida'  => -$cantidad,
            'ajuste'  => $cantidad - (float)$item['stock_actual'],
        };

        $nuevoStock = max(0, (float)$item['stock_actual'] + $delta);

        $pdo->prepare("UPDATE vet_stock SET stock_actual=? WHERE id=?")->execute([$nuevoStock, $id]);
        $pdo->prepare("INSERT INTO vet_stock_movimientos (negocio_id, item_id, tipo, cantidad, motivo, consulta_id) VALUES (?,?,?,?,?,?)")
            ->execute([$negocioId, $id, $tipo, $cantidad, trim($d['motivo'] ?? '') ?: null, intval($d['consulta_id'] ?? 0) ?: null]);

        Response::success('Movimiento registrado', ['stock_actual' => $nuevoStock]);
        exit;
    }

    // Edición de datos del ítem
    $allowed = ['nombre','descripcion','categoria','unidad','stock_minimo','precio_costo','precio_venta','activo'];
    $sets = []; $params = [];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $d)) {
            $sets[]   = "$f = ?";
            $params[] = $d[$f];
        }
    }
    if (!$sets) { Response::error('Sin cambios', 400); exit; }
    $params[] = $id;
    $pdo->prepare("UPDATE vet_stock SET " . implode(', ', $sets) . " WHERE id=?")->execute($params);

    Response::success('Ítem actualizado');
    exit;
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) { Response::error('id requerido', 400); exit; }

    $st = $pdo->prepare("UPDATE vet_stock SET activo=0 WHERE id=? AND negocio_id=?");
    $st->execute([$id, $negocioId]);
    if ($st->rowCount() === 0) { Response::error('Ítem no encontrado', 404); exit; }

    Response::success('Ítem eliminado');
    exit;
}

Response::error('Método no permitido', 405);

} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
