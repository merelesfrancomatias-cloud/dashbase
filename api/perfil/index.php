<?php
require_once __DIR__ . '/../bootstrap.php';

Middleware::cors(['GET', 'POST']);
$method = $_SERVER['REQUEST_METHOD'];

[$negocioId] = Middleware::auth();

try {
    $db   = new Database();
    $conn = $db->getConnection();

    switch ($method) {

        // -------------------------------------------------------
        // GET — obtener perfil del tenant (tabla negocios)
        // -------------------------------------------------------
        case 'GET':
            $stmt = $conn->prepare("
                SELECT
                    id,
                    nombre              AS nombre_negocio,
                    razon_social,
                    cuit,
                    condicion_iva,
                    rubro,
                    telefono,
                    whatsapp,
                    email,
                    sitio_web,
                    instagram,
                    facebook,
                    direccion,
                    ciudad,
                    provincia,
                    codigo_postal,
                    logo,
                    imagen_portada,
                    carta_token,
                    carta_activa,
                    mensaje_ticket,
                    mostrar_logo_ticket,
                    mostrar_direccion_ticket,
                    mostrar_cuit_ticket,
                    horarios,
                    fecha_registro      AS created_at,
                    fecha_actualizacion AS updated_at
                FROM negocios
                WHERE id = :negocio_id
                LIMIT 1
            ");
            $stmt->execute([':negocio_id' => $negocioId]);
            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($perfil) {
                Response::success('Perfil obtenido correctamente', $perfil);
            } else {
                Response::error('Negocio no encontrado', 404);
            }
            break;

        // -------------------------------------------------------
        // POST — actualizar perfil del tenant (tabla negocios)
        // -------------------------------------------------------
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['nombre_negocio'])) {
                Response::error('El nombre del negocio es requerido', 400);
            }

            $stmt = $conn->prepare("
                UPDATE negocios SET
                    nombre                      = :nombre_negocio,
                    razon_social                = :razon_social,
                    cuit                        = :cuit,
                    condicion_iva               = :condicion_iva,
                    rubro                       = :rubro,
                    telefono                    = :telefono,
                    whatsapp                    = :whatsapp,
                    email                       = :email,
                    sitio_web                   = :sitio_web,
                    instagram                   = :instagram,
                    facebook                    = :facebook,
                    direccion                   = :direccion,
                    ciudad                      = :ciudad,
                    provincia                   = :provincia,
                    codigo_postal               = :codigo_postal,
                    mensaje_ticket              = :mensaje_ticket,
                    mostrar_logo_ticket         = :mostrar_logo_ticket,
                    mostrar_direccion_ticket    = :mostrar_direccion_ticket,
                    mostrar_cuit_ticket         = :mostrar_cuit_ticket,
                    horarios                    = :horarios,
                    fecha_actualizacion         = NOW()
                WHERE id = :negocio_id
            ");

            $stmt->execute([
                ':negocio_id'                => $negocioId,
                ':nombre_negocio'            => $data['nombre_negocio'],
                ':razon_social'              => $data['razon_social']              ?? null,
                ':cuit'                      => $data['cuit']                      ?? null,
                ':condicion_iva'             => $data['condicion_iva']             ?? null,
                ':rubro'                     => $data['rubro']                     ?? null,
                ':telefono'                  => $data['telefono']                  ?? null,
                ':whatsapp'                  => $data['whatsapp']                  ?? null,
                ':email'                     => $data['email']                     ?? null,
                ':sitio_web'                 => $data['sitio_web']                 ?? null,
                ':instagram'                 => $data['instagram']                 ?? null,
                ':facebook'                  => $data['facebook']                  ?? null,
                ':direccion'                 => $data['direccion']                 ?? null,
                ':ciudad'                    => $data['ciudad']                    ?? null,
                ':provincia'                 => $data['provincia']                 ?? null,
                ':codigo_postal'             => $data['codigo_postal']             ?? null,
                ':mensaje_ticket'            => $data['mensaje_ticket']            ?? null,
                ':mostrar_logo_ticket'       => $data['mostrar_logo_ticket']       ?? 1,
                ':mostrar_direccion_ticket'  => $data['mostrar_direccion_ticket']  ?? 1,
                ':mostrar_cuit_ticket'       => $data['mostrar_cuit_ticket']       ?? 1,
                ':horarios'                  => isset($data['horarios'])
                                                ? (is_string($data['horarios'])
                                                    ? $data['horarios']
                                                    : json_encode($data['horarios']))
                                                : null,
            ]);

            // Devolver perfil actualizado
            $stmt2 = $conn->prepare("
                SELECT
                    id,
                    nombre              AS nombre_negocio,
                    razon_social, cuit, condicion_iva, rubro,
                    telefono, whatsapp, email, sitio_web, instagram, facebook,
                    direccion, ciudad, provincia, codigo_postal,
                    logo, imagen_portada, carta_token, carta_activa, mensaje_ticket,
                    mostrar_logo_ticket, mostrar_direccion_ticket, mostrar_cuit_ticket,
                    horarios,
                    fecha_registro AS created_at, fecha_actualizacion AS updated_at
                FROM negocios
                WHERE id = :negocio_id
                LIMIT 1
            ");
            $stmt2->execute([':negocio_id' => $negocioId]);
            $perfil = $stmt2->fetch(PDO::FETCH_ASSOC);

            Response::success('Perfil actualizado correctamente', $perfil);
            break;

        default:
            Response::error('Método no permitido', 405);
    }

} catch (Exception $e) {
    Response::error('Error: ' . $e->getMessage(), 500);
}