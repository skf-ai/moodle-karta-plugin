<?php
// AJAX endpoint to proxy requests to Karta API.
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_login();
require_sesskey();

function uuid_v4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$message = required_param('message', PARAM_RAW);
$userid = $USER->id;

$interactionid = (string)$userid;
$sessionid = $SESSION->chatbot_sessionid ?? null;
if (!$sessionid) {
    $sessionid = uuid_v4();
    $SESSION->chatbot_sessionid = $sessionid;
}
$now = microtime(true);
$created = gmdate('Y-m-d\TH:i:s.', (int)$now) . sprintf('%06d', ($now - floor($now)) * 1e6);

$payload = [
    'session_id' => $sessionid,
    'interaction_id' => $interactionid,
    'messages' => [[
        'content' => $message,
        'content_type' => 'text',
        'created_at' => $created
    ]],
    'user_property' => new stdClass(),
    'course_id' => ['111']
];

$ch = curl_init('https://hooks.getkarta.ai/api/v1/skf/SidRFS_67aAV4');
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, 'SKF_ADMIN:^ffB57rC]1$5');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');
if ($errno) {
    echo json_encode(['error' => $error]);
    exit;
}

$data = json_decode($response, true);
if ($code == 200 && isset($data['reply'])) {
    echo json_encode(['reply' => $data['reply']]);
} else {
    echo json_encode(['error' => $response ?: 'HTTP ' . $code]);
}

