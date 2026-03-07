<?php
session_start();
require_once dirname(__DIR__) . '/_auth.php';
sa_check_auth();

header('Content-Type: application/json');
$db = sa_db();
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = intval($body['id'] ?? 0);
$activo = intval($body['activo'] ?? 0);
if (!$id) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }
try {
    $db->prepare("UPDATE usuarios SET activo = ? WHERE id = ?")->execute([$activo, $id]);
    sa_log($activo ? 'usuario_activado' : 'usuario_desactivado', "Usuario ID $id");
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
