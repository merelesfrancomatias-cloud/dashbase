<?php
class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($message, $data = null, $statusCode = 200) {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public static function error($message, $statusCode = 400, $errors = null) {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
