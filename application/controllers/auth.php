<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//include_once( APPPATH . 'controllers/app_config.php' );
include_once( APPPATH . 'controllers/acl.php' );

class Auth extends CI_Controller {
	
	var $debug = false;
	var $maxPending = 99999;
	
    function __construct() 
    {
        parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->model('auth_model');
		$this->load->model('common_model');
    }

    function index()
    {
        redirect('/');
    }

    function login($page = 'login')
	{
		if(!empty($this->session->userdata['user'])){
			//redirect if user session active
			redirect('home');
		}
		
		if ( ! file_exists('application/views/pages/'.$page.'.php'))
		{
			// Whoops, we don't have a page for that!
			show_404();
		}
		
		$data['title'] = ucfirst($page); // Capitalize the first letter
		//~ $data['captcha'] = $this->generate_login_captcha();

		$this->load->helper('captcha');
		$vals = array(
			'img_path'	 => FCPATH.'captcha/',
			'img_url'	 => $this->config->item('base_url').'captcha/',
			'img_width'	 => 200,
			'img_height' => 40,
			'font_path'	 => FCPATH.'fonts/Gabriola.ttf',
			'font_size'	 => 28,
			'wordlength'=> 4,
			'pool' 	     => '0123456789',
		);
		$data['captcha'] = create_captcha($vals);

		$newdata['captcha'] = $data['captcha'];
		$this->session->set_userdata($newdata);

		$this->load->helper('form');

		if($this->session->flashdata("err_msg")){
			$data['err_msg'] = $this->session->flashdata("err_msg");
		}

		$config_data = $this->common_model->get_table(
			'sys_config',
			'*',
			"`key` IN ('termination_sop')"
		);
		$cfg = array_column($config_data, 'val', 'key');

		//currently its linkede with termination flow but in future if customer portal is used for other features then have to change the logic
		$data['termination_flow'] = $cfg['termination_sop'] ?? 0;

		$this->load->view('templates/login_header', $data);
		$this->load->view('pages/'.$page, $data);
		$this->load->view('templates/login_footer', $data);
	}
	
	function generate_login_captcha()
	{			
		$this->load->helper('captcha');
		$vals = array(
			'img_path'	 => FCPATH.'captcha/',
			'img_url'	 => base_url('captcha').'/',
			'img_width'	 => 300,
			'img_height' => 40,
			'font_path'	 => FCPATH.'fonts/Gabriola.ttf',
			'font_size'	 => 40,
			'wordlength' => 4,
			'pool' 	     => '1234567890',
		);
		$return_val = create_captcha($vals);
		$this->session->set_flashdata('captcha', $return_val);		
		return $return_val;
	}
	
	function validate_login_captcha($post_val,$captcha_val)
	{
		$return_val = '';
		if(trim($post_val) == '') 
			$return_val = 'Please enter captcha code';
		elseif($captcha_val != $post_val) 
			$return_val = 'The captcha code entered was incorrect';		
		return $return_val;
	}
	
