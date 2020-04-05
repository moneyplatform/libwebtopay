<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../src/includes.php';

try {
    $parsedData = WebToPay::validateAndParseData($_GET, $config['projectid'], $config['sign_password']);
} catch (WebToPayException $e) {
    $parsedData = 'Error: ' . $e->getMessage();
}

$data = load_data();
$data['sms'][] = [
    '_GET' => $_GET,
    'parsedData' => $parsedData,
];
save_data($data);