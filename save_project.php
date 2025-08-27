<?php
$savesDir = __DIR__ . '/saves';
$code = preg_replace('/[^0-9]/', '', $_POST['code'] ?? '');
$data = $_POST['schedule'] ?? '';
if ($code && $data) {
    file_put_contents("$savesDir/$code.save", $data);
    echo 'ok';
} else {
    http_response_code(400);
    echo 'error';
}
