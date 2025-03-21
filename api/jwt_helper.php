<?php
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "56mLHc5c3+CiKFSltPS+wKw61zGGiIkSvyQbLo99ahU"; 

function generateJWT($data, $exp = 600) {
    global $secret_key;
    $issuedAt = time();
    $payload = [
        'iat'  => $issuedAt,
        'exp'  => $issuedAt + $exp,
        'data' => $data
    ];
    return JWT::encode($payload, $secret_key, 'HS256');
}

function validateJWT($token) {
    global $secret_key;
    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        return (array)$decoded->data;
    } catch(Exception $e) {
        return false;
    }
}
?>

