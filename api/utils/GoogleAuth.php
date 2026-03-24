<?php

class GoogleAuth
{
    private const TOKEN_INFO_ID_URL = 'https://oauth2.googleapis.com/tokeninfo?id_token=';
    private const TOKEN_INFO_ACCESS_URL = 'https://oauth2.googleapis.com/tokeninfo?access_token=';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    private static function fetchJson(string $url, array $headers = []): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 8,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers),
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            throw new RuntimeException('No se pudo validar el token de Google');
        }

        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            throw new RuntimeException('Respuesta inválida de Google');
        }

        if (!empty($payload['error_description']) || !empty($payload['error'])) {
            throw new RuntimeException('Token de Google inválido');
        }

        return $payload;
    }

    private static function getClientId(): string
    {
        $clientId = trim((string)($_ENV['GOOGLE_CLIENT_ID'] ?? ''));
        if ($clientId === '') {
            throw new RuntimeException('Google Auth no está configurado');
        }
        return $clientId;
    }

    private static function normalizePayload(array $payload): array
    {
        $email = mb_strtolower(trim((string)($payload['email'] ?? '')));
        $sub = trim((string)($payload['sub'] ?? ''));
        if ($sub === '' || $email === '') {
            throw new RuntimeException('Token de Google incompleto');
        }

        $emailVerifiedRaw = (string)($payload['email_verified'] ?? '');
        $emailVerified = $emailVerifiedRaw === 'true' || $emailVerifiedRaw === '1' || $emailVerifiedRaw === 'True';
        if (!$emailVerified) {
            throw new RuntimeException('El email de Google no está verificado');
        }

        return [
            'sub' => $sub,
            'email' => $email,
            'name' => trim((string)($payload['name'] ?? '')),
            'given_name' => trim((string)($payload['given_name'] ?? '')),
            'family_name' => trim((string)($payload['family_name'] ?? '')),
            'picture' => trim((string)($payload['picture'] ?? '')),
        ];
    }

    public static function verifyIdToken(string $idToken): array
    {
        $idToken = trim($idToken);
        if ($idToken === '') {
            throw new RuntimeException('Token de Google requerido');
        }

        $clientId = self::getClientId();

        $payload = self::fetchJson(self::TOKEN_INFO_ID_URL . rawurlencode($idToken));

        $aud = (string)($payload['aud'] ?? '');
        if (!hash_equals($clientId, $aud)) {
            throw new RuntimeException('Token de Google para otro cliente');
        }

        $issuer = (string)($payload['iss'] ?? '');
        if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('Issuer de Google inválido');
        }

        $expiresAt = (int)($payload['exp'] ?? 0);
        if ($expiresAt <= time()) {
            throw new RuntimeException('Token de Google expirado');
        }

        return self::normalizePayload($payload);
    }

    public static function verifyAccessToken(string $accessToken): array
    {
        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            throw new RuntimeException('Token de Google requerido');
        }

        $clientId = self::getClientId();

        $tokenInfo = self::fetchJson(self::TOKEN_INFO_ACCESS_URL . rawurlencode($accessToken));

        $aud = (string)($tokenInfo['aud'] ?? '');
        if (!hash_equals($clientId, $aud)) {
            throw new RuntimeException('Token de Google para otro cliente');
        }

        $expiresIn = (int)($tokenInfo['expires_in'] ?? 0);
        if ($expiresIn <= 0) {
            throw new RuntimeException('Token de Google expirado');
        }

        $profile = self::fetchJson(self::USERINFO_URL, [
            'Authorization: Bearer ' . $accessToken,
        ]);

        $payload = [
            'sub' => $profile['sub'] ?? ($tokenInfo['sub'] ?? ''),
            'email' => $profile['email'] ?? ($tokenInfo['email'] ?? ''),
            'email_verified' => $profile['email_verified'] ?? ($tokenInfo['email_verified'] ?? 'false'),
            'name' => $profile['name'] ?? '',
            'given_name' => $profile['given_name'] ?? '',
            'family_name' => $profile['family_name'] ?? '',
            'picture' => $profile['picture'] ?? '',
        ];

        return self::normalizePayload($payload);
    }
}
