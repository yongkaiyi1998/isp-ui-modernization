<?php

class Docs_log_model extends MY_Model{
	
	public function __construct()
	{
		parent::__construct();
	}

    function log_item($data) {

        $insert = array();
        foreach ($data as $key => $val) {
            $insert[] = '("' . $this->db->escape_str($val['acc_id']) . '", "' . $this->db->escape_str($val['customer_no']) . '", "' . $this->db->escape_str($val['user_id']) . '", "' . $this->db->escape_str($val['controller']) . '", "' . $this->db->escape_str($val['doc_id']) . '", "' . $this->db->escape_str($val['send_type']) . '", "' . $this->db->escape_str($val['remark']) . '", "'.$this->db->escape_str($val['send_method']).'")';
        }

        /*
        acc_id = Profile ID
        customer_no = Account ID
        //acc_id is compulsory but customer_no is not
        user_id = Backend User ID
        controller = from where using this model
        doc_id = the document_id if applicable
        send_type = either email, whatsapp or telegram
        remark = additional remark
        send_method = either auto or manual
        */

        if (!empty($insert)) {
            $sql = 'INSERT INTO `docs_send_log` (`acc_id`, `customer_no`, `user_id`, `controller`, `doc_id`, `send_type`, `remark`, `send_method`) 
            VALUES ' . implode(', ', $insert);
           
            $this->db->query($sql);
        }

    }

    function get_log($doc_type, $doc_ref='') {
        $return_val = array();

        $query = $this->db->query('SELECT * FROM docs_send_log WHERE doc_type = ? AND doc_ref = ? ORDER BY date_created desc', array($this->db->escape_str($doc_type), $this->db->escape_str($doc_ref)));

        if (!empty($query->result_array())) {
            $return_val = $query->result_array();
        }

        return $return_val;
    }

    //one function to email, one to whatsapp, one to telegram

    //individual models brushed up to make sure templates are standardized and logs are saved

    /*
	old function
	- will acccept a list of contacts from both whatsapp and email
	- will operate puppeteer to screenshot for attachment
	- send out to each of the contacts and also for custom contacts as well

	new solution
    - call 1 function, maybe differentiate by type
    - function check type, for example if its send to customer - then need check customer setting which route (email or whatsapp etc) they using
    - function compiles a list, for email, whatsapp, telegram
    - attachment must give full path location , pass to this function
    - after compiling list, then arrange send, email first, then whatsapp, then telegram based on list of contact
    - for each send, save a record at docs_send_log
	
    */

