<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST', 'PUT', 'DELETE']);
$method = $_SERVER['REQUEST_METHOD'];

[$negocioId, $usuarioId] = Middleware::auth();
$isAdmin = Auth::isAdmin();

$database = new Database();
$db = $database->getConnection();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener un empleado específico con sus permisos
                $stmt = $db->prepare("
                    SELECT u.*, p.* 
                    FROM usuarios u
                    LEFT JOIN permisos p ON u.id = p.usuario_id
                    WHERE u.id = ? AND u.negocio_id = ?
                ");
                $stmt->execute([$_GET['id'], $negocioId]);
                $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($empleado) {
                    // No devolver la contraseña
                    unset($empleado['password']);
                    
                    // Separar permisos del empleado
                    $permisos = [
                        'ver_productos'       => (int)($empleado['ver_productos']       ?? 1),
                        'crear_productos'     => (int)($empleado['crear_productos']     ?? 0),
                        'editar_productos'    => (int)($empleado['editar_productos']    ?? 0),
                        'eliminar_productos'  => (int)($empleado['eliminar_productos']  ?? 0),
                        'ver_ventas'          => (int)($empleado['ver_ventas']          ?? 1),
                        'crear_ventas'        => (int)($empleado['crear_ventas']        ?? 1),
                        'cancelar_ventas'     => (int)($empleado['cancelar_ventas']     ?? 0),
                        'ver_gastos'          => (int)($empleado['ver_gastos']          ?? 1),
                        'crear_gastos'        => (int)($empleado['crear_gastos']        ?? 1),
                        'ver_empleados'       => (int)($empleado['ver_empleados']       ?? 0),
                        'crear_empleados'     => (int)($empleado['crear_empleados']     ?? 0),
                        'ver_reportes'        => (int)($empleado['ver_reportes']        ?? 0),
                        'gestionar_caja'      => (int)($empleado['gestionar_caja']      ?? 1),
                        // Restaurant
                        'ver_mesas'           => (int)($empleado['ver_mesas']           ?? 0),
                        'gestionar_mesas'     => (int)($empleado['gestionar_mesas']     ?? 0),
                        'ver_reservas'        => (int)($empleado['ver_reservas']        ?? 0),
                        'gestionar_reservas'  => (int)($empleado['gestionar_reservas']  ?? 0),
                        'ver_cocina'          => (int)($empleado['ver_cocina']          ?? 0),
                        'gestionar_cocina'    => (int)($empleado['gestionar_cocina']    ?? 0),
                    ];
                    
                    // Limpiar datos duplicados de permisos
                    foreach ($permisos as $key => $value) {
                        unset($empleado[$key]);
                    }
                    
                    $empleado['permisos'] = $permisos;
                    
                    Response::success('Empleado encontrado', $empleado);
                } else {
                    Response::error('Empleado no encontrado', 404);
                }
            } else {
                // Listar empleados
                $where = ["negocio_id = ?"];
                $params = [$negocioId];
                
                if (isset($_GET['rol']) && $_GET['rol'] != '') {
                    $where[] = "rol = ?";
                    $params[] = $_GET['rol'];
                }
                
                if (isset($_GET['estado']) && $_GET['estado'] != '') {
                    if ($_GET['estado'] === 'activo') {
                        $where[] = "activo = 1";
                    } elseif ($_GET['estado'] === 'inactivo') {
                        $where[] = "activo = 0";
                    }
                }
                
                if (isset($_GET['search']) && $_GET['search'] != '') {
                    $where[] = "(nombre LIKE ? OR apellido LIKE ? OR usuario LIKE ? OR email LIKE ? OR telefono LIKE ?)";
                    $searchTerm = '%' . $_GET['search'] . '%';
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $whereClause = implode(' AND ', $where);
                
                $stmt = $db->prepare("
                    SELECT id, nombre, apellido, usuario, email, telefono, rol, activo,
                           IF(activo=1,'activo','inactivo') as estado,
                           fecha_creacion, ultimo_acceso
                    FROM usuarios
                    WHERE $whereClause
                    ORDER BY fecha_creacion DESC
                ");
                $stmt->execute($params);
                $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                Response::success('Empleados obtenidos', $empleados);
            }
            break;
            
        case 'POST':
            // Crear empleado (solo admin)
            if (!$isAdmin) {
                Response::error('No tienes permisos para crear empleados', 403);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['nombre']) || !isset($data['apellido']) || 
                !isset($data['usuario']) || !isset($data['password']) || !isset($data['rol'])) {
                Response::error('Datos incompletos');
                exit;
            }
            
            // Verificar si el usuario ya existe
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ? AND negocio_id = ?");
            $stmt->execute([$data['usuario'], $negocioId]);
            if ($stmt->fetch()) {
                Response::error('El nombre de usuario ya está registrado');
                exit;
            }
            
            // Hash de la contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("
                INSERT INTO usuarios (negocio_id, nombre, apellido, usuario, email, password, telefono, rol, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $negocioId,
                $data['nombre'],
                $data['apellido'],
                $data['usuario'],
                $data['email'] ?? null,
                $hashedPassword,
                $data['telefono'] ?? null,
                $data['rol'],
                $data['activo'] ?? 1
            ]);
            
            if ($result) {
                $empleadoId = $db->lastInsertId();
                
                // Si es empleado, crear sus permisos
                if ($data['rol'] === 'empleado' && isset($data['permisos'])) {
                    $permisos = $data['permisos'];
                    $stmt = $db->prepare("
                        INSERT INTO permisos (
                            usuario_id, ver_productos, crear_productos, editar_productos, eliminar_productos,
                            ver_ventas, crear_ventas, cancelar_ventas,
                            ver_gastos, crear_gastos, ver_empleados, crear_empleados, ver_reportes, gestionar_caja,
                            ver_mesas, gestionar_mesas, ver_reservas, gestionar_reservas, ver_cocina, gestionar_cocina
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $empleadoId,
                        $permisos['ver_productos']      ?? 1,
                        $permisos['crear_productos']    ?? 0,
                        $permisos['editar_productos']   ?? 0,
                        $permisos['eliminar_productos'] ?? 0,
                        $permisos['ver_ventas']         ?? 1,
                        $permisos['crear_ventas']       ?? 1,
                        $permisos['cancelar_ventas']    ?? 0,
                        $permisos['ver_gastos']         ?? 0,
                        $permisos['crear_gastos']       ?? 0,
                        $permisos['ver_empleados']      ?? 0,
                        $permisos['crear_empleados']    ?? 0,
                        $permisos['ver_reportes']       ?? 0,
                        $permisos['gestionar_caja']     ?? 1,
                        $permisos['ver_mesas']          ?? 0,
                        $permisos['gestionar_mesas']    ?? 0,
                        $permisos['ver_reservas']       ?? 0,
                        $permisos['gestionar_reservas'] ?? 0,
                        $permisos['ver_cocina']         ?? 0,
                        $permisos['gestionar_cocina']   ?? 0,
                    ]);
                } elseif ($data['rol'] === 'admin') {
                    $stmt = $db->prepare("
                        INSERT INTO permisos (
                            usuario_id, ver_productos, crear_productos, editar_productos, eliminar_productos,
                            ver_ventas, crear_ventas, cancelar_ventas,
                            ver_gastos, crear_gastos, ver_empleados, crear_empleados, ver_reportes, gestionar_caja,
                            ver_mesas, gestionar_mesas, ver_reservas, gestionar_reservas, ver_cocina, gestionar_cocina
                        ) VALUES (?, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)
                    ");
                    $stmt->execute([$empleadoId]);
                }
                
                Response::success('Empleado creado correctamente', ['id' => $empleadoId]);
            } else {
                Response::error('Error al crear el empleado');
            }
            break;
            
        case 'PUT':
            // Actualizar empleado (solo admin)
            if (!$isAdmin) {
                Response::error('No tienes permisos para editar empleados', 403);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                Response::error('ID de empleado no proporcionado');
                exit;
            }
            
            $fields = [];
            $params = [];
            
            if (isset($data['nombre'])) {
                $fields[] = "nombre = ?";
                $params[] = $data['nombre'];
            }
            if (isset($data['apellido'])) {
                $fields[] = "apellido = ?";
                $params[] = $data['apellido'];
            }
            if (isset($data['usuario'])) {
                // Verificar que el usuario no esté en uso por otro usuario
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ? AND negocio_id = ?");
                $stmt->execute([$data['usuario'], $data['id'], $negocioId]);
                if ($stmt->fetch()) {
                    Response::error('El nombre de usuario ya está registrado');
                    exit;
                }
                $fields[] = "usuario = ?";
                $params[] = $data['usuario'];
            }
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $params[] = $data['email'];
            }
            if (isset($data['telefono'])) {
                $fields[] = "telefono = ?";
                $params[] = $data['telefono'];
            }
            if (isset($data['rol'])) {
                $fields[] = "rol = ?";
                $params[] = $data['rol'];
            }
            if (isset($data['activo'])) {
                $fields[] = "activo = ?";
                $params[] = $data['activo'];
            }
            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = "password = ?";
                $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            
            if (empty($fields)) {
                Response::error('No hay campos para actualizar');
                exit;
            }
            
            $params[] = $data['id'];
            $params[] = $negocioId;
            
            $sql = "UPDATE usuarios SET " . implode(', ', $fields) . 
                   " WHERE id = ? AND negocio_id = ?";
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                // Si es empleado y se enviaron permisos, actualizarlos
                if (isset($data['rol']) && $data['rol'] === 'empleado' && isset($data['permisos'])) {
                    $permisos = $data['permisos'];
                    
                    // Verificar si ya existen permisos
                    $stmt = $db->prepare("SELECT id FROM permisos WHERE usuario_id = ?");
                    $stmt->execute([$data['id']]);
                    $permisosExisten = $stmt->fetch();
                    
                    if ($permisosExisten) {
                        // Actualizar permisos existentes
                        $stmt = $db->prepare("
                            UPDATE permisos SET
                                ver_productos = ?, crear_productos = ?, editar_productos = ?, eliminar_productos = ?,
                                ver_ventas = ?, crear_ventas = ?, cancelar_ventas = ?,
                                ver_gastos = ?, crear_gastos = ?,
                                ver_empleados = ?, crear_empleados = ?,
                                ver_reportes = ?, gestionar_caja = ?,
                                ver_mesas = ?, gestionar_mesas = ?,
                                ver_reservas = ?, gestionar_reservas = ?,
                                ver_cocina = ?, gestionar_cocina = ?
                            WHERE usuario_id = ?
                        ");
                        
                        $stmt->execute([
                            $permisos['ver_productos']      ?? 1,
                            $permisos['crear_productos']    ?? 0,
                            $permisos['editar_productos']   ?? 0,
                            $permisos['eliminar_productos'] ?? 0,
                            $permisos['ver_ventas']         ?? 1,
                            $permisos['crear_ventas']       ?? 1,
                            $permisos['cancelar_ventas']    ?? 0,
                            $permisos['ver_gastos']         ?? 0,
                            $permisos['crear_gastos']       ?? 0,
                            $permisos['ver_empleados']      ?? 0,
                            $permisos['crear_empleados']    ?? 0,
                            $permisos['ver_reportes']       ?? 0,
                            $permisos['gestionar_caja']     ?? 1,
                            $permisos['ver_mesas']          ?? 0,
                            $permisos['gestionar_mesas']    ?? 0,
                            $permisos['ver_reservas']       ?? 0,
                            $permisos['gestionar_reservas'] ?? 0,
                            $permisos['ver_cocina']         ?? 0,
                            $permisos['gestionar_cocina']   ?? 0,
                            $data['id']
                        ]);
                    } else {
                        // Crear nuevos permisos
                        $stmt = $db->prepare("
                            INSERT INTO permisos (
                                usuario_id, ver_productos, crear_productos, editar_productos, eliminar_productos,
                                ver_ventas, crear_ventas, cancelar_ventas,
                                ver_gastos, crear_gastos, ver_empleados, crear_empleados, ver_reportes, gestionar_caja,
                                ver_mesas, gestionar_mesas, ver_reservas, gestionar_reservas, ver_cocina, gestionar_cocina
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $data['id'],
                            $permisos['ver_productos']      ?? 1,
                            $permisos['crear_productos']    ?? 0,
                            $permisos['editar_productos']   ?? 0,
                            $permisos['eliminar_productos'] ?? 0,
                            $permisos['ver_ventas']         ?? 1,
                            $permisos['crear_ventas']       ?? 1,
                            $permisos['cancelar_ventas']    ?? 0,
                            $permisos['ver_gastos']         ?? 0,
                            $permisos['crear_gastos']       ?? 0,
                            $permisos['ver_empleados']      ?? 0,
                            $permisos['crear_empleados']    ?? 0,
                            $permisos['ver_reportes']       ?? 0,
                            $permisos['gestionar_caja']     ?? 1,
                            $permisos['ver_mesas']          ?? 0,
                            $permisos['gestionar_mesas']    ?? 0,
                            $permisos['ver_reservas']       ?? 0,
                            $permisos['gestionar_reservas'] ?? 0,
                            $permisos['ver_cocina']         ?? 0,
                            $permisos['gestionar_cocina']   ?? 0,
                        ]);
                    }
                } elseif (isset($data['rol']) && $data['rol'] === 'admin') {
                    // Si cambia a admin, darle todos los permisos
                    $stmt = $db->prepare("SELECT id FROM permisos WHERE usuario_id = ?");
                    $stmt->execute([$data['id']]);
                    $permisosExisten = $stmt->fetch();
                    
                    if ($permisosExisten) {
                        $stmt = $db->prepare("
                            UPDATE permisos SET
                                ver_productos = 1, crear_productos = 1, editar_productos = 1, eliminar_productos = 1,
                                ver_ventas = 1, crear_ventas = 1, cancelar_ventas = 1,
                                ver_gastos = 1, crear_gastos = 1,
                                ver_empleados = 1, crear_empleados = 1,
                                ver_reportes = 1, gestionar_caja = 1,
                                ver_mesas = 1, gestionar_mesas = 1,
                                ver_reservas = 1, gestionar_reservas = 1,
                                ver_cocina = 1, gestionar_cocina = 1
                            WHERE usuario_id = ?
                        ");
                        $stmt->execute([$data['id']]);
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO permisos (
                                usuario_id, ver_productos, crear_productos, editar_productos, eliminar_productos,
                                ver_ventas, crear_ventas, cancelar_ventas,
                                ver_gastos, crear_gastos, ver_empleados, crear_empleados, ver_reportes, gestionar_caja,
                                ver_mesas, gestionar_mesas, ver_reservas, gestionar_reservas, ver_cocina, gestionar_cocina
                            ) VALUES (?, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)
                        ");
                        $stmt->execute([$data['id']]);
                    }
                }
                
                Response::success('Empleado actualizado correctamente');
            } else {
                Response::error('Error al actualizar el empleado');
            }
            break;
            
        case 'DELETE':
            // Eliminar empleado (solo admin)
            if (!$isAdmin) {
                Response::error('No tienes permisos para eliminar empleados', 403);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                Response::error('ID de empleado no proporcionado');
                exit;
            }
            
            // No permitir eliminar al usuario que está haciendo la petición
            if ($data['id'] == $usuarioId) {
                Response::error('No puedes eliminarte a ti mismo');
                exit;
            }
            
            $stmt = $db->prepare("
                DELETE FROM usuarios 
                WHERE id = ? AND negocio_id = ?
            ");
            
            $result = $stmt->execute([$data['id'], $negocioId]);
            
            if ($result) {
                Response::success('Empleado eliminado correctamente');
            } else {
                Response::error('Error al eliminar el empleado');
            }
            break;
            
        default:
            Response::error('Método no permitido', 405);
            break;
    }
} catch (PDOException $e) {
    Response::error('Error en la base de datos: ' . $e->getMessage(), 500);
}
