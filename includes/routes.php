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
$app->get('/ticket/{ticket_id}', function( Request $request, Response $response, array $args ) {
	// Render ticket view.
	return $this->renderer->render( $response, 'ticket.phtml', [
		'ticket_id' => $args['ticket_id']
	] );
} );

// Handle github hook.
$app->post('/payload', function ( Request $request, Response $response, array $args ) {
    try {
        // This request is valid.
		$dir = dirname( __DIR__ );
        exec( sprintf('cd %s; ./bin/deploy.sh;', $dir ), $output );
        return $response->withJson([
            'messages'    => $output,
			'working_dir' => $dir,
        ], 200);
    } catch ( \Exception $e ) {
        return $response->withJson( [
            'message' => $e->getMessage(),
        ], $e->getCode() );
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
