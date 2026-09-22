<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Update extends CI_Controller {

	var $debug = false;

    function __construct() {
        parent::__construct();        
        $this->load->library('session');
    }

    function index() {
        redirect('/');
    }
    
	public function sms()
	{
		
		$sid=trim($this->input->get('sid'));

		if(trim($this->input->get('mid')) == ''){
			$err_msg = "Invalid Msg ID";
		}elseif($sid == ''){
			$err_msg = "Invalid session";
		}elseif(trim($this->input->get('did')) == ''){
			$err_msg = "Invalid device ID";
		}elseif(trim($this->input->get('status')) == ''){
			$err_msg = "Invalid status";
		}elseif(trim($this->input->get('parts')) == ''){
			$err_msg = "Invalid parts";
		}else{
			// ***** non-cookie session work-arround *****
			// Modified by SH, after CI 3, session is using native php session
			//~ session_id($sid);
			//~ $this->session->userdata = $_SESSION['ci_session_data'];
			// ********************************************
			
			// ***** non-cookie session work-arround *****
			session_name('cisessionitelco');
			session_id($sid);
			session_start();
			//~ $this->session->userdata = $_SESSION['ci_session_data'];
			// ********************************************
			
			$user = $this->session->userdata;
        	if(empty($user)){
				log_message('error', 'empty user' . $sid );
        		echo "ER%Invalid Session";
        	}else {
				$this->load->database();
				$devid = $this->db->escape($this->input->get('did'));
				$msgid = $this->db->escape($this->input->get('mid'));
				$statusRaw = $this->input->get('status');
				$status = $this->db->escape($statusRaw);	
				$parts 	= $this->db->escape($this->input->get('parts'));

				
				//$this->load->model('sms_outgoing_model');
				//$results = $this->sms_outgoing_model->get_outgoing_by( array( "0"=> "sms_id" , "1"=> $msgid )  ) ; 
				
				//if( !empty( $results[0] ) && $results[0]['sms_id'] != '' ){
					//$this->sms_outgoing_model->update_sms_outgoing_attempt( $msgid );
				//}

				$qry="UPDATE sms_outgoing SET sms_attempt = sms_attempt + 1 WHERE sms_id = $msgid";
				$this->db->query($qry);
				
				$qry="INSERT INTO sms_outarchive (sms_id,acc_id,scheduler_id,scheduled_time,sms_phone,sms_msg,sms_timestamp,sms_processed,sms_status,sms_total,sms_transport,sms_attempt) select sms_id,acc_id,scheduler_id,scheduled_time,sms_phone,sms_msg,sms_timestamp,now(),$status,$parts,sms_transport,sms_attempt FROM sms_outgoing where sms_id=$msgid";
				//$qry="UPDATE sms_outarchive set sms_status=".$status.",sms_total=".$parts.",sms_processed=now() where sms_id=$msgid";

				$this->db->query($qry);
				$this->db->query("DELETE FROM sms_outgoing WHERE sms_id=$msgid");
				if ($statusRaw=='S')
					$supdate = 'dev_sent=dev_sent+1';
				else 
					$supdate = 'dev_failed=dev_failed+1';
				$qry="UPDATE devices SET dev_pending=dev_pending-1,$supdate WHERE dev_number=$devid";
				$this->db->query($qry);

				echo "OK";
			}
		}
		
	}
	
	
}
?>
