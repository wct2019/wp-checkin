<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Home.
$app->get('/', function (Request $request, Response $response, array $args) {
    // Render index view
    return $this->renderer->render($response, 'index.phtml', [
        'versions' => '1.0.0',
    ]);
});

// Do post request.
//$app->post('/validator', [Validator::class, 'handlePostRequest']);
//$app->post('/validator/{version}', [ Validator::class, 'handlePostRequest']);

// Handle github hook.
$app->post('/payload', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $json = json_decode($body);
    try {
        $github_token = Validator::base() . '/GITHUB_TOKEN';
        if (!file_exists($github_token) || !($token = file_get_contents($github_token))) {
            throw new \Exception('Token not found.', 404);
        }
        $hash = 'sha1=' . hash_hmac('sha1', $body, $token);
        $header = $request->getHeader('X-Hub-Signature');
        if (!$header) {
            throw new \Exception('Header is not set.', 400);
        } elseif (!hash_equals($hash, $header[0])) {
//            throw new \Exception('Signature header is invalid.', 403);
            // Do not check.
        }
        // This request is valid.
        exec(sprintf('cd %s; bin/deploy.sh', Validator::base()), $output);
        return $response->withJson([
            'messages' => $output,
        ], 200);
    } catch (\Exception $e) {
        return $response->withJson([
            'message' => $e->getMessage(),
        ], $e->getCode());
    }
});

// Monitor site
$app->get('/monitor', function (Request $request, Response $response, array $args) {
    $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
    return $response->withJson([
        'status' => 'success',
        'timestamp' => $now->format(DateTime::ATOM),
    ], 200);
});
