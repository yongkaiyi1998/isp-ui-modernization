<?php

class Telegram_model extends MY_Model {

    function __construct()
    {
    	parent::__construct();
    }

    function send( $send_data ) {

        //if got attachment
        if (!empty($send_data['attachment']) && (strpos($send_data['mimetype'], 'image') !== false)) {
            //picture
            return $this->send_image( $send_data );
        } else if (!empty($send_data['attachment'])) {

            //other doc
            return $this->send_document( $send_data );

        } else {

            //message
            $params = array(
                'chat_id' => $send_data['chat_id'],
                'text' => $send_data['text']
            );
            $postdata = http_build_query($params);

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata,
                )
            );

            $url = $this->config->item('telegram_url').$this->config->item('bot_token').'/sendMessage';
            $context = stream_context_create($opts);
            $result = file_get_contents($url, false, $context);

            //echo $url;
            //print_r($result);
    
            return json_decode($result);

        }

    }

    function send_image( $send_data ) {

        $caption = $send_data['text']; 
        $url = $this->config->item('telegram_url').$this->config->item('bot_token')."/sendPhoto";

        $photo = new CURLFile($send_data['attachment']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ["chat_id" => $send_data['chat_id'], "photo" => $photo, "caption" => $caption]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $out = curl_exec($ch);
        curl_close($ch);

        return json_decode($out);
    }

    function send_document( $send_data ) {

        $caption = $send_data['text']; 
        $url = $this->config->item('telegram_url').$this->config->item('bot_token')."/sendDocument";

        $document = new CURLFile($send_data['attachment']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ["chat_id" => $send_data['chat_id'], "document" => $document, "caption" => $caption]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $out = curl_exec($ch);
        curl_close($ch);

        return json_decode($out);
    }

}