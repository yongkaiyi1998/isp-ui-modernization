<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Smsd extends CI_Controller {

	var $debug = false;
	var $maxPending = 99999;

	function __construct() {
        parent::__construct();        
    }

    function index() {
        //redirect('/');
        if( $this->input->get('userid') != '' && $this->input->get('userpw') != '' &&
			$this->input->get('app') != '' && $this->input->get('number') ){
				
			log_message('error', $this->input->get('userid') . ' -> ' . $this->input->get('userpw') );
			$this->mlogin();
		}else{
			log_message('error', var_dump($this->input->get()) );
			$this->sms_update();
		}
        
    }

	//http://localhost/itelco/smsd/mlogin?userid=infonal&userpw=123&app=itelco&number=0125852990&ui=0
	function mlogin()
	{
		if(trim($this->input->get('userid')) == ''){
			$err_msg = "Invalid user ID";
		}elseif(trim($this->input->get('userpw')) == ''){
			$err_msg = "Invalid password";
		}elseif(trim($this->input->get('app')) == ''){
			$err_msg = "Invalid App";
		}elseif(trim($this->input->get('number')) == '') {
			$err_msg = "Invalid number";
		}else{
			$db = $this->load->database('default', TRUE );
			$username = $this->db->escape($this->input->get('userid'));
			$password = $this->db->escape($this->input->get('userpw'));
			$numberraw = $this->input->get('number');
			$number = $this->db->escape($numberraw);
			
		$str = "SELECT * FROM user 
				WHERE username='".$this->db->escape_str($this->input->get('userid'))."' 
				AND password = '".$this->db->escape_str( $this->input->get('userpw') )."'  
				LIMIT 1 ";
					  
			$query = $this->db->query( $str ); 
			
			
			if ($query->num_rows() > 0) 
			{
				$row = $query->row_array();
				if($row['active'] == 1){
					$user['userid'] = $row['username'];
				}else{
					$err_msg = "Account disabled";
				}
			}else{
				$err_msg = "Bad login or password" ;
			}
		}
		
		if(isset($err_msg)){
			echo "ER%".$err_msg;
		}else{
			$this->session->set_userdata("user", $user);
			$this->session->set_userdata("number",$numberraw);

			$qry="INSERT INTO devices (dev_number) VALUES ($number) ON DUPLICATE KEY UPDATE dev_lastcheckin=CURRENT_TIMESTAMP";
			$this->db->query($qry);
			$sms_id='';
			$no='';
			$text='';

			$qry="SELECT dev_active,dev_pending,dev_failed FROM devices where dev_number=$number";
			$query=$this->db->query($qry);
			if ($query->num_rows() > 0) {
   				$row = $query->row_array(); 
   				if ($row['dev_active']==0) {
   					echo "ER%Device deactivated";
   					return;
   				}
   				elseif ($row["dev_pending"]>=$this->maxPending) {
   					echo "ER%Too many pending SMS";
   					$this->db->query("UPDATE devices set dev_active=0 where dev_number=$number");
   					return;
   				}

   			}
   			else {
   				// something very wrong here...
   				error_log("Missing device record for number $number");
   				// create new device record
   				$this->db->query("INSERT INTO devices dev_number,dev_active) values ($number,0)"); // default to disabled pending authorization from admin
   				echo "ER%Unregistered device";
   			}

			if ($this->input->get('ui')!='1')
			{
				$this->db->trans_start();
				$query = $this->db->query("SELECT sms_id,sms_phone,sms_msg FROM sms_outgoing WHERE sms_status='N' AND ( NOW() > UNIX_TIMESTAMP(scheduled_time) ) ORDER BY scheduled_time LIMIT 1 FOR UPDATE");
				if ($query->num_rows() > 0)
				{
					$row = $query->row_array();
					$sms_id=$row['sms_id'];
					$no=$row['sms_phone'];
					$text=$row['sms_msg'];
					$this->db->query("UPDATE sms_outgoing SET sms_status='P',sms_transport= $number WHERE sms_id=".$row['sms_id']);
					$this->db->query("UPDATE devices set dev_pending=dev_pending+1 where dev_number=$number");
				}
				$this->db->trans_complete();
			}
			echo "OK%".$this->session->userdata['session_id']."%".$sms_id."%".$no."%".$text;
			$_SESSION['ci_session_data'] = $this->session->userdata;
			// *******************************************
			if ($this->debug) {
				echo "\n<pre>";
				print_r($this->session->userdata);
				echo "</pre>\n";
			}
		}
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
			session_id($sid);
			$this->session->userdata = $_SESSION['ci_session_data'];
			// ********************************************
			$user = $this->session->userdata("user");
        	if(empty($user))
        		echo "ER%Invalid Session";
        	else {
				$this->load->database();
				$devid = $this->db->escape($this->input->get('did'));
				//$devid = '0122831682';
				
				$msgid = $this->db->escape($this->input->get('mid'));
				//$msgid = '107';
				
				$statusRaw = $this->input->get('status');
				//$statusRaw = 'S';
				$status = $this->db->escape($statusRaw);	
						
				$parts = $this->db->escape($this->input->get('parts'));
				//$parts = 1 ;

				$qry="INSERT INTO sms_outarchive (sms_id,acc_id,scheduler_id,scheduled_time,sms_phone,sms_msg,sms_timestamp,sms_processed,sms_status,sms_total,sms_transport) "
					."select sms_id,acc_id,scheduler_id,scheduled_time,sms_phone,sms_msg,sms_timestamp,now(),$status,$parts,sms_transport from sms_outgoing where sms_id=$msgid";
				//$qry="UPDATE sms_outarchive set sms_status=".$status.",sms_total=".$parts.",sms_processed=now() where sms_id=$msgid";
				$this->db->query($qry);
				$this->db->query("delete from sms_outgoing where sms_id=$msgid");
				if ($statusRaw=='S')
					$supdate = 'dev_sent=dev_sent+1';
				else 
					$supdate = 'dev_failed=dev_failed+1';
				$qry="UPDATE devices SET dev_pending=dev_pending-1,$supdate WHERE dev_number=$devid";
				$this->db->query($qry);

				echo "OK";
			}
		}
		
		echo $err_msg ; 
		
	}

	public function sms_update()
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
			session_name('ci_session');
			session_id($sid);
			session_start();
			$this->session->userdata = $_SESSION['ci_session_data'];
			// ********************************************
			
			$user = $this->session->userdata("user");
        	if(empty($user))
        		echo "ER%Invalid Session";
        	else {
				$this->load->database();
				$devid = $this->db->escape($this->input->get('did'));
				$msgid = $this->db->escape($this->input->get('mid'));
				$statusRaw = $this->input->get('status');
				$status = $this->db->escape($statusRaw);	
				$parts = $this->db->escape($this->input->get('parts'));
				

				$qry="INSERT INTO sms_outarchive (sms_id,acc_id,scheduler_id,scheduled_time,sms_phone,sms_msg,sms_timestamp,sms_processed,sms_status,sms_total,sms_transport) "
					."select sms_id,acc_id,scheduler_id,scheduled_time,sms_phone,sms_msg,sms_timestamp,now(),$status,$parts,sms_transport from sms_outgoing where sms_id=$msgid";
				//$qry="UPDATE sms_outarchive set sms_status=".$status.",sms_total=".$parts.",sms_processed=now() where sms_id=$msgid";
				$this->db->query($qry);
				$this->db->query("delete from sms_outgoing where sms_id=$msgid");
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
