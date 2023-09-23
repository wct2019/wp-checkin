<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

use WCTokyo\WpCheckin\FireBase;

FireBase::get_instance()->setCredentials( dirname( __DIR__ ) . '/wp-checkin-2023-firebase-adminsdk-8mk70-443ebaa225.json', 'https://wp-checkin-2023.firebaseio.com' );
