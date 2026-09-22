<?php
class Einvoice_api_model extends MY_Model{

	/*protected $_table 			= 'invoice_head';
	protected $_detail 			= 'invoice_details';
	protected $_primary_key 	= 'invoice_id';
	protected $_primary_ke_2	= 'invoice_detail_id';
	protected $_fkey 			= 'cust_id';
	protected $_unique_key 		= 'invoice_num';*/

	public function __construct()
	{
        parent::__construct();

        $api_url = $this->config->item('api_url');
	}

	//API login
	public function api_login( $data ) {

        if (empty($data['api_path']??'')) {
            return json_decode(array());
        }

        $params = array( 
            "client_id" => $data['client_id'],
            "client_secret" => $data['client_secret'],
            "grant_type" => $data['grant_type'],
            "scope" => $data['scope'],

        );
        $postdata = http_build_query($params);
 

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
            )
        );

        $url = $data['api_path'].'connect/token';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return json_decode($result);

	}

	public function get_document_types( $data ) {

		//store client secret and what not in config file
		$token = $data['token'];
		//echo $token;
 
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => "Content-Type: application/json en\r\n" .
                    "Authorization: Bearer ".$token."\r\n",
                //'content' => $postdata,
            )
        );

        $url = $data['api_path'].'api/v1.0/documenttypes';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return json_decode($result);

	}

	//maybe function to create an invoice record to track e-invoicing? maybe can fused with workflow invoice?

	//send function to portal

	//request status from portal

	public function send_api( $data ) {
		//send json to portal

        if (empty($data['api_path']??'')) {
            return json_decode(array());
        }

        $token = $data['token'];

        $params = array( 
            "documents" => $data['documents'], 

        );

        //dont use http_build_query if header is expecting json
        $postdata = json_encode($params);

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => "Content-Type: application/json en\r\n" .
                    "Authorization: Bearer ".$token."\r\n",
                'content' => $postdata,
            )
        );

        $url = $data['api_path'].'api/v1.0/documentsubmissions';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return json_decode($result);

	}

	public function check_status ( $data ) {
		//use uuid to check for e-invoice status 
        $token = $data['token'];
        //echo $token;
 
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => "Content-Type: application/json en\r\n" .
                    "Authorization: Bearer ".$token."\r\n",
                //'content' => $postdata,
            )
        );

        $url = $data['api_path'].'api/v1.0/documents/'.$data['uuid'].'/details';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return json_decode($result);
	}

    public function cancel_invoice( $data ) {

        if (empty($data['api_path']??'')) {
            return json_decode(array());
        }

        $token = $data['token'];

        $params = array( 
            "status" => $data['status'], 
            "reason" => $data['reason'],

        );

        //dont use http_build_query if header is expecting json
        $postdata = json_encode($params);

        //log_message('error', print_r($postdata, true));

        $opts = array('http' =>
            array(
                'method'  => 'PUT',
                'header'  => "Content-Type: application/json en\r\n" .
                    "Authorization: Bearer ".$token."\r\n",
                'content' => $postdata,
            )
        );

        $url = $data['api_path'].'api/v1.0/documents/state/'.$data['uuid'].'/state';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return json_decode($result);
    }

    public function validate_tin( $data ) {

        //use uuid to check for e-invoice status 
        $token = $data['token'];
        //echo $token;
 
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => "Content-Type: application/json en\r\n" .
                    "Authorization: Bearer ".$token."\r\n",
                //'content' => $postdata,
            )
        );

        $url = $data['api_path'].'api/v1.0/taxpayer/validate/'.$data['tin'].'?idType='.$data['type'].'&idValue='.$data['idvalue'];
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return ($result !== false);

    }

    public function search_taxpayer_tin($data) {

        $token = $data['token'];
 
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => "Content-Type: application/json en\r\n" .
                    "Authorization: Bearer ".$token."\r\n",
            )
        );

        $url = $data['api_path'].'api/v1.0/taxpayer/search/tin?'
                .'taxpayerName='.$data['name'];

        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        //echo $url;
        //print_r($result);
        log_message('error', $url);
        log_message('error', print_r($result, true));

        return ($result !== false);

    }

    public function return_qr_link( $data ) {
        //https://preprod.myinvois.hasil.gov.my/XRT5XTE06K98WHD5YFZJ8X2J10/share/C8P7M30JAPRP61CBYFZJ8X2J10wFEpWJ1721116491

        return $data['portal'].$data['uuid'].'/share/'.$data['longid'];
    }

}