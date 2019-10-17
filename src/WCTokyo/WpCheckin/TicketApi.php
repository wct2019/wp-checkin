<?php

namespace WCTokyo\WpCheckin;


use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Hametuha\SingletonPattern\Singleton;
use Slim\Http\Request;
use Slim\Http\Response;

class TicketApi extends Singleton {
	
	/**
	 * Search ticket.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function handle_search( Request $request, Response $response, array $args ) {
		try {
			$query = $request->getQueryParam( 's' );
			if ( ! $query ) {
				throw new \Exception( '検索キーワードが指定されていません。', 404 );
			}
			$result = [];
			$tickets = FireBase::get_instance()
							   ->db()
							   ->collection( 'Tickets' )
							   ->documents();
			foreach ( $tickets as $ticket ) {
				/** @var DocumentSnapshot $ticket */
				if ( ! $ticket->exists() ) {
					continue;
				}
				$data  = $this->convert_to_array( $ticket );
				$string = implode( ' ', $data );
				if ( false !== strpos( $string, $query ) ) {
					$result[] = $data;
				}
			}
			return $response->withJson( $result );
		} catch ( \Exception $e ) {
			return $response->withJson( [], 404 );
		}
	}
	
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
			$document = $this->add_items( $document );
			return $response->withJson( $document );
		} else {
			return $response->withJson( null, 404 );
		}
	}
	
	/**
	 * Handle post request.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function handle_post( Request $request, Response $response, array $args ) {
		try {
			$document = $this->get_reference( $args[ 'ticket_id' ] );
			if ( ! $document->snapshot()->exists() ) {
				throw new \Exception( '該当するチケットが存在しません。', 404 );
			}
			$document->update( [
				[
					'path' => 'checkedin',
					'value' => date( 'Y-m-d H:i:s' ),
				],
			] );
			return $response->withJson( $this->add_items( $this->convert_to_array( $document->snapshot() ) ) );
		} catch ( \Exception $e ) {
			return $response->withJson( [
				'message' => $e->getMessage(),
			], $e->getCode() );
		}
	}
	
	/**
	 * Uncheck document.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function handle_delete( Request $request, Response $response, array $args ) {
		try {
			$document = $this->get_reference( $args[ 'ticket_id' ] );
			if ( ! $document->snapshot()->exists() ) {
				throw new \Exception( '該当するチケットが存在しません。', 404 );
			}
			$document->update( [
				[
					'path' => 'checkedin',
					'value' => '',
				],
			] );
			return $response->withJson( $this->convert_to_array( $document->snapshot() ) );
		} catch ( \Exception $e ) {
			return $response->withJson( [
				'message' => $e->getMessage(),
			], $e->getCode() );
		}
	}
	
	/**
	 * Get document snapshot.
	 *
	 * @param string $ticket_id
	 *
	 * @return DocumentReference
	 */
	protected function get_reference( $ticket_id ) {
		return FireBase::get_instance()
							->db()
							->collection( 'Tickets' )
							->document( $ticket_id );
	}
	
	/**
	 * Get document.
	 *
	 * @param string $ticket_id
	 * @return array
	 */
	protected function get_document( $ticket_id ) {
		$document = $this->get_reference( $ticket_id )->snapshot();
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
		// Add role.
		$role = '一般参加';
		foreach ( [
			'wct-sponsor-2019' => 'スポンサー',
			'wct-staff-2019'   => 'スタッフ',
			'wct-speaker-2019' => 'スピーカー',
		] as $coupon => $label ) {
			if ( false !== strpos( $data['coupon'], $coupon ) ) {
				$role = $label;
				break;
			}
		}
		if ( false !== strpos( $data['category'], 'マイクロスポンサー' ) ) {
			 $role = 'マイクロスポンサー';
		}
		$data['role'] = $role;
		return $data;
	}
	
	/**
	 * Convert array
	 *
	 * @param array $document
	 *
	 * @return array
	 */
	public function add_items( $document ) {
		$document['items'] = [
			'パンフレット',
			'ストラップ',
			'ギグバンド' . ( $document['u20'] ? '（緑）' : '（黄色）' ),
			'ナップサック',
		];
		if ( false !== strpos( $document['role'], 'スポンサー' ) ) {
			$document['items'][] = 'Tシャツ（グレイ）';
		}
		if ( false !== strpos( $document['role'], 'スピーカー' ) ) {
			$document['items'][] = 'Tシャツ（緑）';
		}
		return $document;
	}
}
