<?php

namespace WCTokyo\WpCheckin;


use Google\Cloud\Firestore\DocumentSnapshot;
use Hametuha\SingletonPattern\Singleton;
use Slim\Http\Request;
use Slim\Http\Response;

class TicketApi extends Singleton {
	
	/**
	 * Returns JSON.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function handle_get( Request $request, Response $response, array $args ) {
		$document = $this->get_document( $args['ticket_id'] );
		if ( $document ) {
			return $response->withJson( $document );
		} else {
			return $response->withJson( null, 404 );
		}
	}
	
	/**
	 * Get document.
	 *
	 * @param string $ticket_id
	 * @return array
	 */
	protected function get_document( $ticket_id ) {
		$document = FireBase::get_instance()
							->db()
							->collection( 'Tickets' )
							->document( $ticket_id )
							->snapshot();
		if ( $document->exists() ) {
			return $this->convert_to_array( $document );
		} else {
			return [];
		}
	}
	
	/**
	 * Convert user data to array.
	 *
	 * @param DocumentSnapshot $document
	 *
	 * @return array
	 */
	public function convert_to_array( $document ) {
		$data = $document->data();
		$data['id'] = $document->id();
		return $data;
	}
}
