<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

use app\src\WCTokyo\WpCheckin\FireBase;

FireBase::get_instance()->setCredentials( dirname( __DIR__ ) . '/wordcamptokyo2019app-firebase-key.json', 'https://wordcamptokyo2019app.firebaseio.com' );
