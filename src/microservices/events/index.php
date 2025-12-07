<?php
require __DIR__ . '/vendor/autoload.php';

// CORS Middleware
Flight::before('start', function() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
});

// GET /modules/@moduleId/lights/status
Flight::route('GET /modules/@moduleId/lights/status', function($moduleId) {
    getLightStatus((int)$moduleId);
});

Flight::start();

function getLightStatus($moduleId) {
    $colors = ['#FFFFFF', '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF'];

    $response = [
        'module_id' => $moduleId,
        'color' => $colors[array_rand($colors)],
        'brightness' => rand(0, 100),
        'is_connected' => (bool)rand(0, 1),
        'updated_at' => date('Y-m-d\TH:i:s\Z')
    ];

    Flight::json($response);
}