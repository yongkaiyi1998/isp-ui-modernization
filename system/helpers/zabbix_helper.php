<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('zabbixRequest')) {
    function zabbixRequest($method = NULL, $params = [], $isAuth = true) {
        $CI =& get_instance();
        $CI->load->config('config', true);

        $host = $CI->config->item('zbxApiHost');
        $path = $CI->config->item('zbxApiPath');
        $isSSL = $CI->config->item('zbxApiIsSSL');
        $apiKey = $CI->config->item('zbxApiKey');

        $baseUri = ($isSSL ? 'https://' : 'http://') . $host;
        $url = $baseUri . $path;

        if (!is_string($method) || json_encode($params) === false) return false;

        $payload = json_encode([
            'jsonrpc' => '2.0',
            'method'  => $method,
            'params'  => $params,
            'id'      => uniqid(getmypid())
        ]);

        $headers = [
            'Content-Type: application/json'
        ];

        if ($isAuth) {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            return false;
        }

        $data = json_decode($response);
        $data = get_object_vars($data);
        if (is_null($data) || !isset($data['result'])) {
            return false;
        }

        return $data['result'];
    }
}

if (!function_exists('callZabbixRequest')) {
    function callZabbixRequest($method = NULL, $params = [], $isAuth = true) {
        $zabbix_api_status = false;
        $return_data = null;

        for ($try = 0; $try < 3; $try++) {
            if ($try > 0) {
                zabbix_log("Retry #$try: Connecting to Zabbix API...", 'error');
            }

            $return_data = zabbixRequest($method, $params, $isAuth);

            if (!empty($return_data)) {
                $zabbix_api_status = true;
                zabbix_log("Zabbix API Connected Successfully.", 'error');
                break;
            }

            zabbix_log("Attempt #$try: Failed to connect to Zabbix API.", 'error');
        }

        if (!$zabbix_api_status) {
            zabbix_log("Zabbix API connection failed after 3 attempts. Exiting.", 'error');
            exit(); 
        }

        return $return_data;
    }
}

if (!function_exists('zabbix_log')) {
    function zabbix_log($msg, $level = 'info')
    {
        $log_path = APPPATH . 'logs/zabbix/';
        if (!is_dir($log_path)) mkdir($log_path, 0755, TRUE);
        $filepath = $log_path . 'zabbix-' . date('Y-m-d') . '.log';
        $message = strtoupper($level) . ' - ' . date('Y-m-d H:i:s') . " --> " . $msg . "\n";
        file_put_contents($filepath, $message, FILE_APPEND);
    }
}

if (!function_exists('clean_zabbix_logs')) {
    function clean_zabbix_logs($days = 30)
    {
        $dir = APPPATH . 'logs/zabbix/';
        if (!is_dir($dir) || !is_really_writable($dir)) {
            return ['status' => false, 'msg' => 'Zabbix log folder not found or not writable.'];
        }

        $files   = glob($dir . '*');
        $deleted = 0;
        $kept    = 0;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < strtotime("-{$days} days")) {
                @unlink($file);
                $deleted++;
            } else {
                $kept++;
            }
        }

        return [
            'status'  => true,
            'total'   => $deleted + $kept,
            'deleted' => $deleted,
            'kept'    => $kept
        ];
    }
}
