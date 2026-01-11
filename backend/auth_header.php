<?php
session_start();

define('SSO_SECRET', 'VETCLINIC_SSO_2026_SECRET');

/* =========================
   1. Capture token if present
========================= */
if (isset($_GET['token'])) {
    $_SESSION['sso_token'] = $_GET['token'];
}

/* =========================
   2. Require token or session
========================= */
$token = $_SESSION['sso_token'] ?? null;

if (!$token) {
    header("Location: http://10.48.74.199:81/vetcli/frontend/userlogin.php");
    exit;
}

/* =========================
   3. Verify token
========================= */
$parts = explode('.', $token);
if (count($parts) !== 2) die("Invalid token");

[$payload_b64, $signature] = $parts;
$expected = hash_hmac('sha256', $payload_b64, SSO_SECRET);
if (!hash_equals($expected, $signature)) die("Invalid token");

$payload = json_decode(base64_decode($payload_b64), true);
if (!$payload || $payload['exp'] < time()) die("Token expired");

/* =========================
   4. Create / refresh local session
========================= */
$_SESSION['userID']   = $payload['id'];
$_SESSION['userName'] = $payload['name'];
$_SESSION['userType'] = $payload['type'];
