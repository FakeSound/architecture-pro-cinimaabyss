<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// Базовые URL сервисов
$monolithBase = getenv('MONOLITH_BASE_URL') ?: 'http://monolith:8080';
$moviesBase   = getenv('MOVIES_BASE_URL')   ?: 'http://movies-service:8081';
$eventsBase   = getenv('EVENTS_BASE_URL')   ?: 'http://events-service:8082';

Flight::set('monolith_base', $monolithBase);
Flight::set('movies_base', $moviesBase);
Flight::set('events_base', $eventsBase);

/**
 * Универсальный прокси по HTTP.
 *
 * @param string      $baseUrl
 * @param string|null $pathOverride
 * @param bool        $json
 */


// -------------------------
// Роуты
// -------------------------

// Health самого прокси
Flight::route('GET /health', function () {
    Flight::response()
        ->status(200)
        ->header('Content-Type', 'text/plain')
        ->write('{"status":"ok"}');
});

// ---- Movies → movies-сервис ----
Flight::route('GET /api/movies', function () {
    Flight::response()
        ->status(200)
        ->header('Content-Type', 'text/plain')
        ->write('[{"movie_id":1,"title":"Inception"}]');
});

// ---- Users/Payments/Subscriptions → монолит ----
Flight::route('GET /api/users', function () {
    Flight::response()
        ->status(200)
        ->header('Content-Type', 'text/plain')
        ->write('[{"user_id":1,"name":"John Doe"}]');
});

Flight::start();
