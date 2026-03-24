<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
$method = $_SERVER['REQUEST_METHOD'];

try {
    [$negocioId] = Middleware::auth();

    $database = new Database();
    $db = $database->getConnection();
    PlanGuard::requireActive($negocioId, $db);

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $db->prepare(
                    "SELECT c.*, (SELECT COUNT(*) FROM ventas WHERE cliente_id = c.id) AS total_ventas
                     FROM clientes c WHERE c.id = :id AND c.negocio_id = :negocio_id"
                );
                $stmt->execute([':id' => $_GET['id'], ':negocio_id' => $negocioId]);
                $cliente = $stmt->fetch();

                if ($cliente) {
                    Response::success('Cliente obtenido', $cliente);
                } else {
                    Response::error('Cliente no encontrado', 404);
                }
            } else {
                $query  = "SELECT c.*, (SELECT COUNT(*) FROM ventas WHERE cliente_id = c.id) AS total_ventas
                           FROM clientes c WHERE c.negocio_id = :negocio_id";
                $params = [':negocio_id' => $negocioId];

                if (!empty($_GET['search'])) {
                    $query           .= " AND (c.nombre LIKE :search OR c.apellido LIKE :search OR c.razon_social LIKE :search
                                         OR c.documento LIKE :search OR c.codigo_cliente LIKE :search OR c.email LIKE :search OR c.telefono LIKE :search)";
                    $params[':search'] = '%' . $_GET['search'] . '%';
                }
                if (!empty($_GET['tipo']))      { $query .= " AND c.tipo = :tipo";           $params[':tipo']      = $_GET['tipo']; }
                if (!empty($_GET['categoria'])) { $query .= " AND c.categoria = :categoria"; $params[':categoria'] = $_GET['categoria']; }
                if (!empty($_GET['estado']))    { $query .= " AND c.estado = :estado";       $params[':estado']    = $_GET['estado']; }

                $query .= " ORDER BY c.fecha_creacion DESC";
                $stmt   = $db->prepare($query);
                $stmt->execute($params);
                Response::success('Clientes obtenidos', $stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->nombre)) {
                Response::error('El nombre es requerido', 400);
            }

            if (!empty($data->codigo_cliente)) {
                $chk = $db->prepare("SELECT COUNT(*) AS total FROM clientes WHERE codigo_cliente = :codigo_cliente AND negocio_id = :negocio_id");
                $chk->execute([':codigo_cliente' => $data->codigo_cliente, ':negocio_id' => $negocioId]);
                if ($chk->fetch()['total'] > 0) { Response::error('El código de cliente ya existe', 400); }
            }

            if (!empty($data->documento)) {
                $chk = $db->prepare("SELECT COUNT(*) AS total FROM clientes WHERE documento = :documento AND negocio_id = :negocio_id");
                $chk->execute([':documento' => $data->documento, ':negocio_id' => $negocioId]);
                if ($chk->fetch()['total'] > 0) { Response::error('El documento ya está registrado', 400); }
            }

            $stmt = $db->prepare(
                "INSERT INTO clientes (negocio_id, codigo_cliente, tipo, nombre, apellido, razon_social, documento,
                    email, telefono, celular, fecha_nacimiento, direccion, ciudad, provincia,
                    codigo_postal, pais, notas, categoria, descuento_especial, limite_credito, estado)
                 VALUES (:negocio_id, :codigo_cliente, :tipo, :nombre, :apellido, :razon_social, :documento,
                    :email, :telefono, :celular, :fecha_nacimiento, :direccion, :ciudad, :provincia,
                    :codigo_postal, :pais, :notas, :categoria, :descuento_especial, :limite_credito, :estado)"
            );
            $stmt->execute([
                ':negocio_id'         => $negocioId,
                ':codigo_cliente'     => $data->codigo_cliente     ?? null,
                ':tipo'               => $data->tipo               ?? 'persona',
                ':nombre'             => $data->nombre,
                ':apellido'           => $data->apellido           ?? null,
                ':razon_social'       => $data->razon_social       ?? null,
                ':documento'          => $data->documento          ?? null,
                ':email'              => $data->email              ?? null,
                ':telefono'           => $data->telefono           ?? null,
                ':celular'            => $data->celular            ?? null,
                ':fecha_nacimiento'   => $data->fecha_nacimiento   ?? null,
                ':direccion'          => $data->direccion          ?? null,
                ':ciudad'             => $data->ciudad             ?? null,
                ':provincia'          => $data->provincia          ?? null,
                ':codigo_postal'      => $data->codigo_postal      ?? null,
                ':pais'               => $data->pais               ?? 'México',
                ':notas'              => $data->notas              ?? null,
                ':categoria'          => $data->categoria          ?? 'regular',
                ':descuento_especial' => $data->descuento_especial ?? 0,
                ':limite_credito'     => $data->limite_credito     ?? 0,
                ':estado'             => $data->estado             ?? 'activo',
            ]);

            $clienteId = $db->lastInsertId();
            $getStmt = $db->prepare("SELECT * FROM clientes WHERE id = :id");
            $getStmt->execute([':id' => $clienteId]);
            Response::success('Cliente creado exitosamente', $getStmt->fetch(), 201);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->id)) {
                Response::error('ID del cliente es requerido', 400);
            }

            $chk = $db->prepare("SELECT id FROM clientes WHERE id = :id AND negocio_id = :negocio_id");
            $chk->execute([':id' => $data->id, ':negocio_id' => $negocioId]);
            if (!$chk->fetch()) { Response::error('Cliente no encontrado', 404); }

            $stmt = $db->prepare(
                "UPDATE clientes SET
                    tipo = :tipo, nombre = :nombre, apellido = :apellido, razon_social = :razon_social,
                    documento = :documento, email = :email, telefono = :telefono, celular = :celular,
                    fecha_nacimiento = :fecha_nacimiento, direccion = :direccion, ciudad = :ciudad,
                    provincia = :provincia, codigo_postal = :codigo_postal, pais = :pais,
                    notas = :notas, categoria = :categoria, descuento_especial = :descuento_especial,
                    limite_credito = :limite_credito, estado = :estado
                 WHERE id = :id AND negocio_id = :negocio_id"
            );
            $stmt->execute([
                ':id'                 => $data->id,
                ':negocio_id'         => $negocioId,
                ':tipo'               => $data->tipo               ?? 'persona',
                ':nombre'             => $data->nombre,
                ':apellido'           => $data->apellido           ?? null,
                ':razon_social'       => $data->razon_social       ?? null,
                ':documento'          => $data->documento          ?? null,
                ':email'              => $data->email              ?? null,
                ':telefono'           => $data->telefono           ?? null,
                ':celular'            => $data->celular            ?? null,
                ':fecha_nacimiento'   => $data->fecha_nacimiento   ?? null,
                ':direccion'          => $data->direccion          ?? null,
                ':ciudad'             => $data->ciudad             ?? null,
                ':provincia'          => $data->provincia          ?? null,
                ':codigo_postal'      => $data->codigo_postal      ?? null,
                ':pais'               => $data->pais               ?? 'México',
                ':notas'              => $data->notas              ?? null,
                ':categoria'          => $data->categoria          ?? 'regular',
                ':descuento_especial' => $data->descuento_especial ?? 0,
                ':limite_credito'     => $data->limite_credito     ?? 0,
                ':estado'             => $data->estado             ?? 'activo',
            ]);

            Response::success('Cliente actualizado exitosamente');
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"));

            if (empty($data->id)) {
                Response::error('ID del cliente es requerido', 400);
            }

            $chk = $db->prepare("SELECT COUNT(*) AS total FROM ventas WHERE cliente_id = :id");
            $chk->execute([':id' => $data->id]);
            if ($chk->fetch()['total'] > 0) {
                Response::error('No se puede eliminar el cliente porque tiene ventas registradas', 400);
            }

            $stmt = $db->prepare("DELETE FROM clientes WHERE id = :id AND negocio_id = :negocio_id");
            $stmt->execute([':id' => $data->id, ':negocio_id' => $negocioId]);
            Response::success('Cliente eliminado exitosamente');
            break;

        default:
            Response::error('Método no permitido', 405);
            break;
    }

} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}
