<?php
require_once __DIR__ . '/../bootstrap.php';
Middleware::cors(['GET','POST','PUT','DELETE']);
[$negocioId, $usuarioId] = Middleware::auth();

$method = $_SERVER['REQUEST_METHOD'];
$db     = (new Database())->getConnection();

// ── GET ───────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Detalle de una receta con sus ítems
    if ($id) {
        $s = $db->prepare("SELECT * FROM farmacia_recetas WHERE id=? AND negocio_id=?");
        $s->execute([$id, $negocioId]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if (!$row) Response::error('No encontrada', 404);
        $si = $db->prepare("SELECT ri.*, p.codigo_barras
            FROM farmacia_receta_items ri
            LEFT JOIN productos p ON p.id = ri.producto_id
            WHERE ri.receta_id=?");
        $si->execute([$id]);
        $row['items'] = $si->fetchAll(PDO::FETCH_ASSOC);
        Response::success('OK', $row);
    }

    // Listado con filtros
    $estado   = $_GET['estado']   ?? '';
    $q        = trim($_GET['q']   ?? '');
    $sql = "SELECT * FROM farmacia_recetas WHERE negocio_id=?";
    $params = [$negocioId];
    if ($estado) { $sql .= " AND estado=?"; $params[] = $estado; }
    if ($q)      { $sql .= " AND (paciente LIKE ? OR medico LIKE ? OR numero_receta LIKE ?)";
                   $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
    $sql .= " ORDER BY created_at DESC LIMIT 200";
    $s = $db->prepare($sql);
    $s->execute($params);
    Response::success('OK', $s->fetchAll(PDO::FETCH_ASSOC));
}

// ── POST ──────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    $db->beginTransaction();
    try {
        $s = $db->prepare("INSERT INTO farmacia_recetas
            (negocio_id,numero_receta,medico,matricula,paciente,dni_paciente,
             obra_social,nro_afiliado,fecha_emision,fecha_vencimiento,estado,notas,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $s->execute([$negocioId, $d['numero_receta']??null, $d['medico']??null,
            $d['matricula']??null, $d['paciente']??null, $d['dni_paciente']??null,
            $d['obra_social']??null, $d['nro_afiliado']??null,
            $d['fecha_emision']??null, $d['fecha_vencimiento']??null,
            $d['estado']??'pendiente', $d['notas']??null, $usuarioId]);
        $recetaId = $db->lastInsertId();
        $items = $d['items'] ?? [];
        $si = $db->prepare("INSERT INTO farmacia_receta_items
            (receta_id,producto_id,medicamento,presentacion,cantidad,indicaciones,dispensado)
            VALUES (?,?,?,?,?,?,?)");
        foreach ($items as $it) {
            if (empty($it['medicamento'])) continue;
            $si->execute([$recetaId, $it['producto_id']??null, $it['medicamento'],
                $it['presentacion']??null, (int)($it['cantidad']??1),
                $it['indicaciones']??null, 0]);
        }
        $db->commit();
        Response::success('Receta creada', ['id' => $recetaId], 201);
    } catch (Exception $e) {
        $db->rollBack();
        Response::error('Error al crear receta: ' . $e->getMessage(), 500);
    }
}

// ── PUT ───────────────────────────────────────────────────────────────────────
if ($method === 'PUT') {
    $d = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($d['id'])) Response::error('ID requerido', 422);
    $recetaId = (int)$d['id'];

    // Solo cambio de estado
    if (isset($d['estado']) && count($d) <= 2) {
        $db->prepare("UPDATE farmacia_recetas SET estado=? WHERE id=? AND negocio_id=?")
           ->execute([$d['estado'], $recetaId, $negocioId]);
        // Si se despacha, marcar todos los ítems como dispensados
        if ($d['estado'] === 'despachada') {
            $db->prepare("UPDATE farmacia_receta_items SET dispensado=1 WHERE receta_id=?")
               ->execute([$recetaId]);
        }
        Response::success('Estado actualizado');
    }

    // Edición completa
    $db->beginTransaction();
    try {
        $s = $db->prepare("UPDATE farmacia_recetas SET
            numero_receta=?,medico=?,matricula=?,paciente=?,dni_paciente=?,
            obra_social=?,nro_afiliado=?,fecha_emision=?,fecha_vencimiento=?,estado=?,notas=?
            WHERE id=? AND negocio_id=?");
        $s->execute([$d['numero_receta']??null, $d['medico']??null, $d['matricula']??null,
            $d['paciente']??null, $d['dni_paciente']??null, $d['obra_social']??null,
            $d['nro_afiliado']??null, $d['fecha_emision']??null, $d['fecha_vencimiento']??null,
            $d['estado']??'pendiente', $d['notas']??null, $recetaId, $negocioId]);
        // Reemplazar ítems
        if (isset($d['items'])) {
            $db->prepare("DELETE FROM farmacia_receta_items WHERE receta_id=?")->execute([$recetaId]);
            $si = $db->prepare("INSERT INTO farmacia_receta_items
                (receta_id,producto_id,medicamento,presentacion,cantidad,indicaciones,dispensado)
                VALUES (?,?,?,?,?,?,?)");
            foreach ($d['items'] as $it) {
                if (empty($it['medicamento'])) continue;
                $si->execute([$recetaId, $it['producto_id']??null, $it['medicamento'],
                    $it['presentacion']??null, (int)($it['cantidad']??1),
                    $it['indicaciones']??null, (int)($it['dispensado']??0)]);
            }
        }
        $db->commit();
        Response::success('Receta actualizada');
    } catch (Exception $e) {
        $db->rollBack();
        Response::error('Error: ' . $e->getMessage(), 500);
    }
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) Response::error('ID requerido', 422);
    $db->prepare("UPDATE farmacia_recetas SET estado='anulada' WHERE id=? AND negocio_id=?")
       ->execute([$id, $negocioId]);
    Response::success('Receta anulada');
}
