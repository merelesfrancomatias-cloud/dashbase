<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT']);
$method = $_SERVER['REQUEST_METHOD'];

try {
    [$negocioId, $usuarioId] = Middleware::auth();

    $database = new Database();
    $db = $database->getConnection();
    PlanGuard::requireActive($negocioId, $db);

    switch ($method) {
        case 'GET':
            if (isset($_GET['activa'])) {
                // Obtener caja activa del usuario
                $query = "SELECT c.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
                          FROM cajas c
                          INNER JOIN usuarios u ON c.usuario_id = u.id
                          WHERE c.usuario_id = :usuario_id AND c.estado = 'abierta'
                          ORDER BY c.fecha_apertura DESC
                          LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute([':usuario_id' => $usuarioId]);
                $caja = $stmt->fetch();

                if (!$caja) {
                    Response::json(['success' => false, 'message' => 'No hay caja abierta', 'data' => null], 200);
                }

                if ($caja) {
                    $ventasStmt = $db->prepare(
                        "SELECT COALESCE(SUM(total), 0) AS total_ventas FROM ventas WHERE caja_id = :caja_id AND estado = 'completada'"
                    );
                    $ventasStmt->execute([':caja_id' => $caja['id']]);
                    $ventasData = $ventasStmt->fetch();

                    $gastosStmt = $db->prepare(
                        "SELECT COALESCE(SUM(monto), 0) AS total_gastos FROM gastos WHERE caja_id = :caja_id"
                    );
                    $gastosStmt->execute([':caja_id' => $caja['id']]);
                    $gastosData = $gastosStmt->fetch();

                    $detalleStmt = $db->prepare(
                        "SELECT metodo_pago, COUNT(*) AS cantidad, COALESCE(SUM(total), 0) AS total
                         FROM ventas WHERE caja_id = :caja_id AND estado = 'completada'
                         GROUP BY metodo_pago"
                    );
                    $detalleStmt->execute([':caja_id' => $caja['id']]);
                    $detallePagos = $detalleStmt->fetchAll();

                    $caja['monto_ventas']   = $ventasData['total_ventas'];
                    $caja['monto_gastos']   = $gastosData['total_gastos'];
                    $caja['monto_esperado'] = $caja['monto_inicial'] + $ventasData['total_ventas'] - $gastosData['total_gastos'];
                    $caja['detalle_pagos']  = $detallePagos;

                    Response::success('Caja activa encontrada', $caja);
                }
            } elseif (isset($_GET['historial'])) {
                $limit = (int)($_GET['limit'] ?? 20);

                $sql    = "SELECT c.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
                           FROM cajas c
                           INNER JOIN usuarios u ON c.usuario_id = u.id
                           WHERE c.negocio_id = :negocio_id";
                $params = [':negocio_id' => $negocioId];

                if (!Auth::isAdmin()) {
                    $sql              .= " AND c.usuario_id = :usuario_id";
                    $params[':usuario_id'] = $usuarioId;
                }

                // LIMIT con PDO::PARAM_INT requiere bindValue; usamos un literal seguro
                $sql .= " ORDER BY c.fecha_apertura DESC LIMIT " . $limit;

                $stmt = $db->prepare($sql);
                $stmt->execute($params);

                Response::success('Historial de cajas', $stmt->fetchAll());
            } else {
                Response::error('Parámetro requerido', 400);
            }
            break;

        case 'POST':
            // Abrir caja
            $data = json_decode(file_get_contents("php://input"));

            if (!isset($data->monto_inicial) || $data->monto_inicial < 0) {
                Response::error('Monto inicial inválido', 400);
            }

            // Verificar que no haya caja abierta
            $checkStmt = $db->prepare(
                "SELECT COUNT(*) AS total FROM cajas WHERE usuario_id = :usuario_id AND estado = 'abierta'"
            );
            $checkStmt->execute([':usuario_id' => $usuarioId]);
            $result = $checkStmt->fetch();

            if ($result['total'] > 0) {
                Response::error('Ya tienes una caja abierta. Debes cerrarla primero.', 400);
            }

            $stmt = $db->prepare(
                "INSERT INTO cajas (negocio_id, usuario_id, monto_inicial) VALUES (:negocio_id, :usuario_id, :monto_inicial)"
            );
            $stmt->execute([
                ':negocio_id'    => $negocioId,
                ':usuario_id'    => $usuarioId,
                ':monto_inicial' => $data->monto_inicial,
            ]);

            Response::success('Caja abierta exitosamente', ['id' => $db->lastInsertId()], 201);
            break;

        case 'PUT':
            // Cerrar caja
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->caja_id) || !isset($data->monto_real)) {
                Response::error('ID de caja y monto real son requeridos', 400);
            }

            $getCajaStmt = $db->prepare(
                "SELECT * FROM cajas WHERE id = :id AND usuario_id = :usuario_id AND estado = 'abierta'"
            );
            $getCajaStmt->execute([':id' => $data->caja_id, ':usuario_id' => $usuarioId]);
            $caja = $getCajaStmt->fetch();

            if (!$caja) {
                Response::error('Caja no encontrada o ya está cerrada', 404);
            }

            $ventasStmt = $db->prepare(
                "SELECT COALESCE(SUM(total), 0) AS total_ventas FROM ventas WHERE caja_id = :caja_id AND estado = 'completada'"
            );
            $ventasStmt->execute([':caja_id' => $data->caja_id]);
            $montoVentas = $ventasStmt->fetch()['total_ventas'];

            $gastosStmt = $db->prepare(
                "SELECT COALESCE(SUM(monto), 0) AS total_gastos FROM gastos WHERE caja_id = :caja_id"
            );
            $gastosStmt->execute([':caja_id' => $data->caja_id]);
            $montoGastos = $gastosStmt->fetch()['total_gastos'];

            $montoFinal = $caja['monto_inicial'] + $montoVentas - $montoGastos;
            $diferencia = $data->monto_real - $montoFinal;

            $stmt = $db->prepare(
                "UPDATE cajas
                 SET estado = 'cerrada',
                     monto_ventas  = :monto_ventas,
                     monto_gastos  = :monto_gastos,
                     monto_final   = :monto_final,
                     monto_real    = :monto_real,
                     diferencia    = :diferencia,
                     observaciones = :observaciones,
                     fecha_cierre  = CURRENT_TIMESTAMP
                 WHERE id = :id AND usuario_id = :usuario_id"
            );
            $stmt->execute([
                ':id'           => $data->caja_id,
                ':usuario_id'   => $usuarioId,
                ':monto_ventas' => $montoVentas,
                ':monto_gastos' => $montoGastos,
                ':monto_final'  => $montoFinal,
                ':monto_real'   => $data->monto_real,
                ':diferencia'   => $diferencia,
                ':observaciones'=> $data->observaciones ?? null,
            ]);

            Response::success('Caja cerrada exitosamente', [
                'monto_inicial' => $caja['monto_inicial'],
                'monto_ventas'  => $montoVentas,
                'monto_gastos'  => $montoGastos,
                'monto_final'   => $montoFinal,
                'monto_real'    => $data->monto_real,
                'diferencia'    => $diferencia,
            ]);
            break;

        default:
            Response::error('Método no permitido', 405);
            break;
    }

} catch (Exception $e) {
    Response::error('Error en el servidor: ' . $e->getMessage(), 500);
}
