<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';

$data = load_data();

echo template('orders.html', [
    'orders' => isset($data['orders']) ? $data['orders'] : [],
    'sms' => isset($data['sms']) ? $data['sms'] : [],
]);