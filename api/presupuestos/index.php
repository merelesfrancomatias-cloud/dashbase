<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();

$db     = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

/* ── helpers ── */
function nextNumero(PDO $db, int $nid): string {
    $stmt = $db->prepare("SELECT COUNT(*)+1 FROM presupuestos WHERE negocio_id=?");
    $stmt->execute([$nid]);
    $n = (int)$stmt->fetchColumn();
    return 'PRES-' . str_pad($n, 4, '0', STR_PAD_LEFT);
}

function calcTotales(array $items): array {
    $sub = 0;
    foreach ($items as $it) {
        $sub += $it['precio_unit'] * $it['cantidad'] * (1 - ($it['descuento_item'] ?? 0) / 100);
    }
    return $sub;
}

/* ══════════════════════════════════════
   GET — listar / detalle
══════════════════════════════════════ */
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id) {
        // Detalle con items
        $stmt = $db->prepare("SELECT p.*, c.nombre as cli_nombre_bd, c.apellido as cli_apellido_bd, c.telefono as cli_tel_bd
            FROM presupuestos p
            LEFT JOIN clientes c ON c.id = p.cliente_id AND c.negocio_id = p.negocio_id
            WHERE p.id=? AND p.negocio_id=?");
        $stmt->execute([$id, $negocioId]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pres) { Response::error('Presupuesto no encontrado', 404); exit; }

        $stmtI = $db->prepare("SELECT * FROM presupuesto_items WHERE presupuesto_id=? ORDER BY id");
        $stmtI->execute([$id]);
        $pres['items'] = $stmtI->fetchAll(PDO::FETCH_ASSOC);

        Response::success('OK', $pres);
        exit;
    }

    // Listado con filtros
    $where  = ['p.negocio_id = ?'];
    $params = [$negocioId];

    if (!empty($_GET['estado'])) {
        $where[]  = 'p.estado = ?';
        $params[] = $_GET['estado'];
    }
    if (!empty($_GET['q'])) {
        $where[]  = '(p.numero LIKE ? OR p.cliente_nombre LIKE ?)';
        $q = '%' . $_GET['q'] . '%';
        $params[] = $q; $params[] = $q;
    }

    $sql = "SELECT p.id, p.numero, p.cliente_nombre, p.cliente_tel, p.fecha,
                   p.fecha_vencimiento, p.total, p.estado,
                   (SELECT COUNT(*) FROM presupuesto_items pi WHERE pi.presupuesto_id=p.id) as cant_items
            FROM presupuestos p
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.fecha_creacion DESC
            LIMIT 200";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estadísticas
    $stStats = $db->prepare("SELECT
        COUNT(*) as total,
        SUM(estado='borrador') as borradores,
        SUM(estado='enviado') as enviados,
        SUM(estado='aprobado') as aprobados,
        SUM(estado='rechazado') as rechazados,
        SUM(CASE WHEN estado='aprobado' THEN total ELSE 0 END) as monto_aprobado
        FROM presupuestos WHERE negocio_id=?");
    $stStats->execute([$negocioId]);
    $stats = $stStats->fetch(PDO::FETCH_ASSOC);

    Response::success('OK', ['presupuestos' => $rows, 'stats' => $stats]);
    exit;
}

/* ══════════════════════════════════════
   POST — crear
══════════════════════════════════════ */
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    // Si es conversión a venta
    if (($body['action'] ?? '') === 'convertir_venta') {
        $presId = (int)($body['presupuesto_id'] ?? 0);
        if (!$presId) { Response::error('ID requerido'); exit; }

        // Obtener presupuesto
        $stPres = $db->prepare("SELECT * FROM presupuestos WHERE id=? AND negocio_id=?");
        $stPres->execute([$presId, $negocioId]);
        $pres = $stPres->fetch(PDO::FETCH_ASSOC);
        if (!$pres) { Response::error('Presupuesto no encontrado'); exit; }

        $stItems = $db->prepare("SELECT * FROM presupuesto_items WHERE presupuesto_id=?");
        $stItems->execute([$presId]);
        $items = $stItems->fetchAll(PDO::FETCH_ASSOC);

        $db->beginTransaction();
        try {
            // Crear venta
            $stV = $db->prepare("INSERT INTO ventas (negocio_id, usuario_id, cliente_id, subtotal, descuento, total, metodo_pago, estado, observaciones)
                VALUES (?,?,?,?,?,?,?,?,?)");
            $stV->execute([$negocioId, $usuarioId,
                $pres['cliente_id'], $pres['subtotal'], $pres['descuento'], $pres['total'],
                'efectivo', 'completada', 'Convertido desde presupuesto ' . $pres['numero']]);
            $ventaId = $db->lastInsertId();

            foreach ($items as $it) {
                $stDV = $db->prepare("INSERT INTO detalle_ventas (venta_id, producto_id, nombre_producto, cantidad, precio_unit, descuento, subtotal)
                    VALUES (?,?,?,?,?,?,?)");
                $stDV->execute([$ventaId, $it['producto_id'], $it['descripcion'],
                    $it['cantidad'], $it['precio_unit'], $it['descuento_item'], $it['subtotal']]);
                // Descontar stock
                if ($it['producto_id']) {
                    $db->prepare("UPDATE productos SET stock = stock - ? WHERE id=? AND negocio_id=?")->execute([$it['cantidad'], $it['producto_id'], $negocioId]);
                }
            }

            // Marcar presupuesto como aprobado
            $db->prepare("UPDATE presupuestos SET estado='aprobado' WHERE id=?")->execute([$presId]);
            $db->commit();
            Response::success('Convertido a venta correctamente', ['venta_id' => $ventaId]);
        } catch (Exception $e) {
            $db->rollBack();
            Response::error('Error al convertir: ' . $e->getMessage());
        }
        exit;
    }

    // Crear presupuesto nuevo
    $clienteNombre = trim($body['cliente_nombre'] ?? '');
    $clienteTel    = trim($body['cliente_tel'] ?? '');
    $clienteId     = !empty($body['cliente_id']) ? (int)$body['cliente_id'] : null;
    $fecha         = $body['fecha'] ?? date('Y-m-d');
    $fechaVenc     = !empty($body['fecha_vencimiento']) ? $body['fecha_vencimiento'] : date('Y-m-d', strtotime('+15 days'));
    $notas         = trim($body['notas'] ?? '');
    $descuento     = (float)($body['descuento'] ?? 0);
    $items         = $body['items'] ?? [];

    if (empty($clienteNombre) && !$clienteId) { Response::error('Ingresá un cliente'); exit; }
    if (empty($items)) { Response::error('Agregá al menos un ítem'); exit; }

    // Si hay cliente_id, buscar nombre
    if ($clienteId && empty($clienteNombre)) {
        $stC = $db->prepare("SELECT CONCAT(nombre,' ',apellido) FROM clientes WHERE id=? AND negocio_id=?");
        $stC->execute([$clienteId, $negocioId]);
        $clienteNombre = $stC->fetchColumn() ?: '';
    }

    // Calcular totales
    $subtotal = 0;
    foreach ($items as &$it) {
        $it['cantidad']      = (float)($it['cantidad'] ?? 1);
        $it['precio_unit']   = (float)($it['precio_unit'] ?? 0);
        $it['descuento_item']= (float)($it['descuento_item'] ?? 0);
        $it['subtotal']      = round($it['precio_unit'] * $it['cantidad'] * (1 - $it['descuento_item'] / 100), 2);
        $subtotal           += $it['subtotal'];
    }
    unset($it);
    $total = $subtotal - $descuento;

    $numero = nextNumero($db, $negocioId);

    $db->beginTransaction();
    try {
        $stP = $db->prepare("INSERT INTO presupuestos (negocio_id, numero, cliente_id, cliente_nombre, cliente_tel, fecha, fecha_vencimiento, subtotal, descuento, total, estado, notas, creado_por)
            VALUES (?,?,?,?,?,?,?,?,?,?,'borrador',?,?)");
        $stP->execute([$negocioId, $numero, $clienteId, $clienteNombre, $clienteTel,
            $fecha, $fechaVenc, $subtotal, $descuento, $total, $notas, $usuarioId]);
        $presId = $db->lastInsertId();

        $stI = $db->prepare("INSERT INTO presupuesto_items (presupuesto_id, producto_id, descripcion, cantidad, precio_unit, descuento_item, subtotal) VALUES (?,?,?,?,?,?,?)");
        foreach ($items as $it) {
            $stI->execute([$presId, $it['producto_id'] ?? null, $it['descripcion'], $it['cantidad'], $it['precio_unit'], $it['descuento_item'], $it['subtotal']]);
        }

        $db->commit();
        Response::success('Presupuesto creado', ['id' => $presId, 'numero' => $numero]);
    } catch (Exception $e) {
        $db->rollBack();
        Response::error('Error al crear: ' . $e->getMessage());
    }
    exit;
}

/* ══════════════════════════════════════
   PUT — actualizar estado
══════════════════════════════════════ */
if ($method === 'PUT') {
    $body  = json_decode(file_get_contents('php://input'), true) ?? [];
    $id    = (int)($body['id'] ?? 0);
    $campo = $body['campo'] ?? 'estado';

    if (!$id) { Response::error('ID requerido'); exit; }

    $allowed_estados = ['borrador','enviado','aprobado','rechazado','vencido'];
    if ($campo === 'estado') {
        $nuevoEstado = $body['estado'] ?? '';
        if (!in_array($nuevoEstado, $allowed_estados)) { Response::error('Estado inválido'); exit; }
        $db->prepare("UPDATE presupuestos SET estado=? WHERE id=? AND negocio_id=?")
           ->execute([$nuevoEstado, $id, $negocioId]);
    }

    Response::success('Actualizado', null);
    exit;
}

/* ══════════════════════════════════════
   DELETE
══════════════════════════════════════ */
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { Response::error('ID requerido'); exit; }
    $db->prepare("DELETE FROM presupuestos WHERE id=? AND negocio_id=?")->execute([$id, $negocioId]);
    Response::success('Eliminado', null);
    exit;
}

Response::error('Método no permitido', 405);
