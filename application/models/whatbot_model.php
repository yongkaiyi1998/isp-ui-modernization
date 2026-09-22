<?php

class Whatbot_model extends MY_Model {

    function __construct()
    {
    	parent::__construct();
    }

    function send($chatid, $msg, $quoted='', $image='', $mimetype='', $filename='', $clientid='', $isForward='', $forwardMsgId='', 
    $isDelete='', $deleteMsgId='', $massForward='', $isptt=0, $meta_template='', $meta_vars=array()) {

        //captions starting with \n causes the send image function to not work
        if ($mimetype != '') {
            $msg = trim($msg);
        }

        $msg = str_replace("\r\n" , "<br>" , $msg) ;

        //encode before send to whatbot
        $msg = str_replace("\n" , "<br>" , $msg) ;
        $msg = str_replace("\\" , "\\\\" , $msg) ;  

        //temporary to curb forward msg id 
        /*if ($isForward == 1) {
            $this->load->model('Mod_chat');
            $split = explode("_", $forwardMsgId);
            $search_str = $split[1].'_'.$split[2];
            $msg_details = $this->Mod_chat->search_by_msgid($search_str);
            if (strpos($msg_details['chatid'], '@g') !== false) {
                //if chatid is group, append sender id
                $forwardMsgId = $forwardMsgId.'_'.$msg_details['senderid'];
            }
        }*/

        // missing country code for malaysia mobile no
        if (substr($chatid, 0, 2) == '01' && in_array(strlen($chatid), [10, 11])) {
            $chatid = '6' . $chatid;
        }

        $params = array( 
            "chatid" => $chatid,
            "text" => $msg, 
            "quoted" => $quoted,
            "image" => $image, 
            "mimetype" => $mimetype, 
            "filename" => $filename,
            "clientid" => $clientid,
            "accountid" => $this->config->item('whatbot_account'),
            "isForward" => $isForward,
            "forwardMsgId" => $forwardMsgId, 
            "isDelete" => $isDelete, 
            "deleteMsgId" => $deleteMsgId, 
            "massForward" => $massForward,
            "isptt" => $isptt,
            "copy_id" => $this->config->item('copy_id'),
            "meta_template" => $meta_template,
            "meta_vars" => json_encode($meta_vars),

        );
        
        log_message('error', '=========================================== WHATSAPP DEBUG ============================================');
        log_message('error', 'META TEMPLATE: ' . $meta_template);
        log_message('error', 'PARAMS: ' . print_r($params, true));
        log_message('error', '=============================================== END ===================================================');

        $postdata = http_build_query($params);
 

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
            )
        );

        // temporarily commented out for disabling whatsapp sending
        $url = $this->config->item('whatbot_url').'api/queue_outgoing/';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        // //echo $url;
        // //print_r($result);
        // log_message('error', $url);
        // log_message('error', print_r($result, true));

        // return json_decode($result);
        return '';
    }

    function is_new_contact ($mobile_no) {
        $params = array( 
            "phoneno" => $mobile_no,
            "groupid" => $this->config->item('whatbot_account')
        );
        $postdata = http_build_query($params);
 

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
            )
        );

        $url = $this->config->item('whatbot_url').'api/isNewContact/';
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        return json_decode($result);
    }

    function message_in_new_queue($outgoing_id) {

        $sql = "SELECT * FROM `new_whatsapp_contact_scheduler` WHERE `retry_times` < 3 AND outgoing_id = ? ORDER BY retry_times ASC LIMIT 1";

        $query = $this->db->query($sql, [$outgoing_id]);
        if($query->num_rows() > 0) {
            return true;
        }

        return false;
    }

    function new_send($chatid, $msg, $quoted='', $image='', $mimetype='', $filename='', $clientid='', $isForward='', $forwardMsgId='', 
    $isDelete='', $deleteMsgId='', $massForward='', $isptt=0){

        // Your Meta App configuration
        $accessToken = $this->config->item('meta_access_token');
        $phoneNumberId = $this->config->item('meta_phone_id');

        $url = "https://graph.facebook.com/v21.0/".$phoneNumberId."/messages";

        // Message layout payload for Direct Send
        $payload = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $chatid, // Recipient's phone number with country code
            "type" => "text",
            "category" => "utility", // CRITICAL: This invokes the Direct Send API
            "text" => [
                "body" => $msg
            ],
            "ttl_seconds" => 3600 // Optional: Set a custom expiration for the message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            echo "HTTP Status: {$httpCode}\n";
            echo "Response: {$response}\n";
        }

        curl_close($ch);

    }

}
