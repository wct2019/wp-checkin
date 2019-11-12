<?php

namespace WCTokyo\WpCheckin;


use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Hametuha\SingletonPattern\Singleton;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

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
			$query = explode( ' ', str_replace( '　', ' ', $query ) );
			$result = $this->search( $query );
			return $response->withJson( $result );
		} catch ( \Exception $e ) {
			return $response->withJson( [], 404 );
		}
	}
	
	/**
	 * Handle CSV request.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 */
	public function handle_qr( Request $request, Response $response, array $args ) {
		try {
			$queries = [];
			foreach ( [ 'f', 'g', 'e' ] as $key ) {
				if ( $param = $request->getQueryParam( $key ) ) {
					$queries[] = $param;
				}
			}
			if ( ! $queries ) {
				throw new \Exception( 'No queries set.' );
			}
			$result = $this->search( $queries );
			if ( 1 !== count( $result) ) {
				throw new \Exception( 'Not found.' );
			}
			list( $data ) = $result;
			$url = sprintf( 'https://2019.tokyo.wp-checkin.com/ticket/%d', $data['id'] );
		} catch ( \Exception $e ) {
			$url = 'https://2019.tokyo.wp-checkin.com';
		} finally {
			$src = str_replace( '&amp;', '&', $this->generate_qr( $url ) );
			$content = file_get_contents( $src );
			header( 'Content-Type: image/png' );
			echo $content;
			exit;
		}
	}
	
	/**
	 * Generate image url of qr code.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function generate_qr( $text ) {
		$url = 'https://chart.apis.google.com/chart?';
		$queries = [];
		foreach ( [
			'cht' => 'qr',
			'chs' => '300x300',
			'chl' => $text,
		] as $key => $val ) {
			$queries[] = sprintf( '%s=%s', $key, rawurlencode( $val ) );
		}
		$url .= implode( '&amp;', $queries );
		return $url;
	}
	
	/**
	 * Search tickets.
	 *
	 * @param string[] $query
	 *
	 * @return array[]
	 */
	private function search( $query ) {
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
			$string = implode( '', $data );
			foreach ( $query as $q ) {
				if ( false === strpos( $string, $q ) ) {
					continue 2;
				}
			}
			$result[] = $data;
		}
		return $result;
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
	 * @return Response
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
	 * @return Response
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
	 * Handle CSV request.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return void|Response
	 */
	public function handle_csv( Request $request, Response $response, array $args ) {
		try {
			$uploaded_files = $request->getUploadedFiles();
			if ( empty( $uploaded_files['stat-csv'] ) ) {
				throw new \Exception( 'CSVファイルが指定されていません。', 400 );
			}
			/* @var UploadedFile $file */
			$file = $uploaded_files['stat-csv'];
			if ( 'text/csv' !== $file->getClientMediaType() ) {
				throw new \Exception( 'CSVファイルの形式が不正です。', 400 );
			}
			// List checked in time.
			$updated = [];
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
				if ( ! empty( $data['checkedin'] ) ) {
					$updated[ $data['id'] ] = $data['checkedin'];
				}
			}
			// Read CSV.
			$pointer = new \SplFileObject( $file->file );
			$pointer->setFlags(\SplFileObject::READ_CSV);
			$output = fopen( 'php://output', 'w' );
			header('Content-Type: text/csv; charset=UTF-8');
			header(sprintf( 'Content-Disposition: attachment; filename=wp-checkin-stats-%s.csv', date( 'Ymd' ) ) );
			// Output CSV headers.
			fputcsv( $output, [
				'id',
				'status',
				'type',
				'issued_for',
				'bought_by',
				'bought_at',
				'participated',
				'adult',
				'checked_in_at',
			] );
			// Parse CSV.
			foreach ( $pointer as $row ) {
				// Skip first line.
				if ( 1 > $pointer->key() || $pointer->eof() ) {
					continue;
				}
				// Get data.
				$id          = $row[0];
				$mail        = md5( $row[4] );
				$mail_bought = md5( $row[ 11 ] );
				$bought_at   = $row[5];
				$status      = $row[7];
				$coupon      = $row[9];
				$title       = $row[1];
				$over_20     = $row[16];
				$submit      = $row[20];
				// Type of attendee.
				if ( false !== strpos( $coupon, 'sponsor' ) ) {
					$type = 'sponsor';
				} else if ( false !== strpos( $coupon, 'staff' ) ) {
					$type = 'staff';
				} else if ( false !== strpos( $coupon, 'thanks' ) ) {
					$type = 'thanks';
				} elseif ( false !== strpos( $title, 'マイクロスポンサー' ) )  {
					$type = 'sponsor';
				} else {
					$type = 'general';
				}
				$participated = ( 'Yes' === $submit || isset( $updated[ $id ] ) ) ? 1 : 0;
				if ( isset( $updated[ $id ] ) ) {
					$gmt = $updated[ $id ];
					// TODO: Offset.
					$checked_in = date( 'Y-m-d H:i:s', strtotime( $gmt ) +  60 * 60 * 9 );
				} else {
					$checked_in = '';
				}
				$is_adult = ( false !== strpos( $over_20, 'Yes' ) ) ? 1 : 0;
				$data = [
					$id,
					$status,
					$type,
					$mail,
					$mail_bought,
					$bought_at,
					$participated,
					$is_adult,
					$checked_in,
				];
				fputcsv( $output, $data );
			}
			exit;
		} catch ( \Exception $e ) {
			return $response->withStatus( $e->getCode() )
				->withHeader( 'Content-Type', 'text/html' )
				->write( $e->getMessage() );
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
			if ( isset( $data['coupon'] ) && false !== strpos( $data['coupon'], $coupon ) ) {
				$role = $label;
				break;
			}
		}
		if ( false !== strpos( $data['category'], 'マイクロスポンサー' ) ) {
			 $role = 'マイクロスポンサー';
		}
		$data['role'] = $role;
		$sorted       = [
			'familyname' => $data['familyname'],
			'givenname'  => $data['givenname'],
		];
		foreach ( $data as $key => $val ) {
			if ( in_array( $key, [ 'familyname', 'givenname' ] ) ) {
				continue;
			}
			$sorted[ $key ] = $val;
		}
		return $sorted;
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
			$tshirt = 'Tシャツ（グレイ）';
			if ( ! empty( $document['tshirtsize'] ) ) {
				$tshirt .= ' - ' . $document['tshirtsize'];
			} else {
				$tshirt .= ' - 要サイズ確認';
			}
			$document['items'][] = $tshirt;
		}
		if ( false !== strpos( $document['role'], 'スピーカー' ) ) {
			$document['items'][] = 'Tシャツ（緑） - 要サイズ確認';
		}
		
		return $document;
	}
}