	function authenticate()
    {
		//$captcha = $this->session->flashdata("captcha");
		$captcha = $this->session->userdata("captcha");
		$msg = '';
		
		if(!$_POST)
		{
			redirect('/');
		}
		else
		{
			//~ $msg = $this->validate_login_captcha($this->input->post('AUTH_CAPTCHA',true),$captcha['word']);

			if(trim($this->input->post('AUTH_CAPTCHA')) == ''){
				$msg = "Please enter captcha code";
			} else if($captcha['word'] != $this->input->post('AUTH_CAPTCHA')){
				$msg = "The captcha code entered was incorrect";
						
			} else if(trim($this->input->post('AUTH_USER',true)) == ''){
				$msg = 'Please enter username';
			}
			elseif(trim($this->input->post('AUTH_PW',true)) == ''){
				$msg = 'Please enter password';
			}
			else {

				//change to new auth

				$username = $this->input->post('AUTH_USER');
				$password = $this->input->post('AUTH_PW');
				$code = $this->input->post('AUTH_CAPTCHA');

				$this->auth_model->weblogin($username,$password,$code,$msg);

			}

			if (!$msg) 
			{			

				$username = $this->db->escape_str($this->input->post('AUTH_USER', TRUE));
				$password = $this->db->escape_str(md5($this->input->post('AUTH_PW', TRUE)));
				
				$get_user_details = $this->auth_model->get_user_details($username,$password);
				
				$row = array();
				$msg2 = "";

				if(!empty($get_user_details)){	
					if(!empty($get_user_details['msg'])) $msg2 = $get_user_details['msg'];	
					if(!empty($get_user_details['row'])) $row = $get_user_details['row'];	
				}

				if (!$msg2) {

					$acl = $this->auth_model->get_acl_list($username);

					$user_id = $row['idx'];
					$this->load->model('common_model');

					$exist = in_array($user_id, $this->common_model->get_ticket_main_pic());
					if($exist) {
						$acl['trouble_ticket'] = [
							'id' => 17,
							'display_name' => 'Service Ticket',
							'actions' => ['A', 'V', 'M', 'D']
						];
						$acl['customer_support'] = [
							'id' => 17,
							'display_name' => 'Trouble Ticket',
							'actions' => ['V', 'M', 'D']
						];
					}
					$_SESSION['acl'] = $acl;

					$this->session->set_userdata("user", $row);											
					$result = $this->auth_model->toggle_login('1',$username);
				
					redirect('home');

				} else {
					$this->session->set_flashdata("err_msg", $msg2);
					redirect('/');
				}
				
			} else {
				$this->session->set_flashdata("err_msg", $msg);
				redirect('/');
			}
		}
    }

    function logout() {
		//get user id in session
		$username = $this->session->userdata['user']['username'];						
		$result = $this->auth_model->toggle_login('0',$username);
				
		$this->session->sess_destroy(); // destroy current session		
		// session_start(); // no idea why need add this, so i comment this code to avoid the session error
		session_destroy();		
		
		redirect("/");
    }

