<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

use WCTokyo\WpCheckin\FireBase;

FireBase::get_instance()->setCredentials( dirname( __DIR__ ) . '/wordcamptokyo2019app-firebase-key.json', 'https://wordcamptokyo2019app.firebaseio.com' );
$db = FireBase::get_instance()->db();
$usersRef = $db->collection('Tickets');
$snapshot = $usersRef->documents();
echo '<pre>';
foreach ($snapshot as $ticket) {
	print_r( $ticket );
}
echo '</pre>';
exit;
