<?php

namespace WCTokyo\WpCheckin;

use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use Hametuha\SingletonPattern\Singleton;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use WCTokyo\WpCheckin\FireBase;

class TicketApi extends Singleton
{

    /**
     * Search ticket.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function handle_search(Request $request, Response $response, array $args)
    {
        try {
            $query = $request->getQueryParam('s');
            if (!$query) {
                throw new \Exception('検索キーワードが指定されていません。', 404);
            }
            $query = explode(' ', str_replace('　', ' ', $query));
            $result = $this->search_by_name_and_email($query);
            return $response->withJson($result);
        } catch (\Exception $e) {
            return $response->withJson($e);
        }
    }

    /**
     * QRコード画像をチケットIDから取得
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function handle_qrcode(Request $request, Response $response, array $args)
    {
        try {
            $param = $args['attendee_id'];
            $ticket = $this->search_by_attendee_id($param);

            $url = sprintf('%s/ticket/%s', getenv('SERVER_HOST'), $ticket['id']);

        } catch (\Exception $e) {
            $url = getenv('SERVER_HOST');
        } finally {
            $src = str_replace('&amp;', '&', $this->generate_qr($url));
            $content = file_get_contents($src);
            header('Content-Type: image/png');
            echo $content;
            exit;
        }
    }

    /**
     * Handle QR request.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     */
    public function handle_qr(Request $request, Response $response, array $args)
    {
        try {
            $queries = [];
            foreach (['f', 'g', 'e'] as $key) {
                if ($param = $request->getQueryParam($key)) {
                    $queries[] = $param;
                }
            }
            if (!$queries) {
                throw new \Exception('No queries set.');
            }
            $result = $this->search_by_name_and_email($queries);
            if (1 !== count($result)) {
                throw new \Exception('Not found.');
            }
            list($data) = $result;
            $url = sprintf('%s/ticket/%s', getenv('SERVER_HOST'), $data['id']);
        } catch (\Exception $e) {
            $url = getenv('SERVER_HOST');
        } finally {
            $src = str_replace('&amp;', '&', $this->generate_qr($url));
            $content = file_get_contents($src);
            header('Content-Type: image/png');
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
    public function generate_qr($text)
    {
        $url = 'https://chart.apis.google.com/chart?';
        $queries = [];
        foreach ([
                     'cht' => 'qr',
                     'chs' => '300x300',
                     'chl' => $text,
                 ] as $key => $val) {
            $queries[] = sprintf('%s=%s', $key, rawurlencode($val));
        }
        $url .= implode('&amp;', $queries);
        return $url;
    }

    /**
     * Search tickets.
     *
     * @param string[] $query
     *
     * @return array[]
     */
    private function search($query)
    {
        $result = [];
        $tickets = FireBase::get_instance()
            ->db()
            ->collection('Tickets')
            ->documents();
        foreach ($tickets as $ticket) {
            if (!$ticket->exists()) {
                continue;
            }
            $data = $this->convert_to_array($ticket);

            $string = implode('', $data);
            foreach ($query as $q) {
                if (false === strpos($string, $q)) {
                    continue 2;
                }
            }
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Search tickets from Firestore
     *
     * @param $query_params
     * @return array
     */
    private function search_by_name_and_email($query_params)
    {
        try {
            $tickets = [];
            $ticketsRef = FireBase::get_instance()->db()->collection('Tickets');
            if (!$ticketsRef) throw new \Exception('FireStoreに接続できませんでした', 500);

            $first_name_query = $ticketsRef->where('first_name', 'in', $query_params);
            foreach ($first_name_query->documents() as $document) {
                $tickets[] = $this->convert_to_array($document);
            }
            $last_name_query = $ticketsRef->where('last_name', 'in', $query_params);
            foreach ($last_name_query->documents() as $document) {
                $tickets[] = $this->convert_to_array($document);
            }
            $email_query = $ticketsRef->where('email', 'in', $query_params);
            foreach ($email_query->documents() as $document) {
                $tickets[] = $this->convert_to_array($document);
            }
            return $tickets;
        } catch(\Exception $e) {
            return null;
        }
    }

    /**
     * Search tickets from Firestore
     *
     * @param $query_params
     * @return array
     */
    private function search_by_attendee_id($query_params)
    {
        $tickets = [];
        $ticketsRef = FireBase::get_instance()->db()->collection('Tickets');
        $first_name_query = $ticketsRef->where('attendee_id', '=', $query_params);
        foreach ($first_name_query->documents() as $document) {
            $tickets[] = $this->convert_to_array($document);
        }
        return $tickets[0];
    }

    /**
     * Returns JSON.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function handle_get(Request $request, Response $response, array $args)
    {
        $document = $this->get_document($args['ticket_id']);
        if ($document) {
            $document = $this->add_items($document);
            return $response->withJson($document);
        } else {
            return $response->withJson(null, 404);
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
    public function handle_post(Request $request, Response $response, array $args)
    {
        try {
            $document = $this->get_reference($args['ticket_id']);
            if (!$document->snapshot()->exists()) {
                throw new \Exception('該当するチケットが存在しません。', 404);
            }
            $document->update([
                [
                    'path' => 'checked_in_at',
                    'value' => date('Y-m-d H:i:s'),
                ],
            ]);
            return $response->withJson($this->add_items($this->convert_to_array($document->snapshot())));
        } catch (\Exception $e) {
            return $response->withJson([
                'message' => $e->getMessage(),
            ], $e->getCode());
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
    public function handle_delete(Request $request, Response $response, array $args)
    {
        try {
            $document = $this->get_reference($args['ticket_id']);
            if (!$document->snapshot()->exists()) {
                throw new \Exception('該当するチケットが存在しません。', 404);
            }
            $document->update([
                [
                    'path' => 'checked_in_at',
                    'value' => '',
                ],
            ]);
            return $response->withJson($this->convert_to_array($document->snapshot()));
        } catch (\Exception $e) {
            return $response->withJson([
                'message' => $e->getMessage(),
            ], $e->getCode());
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
    public function handle_csv(Request $request, Response $response, array $args)
    {
        try {
            $uploaded_files = $request->getUploadedFiles();
            if (empty($uploaded_files['stat-csv'])) {
                throw new \Exception('CSVファイルが指定されていません。', 400);
            }
            /* @var UploadedFile $file */
            $file = $uploaded_files['stat-csv'];
            if ('text/csv' !== $file->getClientMediaType()) {
                throw new \Exception('CSVファイルの形式が不正です。', 400);
            }
            // List checked in time.
            $updated = [];
            $tickets = FireBase::get_instance()
                ->db()
                ->collection('Tickets')
                ->documents();
            foreach ($tickets as $ticket) {
                /** @var DocumentSnapshot $ticket */
                if (!$ticket->exists()) {
                    continue;
                }
                $data = $this->convert_to_array($ticket);
                if (!empty($data['checked_in_at'])) {
                    $updated[$data['id']] = $data['checked_in_at'];
                }
            }
            // Read CSV.
            $pointer = new \SplFileObject($file->file);
            $pointer->setFlags(\SplFileObject::READ_CSV);
            $output = fopen('php://output', 'w');
            header('Content-Type: text/csv; charset=UTF-8');
            header(sprintf('Content-Disposition: attachment; filename=wp-checkin-stats-%s.csv', date('Ymd')));
            // Output CSV headers.
            fputcsv($output, [
                'attendee_id',
                'status',
                'type',
                'issued_for',
                'bought_by',
                'bought_at',
                'participated',
                'adult',
                'checked_in_at',
            ]);
            // Parse CSV.
            foreach ($pointer as $row) {
                // Skip first line.
                if (1 > $pointer->key() || $pointer->eof()) {
                    continue;
                }
                // Get data.
                $id = $row[0];
                $mail = md5($row[4]);
                $mail_bought = md5($row[11]);
                $bought_at = $row[5];
                $status = $row[7];
                $coupon = $row[9];
                $title = $row[1];
                $over_20 = $row[16];
                $submit = $row[20];
                // Type of attendee.
                if (false !== strpos($coupon, 'sponsor')) {
                    $type = 'sponsor';
                } else if (false !== strpos($coupon, 'organizer')) {
                    $type = 'organizer';
                } else if (false !== strpos($coupon, 'volunteer')) {
                    $type = 'volunteer';
                } elseif (false !== strpos($title, 'マイクロスポンサー')) {
                    $type = 'sponsor';
                } else {
                    $type = 'general';
                }
                $participated = ('Yes' === $submit || isset($updated[$id])) ? 1 : 0;
                if (isset($updated[$id])) {
                    $gmt = $updated[$id];
                    // TODO: Offset.
                    $checked_in = date('Y-m-d H:i:s', strtotime($gmt) + 60 * 60 * 9);
                } else {
                    $checked_in = '';
                }
                $is_adult = (false !== strpos($over_20, 'Yes')) ? 1 : 0;
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
                fputcsv($output, $data);
            }
            exit;
        } catch (\Exception $e) {
            return $response->withStatus($e->getCode())
                ->withHeader('Content-Type', 'text/html')
                ->write($e->getMessage());
        }
    }

    /**
     * Import CSV(WordTicks Export Data) to FireStore
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return mixed
     */
    public function import_csv(Request $request, Response $response, array $args)
    {
        try {
            $ticket_ref = FireBase::get_instance()->db()->collection('Tickets');

            $uploaded_files = $request->getUploadedFiles();
            if (empty($uploaded_files['ticket-csv'])) {
                throw new \RuntimeException('CSVファイルが指定されていません。', 400);
            }

            $file = $uploaded_files['ticket-csv'];
            if ('text/csv' !== $file->getClientMediaType()) {
                throw new \RuntimeException('CSVファイルの形式が不正です。', 400);
            }

            $fp = new \SplFileObject($file->file);
            $fp->setFlags(\SplFileObject::READ_CSV);
            $counter = 0;
            while (!$fp->eof()) {
                $row = $fp->fgetcsv();
                $data = [
                    'attendee_id' => $row[0],
                    'ticket_type' => $row[1],
                    'first_name' => $row[2],
                    'last_name' => $row[3],
                    'email' => $row[4],
                    'purchased_at' => $row[5],
                    'modified_at' => $row[6],
                    'status' => $row[7],
                    'transaction_id' => $row[8],
                    'coupon' => $row[9],
                    'buyer_name' => $row[10],
                    'buyer_email' => $row[11],
                    'meal_preference' => $row[13],
                    'agreement' => $row[14]
                ];
                $doc_ref = $ticket_ref->newDocument();
                $doc_ref->set($data);
                $counter++;
            }

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'text/html')
                ->write(`Imported {$counter} tickets from CSV File`);

        } catch (\Exception $exception) {
            return $response->withStatus($exception->getCode())
                ->withHeader('Content-Type', 'text/html')
                ->write($exception->getMessage());
        }
    }

    /**
     * Get document snapshot.
     *
     * @param string $ticket_id
     *
     * @return DocumentReference
     */
    protected function get_reference($ticket_id)
    {
        return FireBase::get_instance()
            ->db()
            ->collection('Tickets')
            ->document($ticket_id);
    }

    /**
     * Get document.
     *
     * @param string $ticket_id
     * @return array
     */
    protected function get_document($ticket_id)
    {
        $document = $this->get_reference($ticket_id)->snapshot();
        if ($document->exists()) {
            return $this->convert_to_array($document);
        }

        return [];
    }

    /**
     * Convert user data to array.
     *
     * @param DocumentSnapshot $document
     *
     * @return array
     */
    public function convert_to_array($document)
    {
        $data = $document->data();

        $data['id'] = $document->id();
        // Add role.
        $role = '一般参加';
        foreach ([
                     getenv('WORDCAMP_SHORT_NAME') . '-organizer' => 'スタッフ',
                     getenv('WORDCAMP_SHORT_NAME') . '-volunteer' => 'ボランティア',
                     getenv('WORDCAMP_SHORT_NAME') . '-speaker' => 'スピーカー',
                 ] as $coupon => $label) {
            if (isset($data['coupon']) && false !== strpos($data['coupon'], $coupon)) {
                $role = $label;
                break;
            }
        }
        if (isset($data['coupon']) && $role === '一般参加' && strpos($data['coupon'], (getenv('WORDCAMP_SHORT_NAME') . '-')) !== false) {
            $role = 'スポンサー';
        }
        if (false !== strpos($data['ticket_type'], 'マイクロスポンサー')) {
            $role = 'マイクロスポンサー';
        }
        $data['role'] = $role;

        // Add photo agreements
        if ($data['agreement'] === '' || $data['agreement'] === null) {
            $data['agreement'] = 'はい / Yes';
        }

        $sorted = [
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
        ];

        foreach ($data as $key => $val) {
            if (in_array($key, ['last_name', 'first_name'])) {
                continue;
            }
            $sorted[$key] = $val;
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
    public function add_items($document)
    {
        $document['items'] = [
            'ネームカード',
            'トートバッグ',
        ];
        if (false !== strpos($document['role'], 'スポンサー')) {
            $document['items'][] = 'タンブラー(@控室)';
        }
        if (false !== strpos($document['role'], 'スピーカー')) {
            $document['items'][] = 'ガジェットポーチ(@控室)';
        }

        return $document;
    }

}