    function clogout() {
		//get user id in session
				
		$this->session->sess_destroy(); // destroy current session		
		// session_start(); // no idea why need add this, so i comment this code to avoid the session error
		session_destroy();		
		
		redirect("auth/clogin");
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

	/*
	how to do customer temp login
	//one option is to redo the menu, main and allow customer to click to view termination form... but if want to reuse the same view will have challenges
	//can use back the same view but put in different controller
	*/

    function clogin($page = 'clogin')
	{
		if(!empty($this->session->userdata['cuser'])){
			//redirect if user session active
			redirect('chome');
		}
		
		if ( ! file_exists('application/views/pages/'.$page.'.php'))
		{
			// Whoops, we don't have a page for that!
			show_404();
		}
		
		$data['title'] = ucfirst('Customer Portal'); // Capitalize the first letter
		//~ $data['captcha'] = $this->generate_login_captcha();

		$this->load->helper('captcha');
		$vals = array(
			'img_path'	 => FCPATH.'captcha/',
			'img_url'	 => $this->config->item('base_url').'captcha/',
			'img_width'	 => 200,
			'img_height' => 40,
			'font_path'	 => FCPATH.'fonts/Gabriola.ttf',
			'font_size'	 => 28,
			'wordlength'=> 4,
			'pool' 	     => '0123456789',
		);
		$data['captcha'] = create_captcha($vals);

		$newdata['captcha'] = $data['captcha'];
		$this->session->set_userdata($newdata);

		$this->load->helper('form');

		if($this->session->flashdata("err_msg")){
			$data['err_msg'] = $this->session->flashdata("err_msg");
		}
		$this->load->view('templates/login_header', $data);
		$this->load->view('pages/'.$page, $data);
		$this->load->view('templates/login_footer', $data);
	}

	function cauthenticate()
	{
		//$captcha = $this->session->flashdata("captcha");
		$captcha = $this->session->userdata("captcha");
		$msg = '';
		
		if(!$_POST)
		{
			redirect('auth/clogin');
		}
		else
		{

			if(trim($this->input->post('AUTH_CAPTCHA')) == ''){
				$msg = "Please enter captcha code";
			} else if($captcha['word'] != $this->input->post('AUTH_CAPTCHA')){
				$msg = "The captcha code entered was incorrect";
						
			} else if(trim($this->input->post('AUTH_EMAIL',true)) == ''){
				$msg = 'Please enter email';
			}
			elseif(trim($this->input->post('AUTH_PW',true)) == ''){
				$msg = 'Please enter password';
			}
			else {

				//change to new auth

				$auth_email = $this->input->post('AUTH_EMAIL');
				$password = $this->input->post('AUTH_PW');
				$code = $this->input->post('AUTH_CAPTCHA');

				$auth_return = array();

				$this->auth_model->cweblogin($auth_email,$password,$code,$msg, $auth_return);

				if (!$msg) {
					//successfully logged in, do session init
					//for some reason have to manually assign whatever session param that is needed
					$acl['customer'] = [
						'id' => 1,
						'display_name' => 'Account',
						'actions' => ['V']
					];
					$_SESSION['acl'] = $acl;

					$this->session->set_userdata("cuser", [
						'customer_no' => $auth_return['customer_no'],
						'customer_name' => $auth_return['name'],
					]);	

					redirect('chome');
				} else {
					$this->session->set_flashdata("err_msg", $msg);
					redirect('auth/clogin');
				}

			}
		}
	}



    //~ function get_acl_list($user = '') {
		//~ if ( empty($user) ) {
			//~ $user = $this->user['username'];
		//~ }
//~ 
		//~ $acl_list = array();
		//~ $query_str = "SELECT ae.idx, ae.name, ae.display_name, ar.acl_list
						//~ FROM user u 
						//~ INNER JOIN acl_role ar ON u.acl_role = ar.role_no 
						//~ INNER JOIN acl_entry ae ON ar.acl_list LIKE CONCAT('%,', ae.idx, ':%') 
						//~ WHERE u.username = '" . $user . "'";
		//~ $query = $this->db->query($query_str);
		//~ foreach ( $query->result_array() as $val ) {
			//~ $pos_start = strpos($val['acl_list'],','.$val['idx'].':');
			//~ $pos_end = strpos($val['acl_list'],',',$pos_start+1);
			//~ if ($pos_end)
				//~ $acl_str = substr($val['acl_list'], $pos_start, $pos_end-$pos_start);
			//~ else
				//~ $acl_str = substr($val['acl_list'], $pos_start);
//~ 
			//~ $acl_arr = explode(':', $acl_str);
			//~ $acl_arr = array_slice($acl_arr, 1);
//~ 
			//~ $acl_list[$val['name']] = array(
					//~ 'id'=>$val['idx'], 
					//~ 'display_name'=>$val['display_name'],
					//~ 'actions'=>$acl_arr
					//~ );
		//~ }
//~ 
		//~ return $acl_list;
	//~ }
    
   /*function get_acl_list($user = '') {
		if ( empty($user) ) {
			$user = $this->user['username'];
		}

		$acl_list = array();
		$query_str = "SELECT ae.name, ae.display_name, ar.acl_list
						FROM user u 
						INNER JOIN acl_role ar ON u.acl_role = ar.role_no 
						INNER JOIN acl_entry ae ON ar.acl_list LIKE CONCAT('%,', ae.idx, ':%') 
						WHERE u.username = '" . $user . "'";
		$query = $this->db->query($query_str);
		foreach ( $query->result_array() as $val ) {
			$acl_list[$val['name']] = $val['display_name'];
		}
		return $acl_list;
	}*/

}
?>
