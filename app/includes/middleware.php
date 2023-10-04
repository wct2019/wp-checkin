<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

use WCTokyo\WpCheckin\FireBase;

FireBase::get_instance()->setCredentials(getenv('FIREBASE_SECRET_KEY_FILE_PATH'), getenv('GCP_HOST'));
