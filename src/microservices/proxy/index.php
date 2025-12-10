<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// Базовые URL сервисов
$monolithBase = getenv('MONOLITH_BASE_URL') ?: 'http://monolith:8080';
$moviesBase   = getenv('MOVIES_BASE_URL')   ?: 'http://movies-service:8081';
$eventsBase   = getenv('EVENTS_BASE_URL')   ?: 'http://events-service:8082';

Flight::set('monolith_base', $monolithBase);
Flight::set('movies_base', $moviesBase);
Flight::set('events_base', $eventsBase);

// Один общий HTTP-клиент
$client = new Client([
    'http_errors' => false, // не кидать исключения на 4xx/5xx
    'timeout'     => 5.0,
]);
Flight::set('http_client', $client);

/**
 * Универсальный прокси по HTTP.
 *
 * @param string      $baseUrl   базовый URL сервиса (например http://movies-service:8081)
 * @param string|null $pathOverride если нужно переопределить путь (по умолчанию берём текущий)
 * @param bool        $json      ожидаем ли JSON (ставим Content-Type application/json)
 */
function proxyRequest(string $baseUrl, ?string $pathOverride = null, bool $json = true): void
{
    $request = Flight::request();
    /** @var Client $client */
    $client  = Flight::get('http_client');

    $method = $request->method;        // GET / POST / ...
    $path   = $pathOverride ?? $request->url; // например /api/movies

    // query-параметры
    $query = $request->query->getData();

    $url = rtrim($baseUrl, '/') . $path;

    $options = [
        'query'       => $query,
        'http_errors' => false,
    ];

    // Тело запроса для POST/PUT/PATCH
    $body = $request->getBody();
    if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
        $options['body'] = $body;

        // Минимальный набор заголовков
        $contentType = $request->getHeader('Content-Type') ?: 'application/json';
        $options['headers'] = [
            'Content-Type' => $contentType,
        ];
    }

    try {
        $response = $client->request($method, $url, $options);
        $status   = $response->getStatusCode();
        $respBody = (string) $response->getBody();
    } catch (GuzzleException $e) {
        $status   = 502;
        $respBody = json_encode(['error' => 'Bad Gateway: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        $json     = true;
    }

    $resp = Flight::response()->status($status);
    $resp->header('Content-Type', $json ? 'application/json' : 'text/plain');
    $resp->write($respBody)->send();
    exit;
}

// -------------------------
// Роуты
// -------------------------

Flight::route('GET /health', function () {
    Flight::response()
        ->status(200)
        ->header('Content-Type', 'text/plain')
        ->write('{"status":"ok"}');
});

// ---- Movies → movies-сервис ----
// GET /api/movies → http://movies-service:8081/api/movies
Flight::route('GET /api/movies', function () {
    proxyRequest(Flight::get('movies_base'));
});

// POST /api/movies → http://movies-service:8081/api/movies
Flight::route('POST /api/movies', function () {
    proxyRequest(Flight::get('movies_base'));
});

// ---- Users → монолит ----
// GET /api/users → http://monolith:8080/api/users
Flight::route('GET /api/users', function () {
    proxyRequest(Flight::get('monolith_base'));
});


Flight::start();
