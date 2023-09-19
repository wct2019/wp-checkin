<?php

namespace app\src\WCTokyo\WpCheckin;


use Google\Cloud\Firestore\FirestoreClient;
use Hametuha\SingletonPattern\Singleton;
use Kreait\Firebase\Factory;

class FireBase extends Singleton {

	protected $credential_file_path = '';
	
	protected $database_url = '';
	
	protected $db = null;
	
	/**
	 * Handle credential errors.
	 *
	 * @param string $setting_file_path
	 * @param string $uri
	 * @throws \Exception
	 */
	public function setCredentials( $setting_file_path, $uri ) {
		$this->credential_file_path = $setting_file_path;
		$this->database_url         = $uri;
	}
	
	/**
	 * Get database client.
	 *
	 * @return FirestoreClient
	 */
	public function db() {
		if ( is_null( $this->db) ) {
			$factory = ( new Factory() )
				->withServiceAccount( $this->credential_file_path )
				->withDatabaseUri( $this->database_url );
			$firestore = $factory->createFirestore();
			$this->db = $firestore->database();
		}
		return $this->db;
	}
}