    function do_send( $send_data ) {

        $this->load->model('email_model');
        $this->load->model('whatbot_model');
        $this->load->model('telegram_model');

        $this->load->model('common_model');

        $send_result = array('status' => 'err', 'msg' => '');

        /* list of type
        - To notify profile/customer
        - To notify user in backend
        - To notify a certain type of user in backend i.e technician
        - Custom , system will send list of email, whatsapp, telegram
        */

        $email_list = array();
        $whatsapp_list = array();
        //telegram uses telegram chat_id
        $telegram_list = array();

        $name_list = array();

        $filename = '';
        $mimetype = '';

        if (!empty($send_data['attachment'])) {
            $pathinfo = pathinfo($send_data['attachment']);
            $filename = $pathinfo['filename'];
            $mimetype = mime_content_type($send_data['attachment']);
        }

        if ($send_data['send_type'] == 'to_customer') {

            //later when telegram and whatsapp have notify tick, update this sql
            $values_array = array();
            if (!empty($send_data['customer_no'])) {
                $sql = "
                SELECT p.acc_name, p.acc_mobileno, p.acc_email, p.comp_name, p.telegram_id, p.allow_telegram, p.allow_whatsapp
                FROM customer c 
                LEFT JOIN profile p ON (c.profile_id = p.acc_id) 
                WHERE c.customer_no = ? 
                ";
                $values_array = array($send_data['customer_no']);
            } else {
                $sql = "
                SELECT p.acc_name, p.acc_mobileno, p.acc_email, p.comp_name, p.telegram_id, p.allow_telegram, p.allow_whatsapp
                FROM profile p 
                WHERE p.acc_id = ? 
                ";
                $values_array = array($send_data['acc_id']);
            }

            $query = $this->db->query($sql, $values_array);
            $customer_info = $query->row_array();

            //check if user got tick notification for whatsapp or telegram
            $email_list[] = $customer_info['acc_email'];
            if ($customer_info['allow_whatsapp'] == '1') {
                $whatsapp_list[] = $customer_info['acc_mobileno'];
            }
            if ($customer_info['allow_telegram'] == '1') {
                $telegram_list[] = $customer_info['telegram_id'];
            }

            //$name_list[$customer_info['acc_email']] = $customer_info['acc_name'];

        } else if ($send_data['send_type'] == 'to_backend_user') {

            $sql = "
            SELECT mobile_no AS acc_mobileno, email AS acc_email, display_name AS acc_name, '' AS comp_name, telegram_id, allow_telegram, allow_whatsapp
            FROM `user` WHERE idx = ? 
            ";
            $values_array = array($send_data['user_id']);

            $query = $this->db->query($sql, $values_array);
            $user_info = $query->row_array();

            $email_list[] = $user_info['acc_email'];
            
            if ($user_info['allow_whatsapp'] == '1') {
                $whatsapp_list[] = $user_info['acc_mobileno'];
            }
            if ($user_info['allow_telegram'] == '1') {
                $telegram_list[] = $user_info['telegram_id'];
            }

        } else if ($send_data['send_type'] == 'to_technician') {
        } else if ($send_data['send_type'] == 'custom') {
            $email_list = $send_data['email_list'] ?? array();
            $whatsapp_list = $send_data['whatsapp_list'] ?? array();
            $telegram_list = $send_data['telegram_list'] ?? array();
        } else {
            $send_result['msg'] = 'Send type not recognized.';
            return $send_result;
        }

        //check for duplicates here before initiate send
        // array_filter($array)-remove empty values like '', null, 0
        // array_unique(...)-remove duplicate values
        // array_values(...)-reindexe the array (make suer the keys are 0,1,2,...)
        $email_list     = array_values(array_unique(array_filter($email_list)));
        $whatsapp_list  = array_values(array_unique(array_filter($whatsapp_list)));
        $telegram_list  = array_values(array_unique(array_filter($telegram_list)));

        $smtp_user  = $this->common_model->get_table('sys_config','*', "`category` = 'email' AND `key` = 'from_name' ");
        
        $from_name = ( isset( $smtp_user[0]['val'] ) && $smtp_user[0]['val'] != '' ) ? $smtp_user[0]['val'] : 'no_reply@itelco.net';

        $send_result['status'] = 'succ';

        //send email - put in email scheduler?
        //maybe bypass email scheduler if not needed, during report, do a union on both email scheduler and also doc send log
        if (!empty($email_list)) {
            foreach ($email_list as $key => $email) {
                $email_result = $this->email_model->generate_html_email(
                    $send_data['subject'],
                    $send_data['body'], 
                    '', 
                    $email, 
                    $from_name, 
                    ( (isset($send_data['acc_name'])) ? $send_data['acc_name'] : ''), 
                    ( (isset($send_data['email_starter'])) ? $send_data['email_starter'] : $send_data['subject'] ),
                    $send_data['attachment']
                 );

                if ($email_result == '1') {
                    $log_data[] = array(
                        'acc_id'               =>  $send_data['acc_id'] ?? 0,
                        'customer_no'       =>  $send_data['customer_no'] ?? 0,
                        'user_id'   =>  $send_data['user_id'] ?? 0,
                        'controller'             =>  $send_data['controller'],
                        'doc_id'               =>  $send_data['doc_id'],
                        'send_type'                =>  'email',
                        'remark'           =>  'Email ' . $send_data['doc_type'] . ' to ' . $email,
                        'send_method' => $send_data['send_method'],
                    );

                    $this->log_item($log_data);
                    unset($log_data);
                } else {
                    $send_result['status'] = 'err';
                    $send_result['msg'] .= print_r($email_result, true)."\n";
                }

            }
        }

        if (!empty($whatsapp_list)) {
            $send_data['body'] = str_replace('<br />', "<br>", $send_data['body']);
            foreach ($whatsapp_list as $key => $phone) {
                $chat_id = $phone;

                if (!empty($send_data['attachment']) && !empty($mimetype) && !empty($filename)) {
                    $mimetype       = $mimetype;
                    $temp_img       = file_get_contents($send_data['attachment']);
                    $base64_img     = base64_encode($temp_img);
                    $filename       = $filename;
                } else {
                    $mimetype       = '';
                    $base64_img     = '';
                    $filename       = '';
                }

                $whatsapp_doc_result = $this->whatbot_model->send(
                    $chat_id, 
                    (!empty($send_data['subject'])?$send_data['subject']."\n":"").$send_data['body'], 
                    '', 
                    $base64_img, 
                    $mimetype, 
                    $filename, 
                    $send_data['acc_id'] ?? 0,
                    '',
                    '',
                    '',
                    '',
                    '',
                    0,
                    $send_data['meta_template'] ?? '',
                    $send_data['meta_vars'] ?? []
                );

                $bill_messaging_remark = $send_data['remark'] ?? '';
                if (!isset($send_data['send_bill_messaging'])) {
                    $bill_messaging_remark = 'Whastapp ' . $send_data['doc_type'] . ' to ' . $phone;
                }

                $log_data[] = array(
                    'acc_id'               =>  $send_data['acc_id'] ?? 0,
                    'customer_no'       =>  $send_data['customer_no'] ?? 0,
                    'user_id'   =>  $send_data['user_id'] ?? 0,
                    'controller'             =>  $send_data['controller'],
                    'doc_id'               =>  $send_data['doc_id'],
                    'send_type'                =>  'whatsapp',
                    'remark'           =>  $bill_messaging_remark,
                    'send_method' => $send_data['send_method'],
                );

                $this->log_item($log_data);
                unset($log_data);

            }
        }

        if (!empty($telegram_list)) {
            foreach ($telegram_list as $key => $telegram_id) {
                // <br> is not supported
                // <div>, <p>, <img>, etc. are not allowed
                // so I need to replace <br> with \n
                $send_data['body'] = str_replace('<br>', "\n", $send_data['body']);
                $send_data['body'] = str_replace('<br />', "\n", $send_data['body']);

                $send_array = array();
                $send_array['chat_id'] = $telegram_id;
                $send_array['text'] = (!empty($send_data['subject'])?$send_data['subject']."\n":"").$send_data['body'];
                $send_array['attachment'] = $send_data['attachment'];
                $send_array['mimetype'] = $mimetype;
                $send_array['filename'] = $filename;

                $telegram_doc_result = $this->telegram_model->send($send_array);
                if (is_object($telegram_doc_result) && isset($telegram_doc_result->ok) && $telegram_doc_result->ok == true) {

                    $bill_messaging_remark = $send_data['remark'] ?? '';
                    if (!isset($send_data['send_bill_messaging'])) {
                        $bill_messaging_remark = 'Telegram ' . $send_data['doc_type'] . ' to ' . ((isset($send_data['acc_name'])) ? $send_data['acc_name'] : '') . ' Chat ID (' . $telegram_id . ')';
                    }

                    $log_data[] = array(
                        'acc_id'        => $send_data['acc_id'] ?? 0,
                        'customer_no'   => $send_data['customer_no'] ?? 0,
                        'user_id'       => $send_data['user_id'] ?? 0,
                        'controller'    => $send_data['controller'],
                        'doc_id'        => $send_data['doc_id'],
                        'send_type'     => 'telegram',
                        'remark'        => $bill_messaging_remark,
                        'send_method'   => $send_data['send_method'],
                    );
                
                    $this->log_item($log_data);
                    unset($log_data);
                } else {
                    $send_result['status'] = 'err';
                    $send_result['msg'] .= is_scalar($telegram_doc_result)
                        ? $telegram_doc_result
                        : print_r($telegram_doc_result, true) . "\n";
                }
            }
        }

        return $send_result;

    }

    function get_docs_log($description ='',$page_item_no='', $query_where='')
    {
        $return_val['data'] = array();
        $return_val['num_rows'] = '0';

        $query_str= " SELECT SQL_CALC_FOUND_ROWS a.*, p.acc_name AS customer_name, u.username AS user_name " .
                    " FROM `docs_send_log` a " . 
                    " LEFT JOIN `profile` p ON (a.acc_id = p.acc_id) " . 
                    " LEFT JOIN `user` u ON (a.user_id = u.idx) " . 
                    " WHERE (a.remark LIKE '%$description%' ) " .
                    " $query_where ". 
                    " ORDER BY a.date_created DESC ".
                    " LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];

        $query      = $this->db->query($query_str);
        $get_count  = $this->db->query('SELECT FOUND_ROWS() AS `Count`');

        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            /*foreach($result as $r_key => $r_val) {
                $result[$r_key]['date_modified'] = datetime_toggle($result[$r_key]['date_modified'],$_SESSION['config']['datetime_format']);
            }*/

            $return_val['data']     = $result;
            $return_val['num_rows'] = $get_count->row()->Count;
        }
        return $return_val;
    }

}