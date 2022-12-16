<?php
/**
 * Webhook Splitter
 * Digunakan untuk memecah request webhook ke beberapa layanan
 *  
 * @version 1.0.0
 */

/**
 * Ubah kode dari sini
 * ==============================================================
 */


$links = [
    'https://enqjuyfgaqk1t.x.pipedream.net/',
    'https://enqjuyfgaqk1t.x.pipedream.net/2',
];

$config = [
    'ignore_outbox'         => true, // apakah pesan outbox dikirim?
    'ignore_status'         => true, // apaka status pengiriman dikirim?
    'ignore_group'          => true, // apakah pesan dari group dikirim?
    'ignore_raw_message'    => true, // apakah pesan raw message dikirim?
];

/**
 * Selesai di sini
 * ==============================================================
 */



final class WebhookSplitter {

    protected $links = [];

    protected $ignore_outbox = false;
    protected $ignore_status = false;
    protected $ignore_group = false;
    protected $ignore_raw_message = true;

    public function __construct(array $links = [], array $config = []) {

        $config = array_merge( 
            [
                'ignore_outbox' => false,
                'ignore_status' => false,
                'ignore_group' => false,
                'ignore_raw_message' => true,
            ],
            $config
        );
        
        $this->links = $links;
        $this->ignore_outbox = $config['ignore_outbox'];
        $this->ignore_status = $config['ignore_status'];
        $this->ignore_group = $config['ignore_group'];
        $this->ignore_raw_message = $config['ignore_raw_message'];
    }

    public static function make(array $links = [], array $config = []) {
        return new static($links, $config);
    }

    public function dispatch() {
        if (empty($this->links)) {
            return 'Empty link';
        }

        $req = $this-> get_json();

        if (empty($req)) {
            return 'request empty';
        }

        if ($this->ignore_status && !isset($req['is_from_me'])) {
            return 'Delivery webhook ignored';
        }

        if ($this->ignore_group && (isset($req['is_group']) && $req['is_group'] === true)) {
            return 'Ignore group message';
        }

        if ($this->ignore_outbox && (isset($req['is_from_me']) && $req['is_from_me'] === true)) {
            return 'Ignore my message';
        }

        if ($this->ignore_raw_message && isset($req['message_raw']) ) {
            unset($req['message_raw']);
        }

        $headers = ['With-Splitter: yes'];
        $allowedHeaders = ['Onesender-Key', 'Api-Key', 'Token', 'X-Api-Key', 'Authorization'];

        foreach(apache_request_headers() as $key => $value) {
            if (in_array($key, $allowedHeaders)) {
                $headers[] = sprintf('%s: %s', $key, $value);
            }
        }

        $output = [];
        foreach($this->links as $link) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $link,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => '',
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 30,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_SSL_VERIFYHOST  => false,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => json_encode($req),
                CURLOPT_HTTPHEADER      => $headers,
            ));

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                echo 'Error:' . curl_error($curl);
            }

            curl_close($curl);

            
            $output[] = $this->autoformat($response);
        }

        echo json_encode($output);
    }

    private function autoformat($string) {
        if (is_string($string)) {
            $arrays = json_decode( $string, true );
            if (json_last_error() === JSON_ERROR_NONE) {
                return $arrays;
            }
        }

        return $string;
    }

    private function get_json() {
        $output = file_get_contents('php://input');
        $output = json_decode( $output, true );
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array();
        }

        return $output;
    }
}

echo WebhookSplitter::make($links, $config)
        ->dispatch();
