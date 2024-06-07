<?php

require "Core.php";

header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

$core = new Core();
if (!empty($_GET['name']) && $_GET['name'] == 'getData') {
    $data = $core->fetchData();
    if (!empty($data['is_success'])) {
        echo json_encode($data['data']);
    } else {
        echo json_encode([]);
    }
}