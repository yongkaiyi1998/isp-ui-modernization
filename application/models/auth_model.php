<?php

class Auth_model extends MY_Model{

	protected $_table 			= 'user';
	protected $_primary_key 	= 'idx';
	
	protected $_table_2 		= 'acl_role';
	protected $_primary_key_2 	= 'role_no';
	
	protected $_table_3 		= 'acl_entry';
	protected $_primary_key_3 	= 'idx';

    var $auth_check_time = '00:05:00'; // check 5 minutes    
    var $auth_max_login = 15;
    var $auth_max_attempt = 10;
    var $auth_badlogin_block_time='2:00:00';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	function toggle_login($in_out = '0', $username) 
    {		
		$return_val = 0;
		$query_str ="UPDATE user SET logged=".$in_out.", last_login=now() WHERE username ='$username' ";		
		
		$model				= 'action_log_model';
		$this->load->model($model);			
		$ctrl				= $this->router->fetch_class();
		$esc_query_str		= $this->db->escape_str($query_str);	
		$method 			= $this->router->method; 		
		$action_desc		= 'this user has logged '.((empty($in_out))?'out.':'in.');
		$action_category 	= 'others';
		$action_log 		=  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);		
		$return_val 		= $this->db->query($query_str);	
		
		return $return_val;
	}
	
	function get_user_details($username = '', $password = '') 
    {
		$return_val = array( 'row' => '' , 'msg' => '' );		
		$query_str 	= "SELECT * FROM user WHERE username = '".$username."' AND password = '".$password."' LIMIT 1 ";
		$query 		= $this->db->query($query_str);
		
		if ($query->num_rows() > 0)
		{
			$return_val['row'] = $query->row_array();
			if ($return_val['row']['active'] == 0) 
			{
				$return_val['msg'] = 'Account suspended';
			}
		}
		else {
			$return_val['msg'] = 'Bad login or password';
		}
		
				
		return $return_val;
	}
	
	function get_acl_list($user = '') 
    {
		if ( empty($user) ) {
			$user = $this->user['username'];
		}

		$acl_list = array();		
		
		$query_str = "SELECT ae.idx, ae.name, ae.display_name, ar.acl_list
						FROM ".$this->_table." u 
						INNER JOIN ".$this->_table_2." ar ON u.acl_role = ar.role_no 
						INNER JOIN ".$this->_table_3." ae ON ar.acl_list LIKE CONCAT('%,', ae.idx, ':%') 
						WHERE u.username = '" . $user . "'";
		$query = $this->db->query($query_str);
		foreach ( $query->result_array() as $val ) {
			$pos_start = strpos($val['acl_list'],','.$val['idx'].':');
			$pos_end = strpos($val['acl_list'],',',$pos_start+1);
			if ($pos_end)
				$acl_str = substr($val['acl_list'], $pos_start, $pos_end-$pos_start);
			else
				$acl_str = substr($val['acl_list'], $pos_start);

			$acl_arr = explode(':', $acl_str);
			$acl_arr = array_slice($acl_arr, 1);

			$acl_list[$val['name']] = array(
					'id'=>$val['idx'], 
					'display_name'=>$val['display_name'],
					'actions'=>$acl_arr
					);
		}

		return $acl_list;
	}

    function weblogin($username,$password,$code,&$err_msg) {

        /*
           $username and $password not escaped !
        */

        $username = $this->db->escape_str($this->input->post('AUTH_USER', TRUE));
        $password = $this->db->escape_str(md5($this->input->post('AUTH_PW', TRUE)));

        $loginstatus='F';
        try {
            if ($username!='') {
                // $qry="select count(*) as count from auth_log where (acc_username=? or log_ip='".$this->input->ip_address()."') and log_ts>=subtime(now(),'".$this->auth_check_time."')";
                $qry="select count(*) as count from auth_log where acc_username=? and log_ts>=subtime(now(),'".$this->auth_check_time."')";
                $query = $this->db->query($qry,array($username));
                $result=$query->row_array();
                if ($result['count']<$this->auth_max_login) {
                    $qry="SELECT idx,username,auth_acc_status,time_to_sec(timediff(auth_acc_block_until,now())) as until,`active` FROM `user` WHERE username = ? AND `password` = ? LIMIT 1";
                    $query = $this->db->query($qry,array($username,$password));
                    if ($query->num_rows() > 0)
                    {
                        $authrow = $query->row_array();
                        if ($authrow['active'] == 0) {
                            $authrow['auth_acc_status'] = 'D';
                        }
                        switch ($authrow['auth_acc_status']) {
                            case 'B':
                                if ($authrow['until']>0) {
                                    $err_msg = "Account blocked";
                                    break;
                                }
                            case 'D':
                                $err_msg = "Account Deactivated";
                                break;
                            case 'A':
                            case 'S':
                                $qry="select * from `user` where idx=".$authrow['idx'];
                                $query2=$this->db->query($qry);
                                if ($query2->num_rows() > 0) {
                                    $row = $query2->row_array();
                                    $loginstatus = 'S';

                                    //$row['acc_config'] = json_decode($row['acc_config'],true);
                                    //if ($row['acc_config'] == null) $row['acc_config']=array();

                                    $user = array_merge($authrow,$row);

                                    $user_agent = substr($this->input->user_agent(), 0, 100);

                                    // login successful                    
                                    $this->db->query('update `user` set auth_acc_lastlogin=CURRENT_TIMESTAMP,auth_acc_lastlogin_from="'.$this->input->ip_address().
                                        '",auth_acc_lastlogin_agent="'.$user_agent.
                                        '" where idx='.$user['idx']);
                                    $this->db->query("insert into login_log (acc_id,log_ip,log_agent) values (".$row['idx'].",'".$this->input->ip_address()."','".$user_agent."')");
                                    
                                    // load user permissions
                                    /*$query = $this->db->query('select perm from user_perm where acc_id=?',array($row['acc_id']));
                                    $perm_rows = $query->result_array();
                                    $perms=array();
                                    foreach($perm_rows as $perm_row)
                                        $perms[$perm_row['perm']] = true;
                                    foreach ($this->perms as $perm_key=>$perm_name)
                                        $user['perms'][$perm_key] = isset($perms[$perm_key]);*/

                                    // ***************************

                                    //$this->session->set_userdata("user", $user);
                                    //$this->session->set_userdata("acl", $this->get_acl_info($user['login_name']));	
                                    //$this->session->set_userdata("config", $this->get_configinfo());
                                    //session_write_close();
                                    
                                    $this->load->library('Log_maintenance');
                                } else {
                                    $err_msg = "Invalid account"; // Need to "lang" these text
                                }
                                break;                            
                            default:
                                $err_msg = "Account disabled";
                        }
                    } else {
                        $qry="select count(*) as failed from auth_log where acc_username=? and log_ts>=subtime(now(),'".$this->auth_check_time."' and log_status='F')";
                        $query = $this->db->query($qry,array($username));
                        $result=$query->row_array();

                        if ($result['failed']<$this->auth_max_attempt)
                            $err_msg = "Invalid login or password";
                        else {
                            // too many failed attempt, temporary suspend account
                            $this->db->query("update `user` set auth_acc_status='B',auth_acc_block_until=addtime(now(),'".
                                $this->auth_badlogin_block_time."') where username=?",array($username));
                            $err_msg = "Account blocked, too many attempt";
                        }
                    }
                } else {
                    $err_msg = "Login too often";
                }
            }
        } finally {
            $this->db->query("insert into auth_log (acc_username,log_ip,log_status) values (?,'".$this->input->ip_address()."','$loginstatus')",array($username));
        }
    }

    function cweblogin($auth_email,$password,$code,&$err_msg,&$auth_return) {
    	//email = either pic email in profile or key in by itelco staff when send termination form
    	//password = temporary password generated from itelco when send termination form
        $auth_email = $this->db->escape_str($this->input->post('AUTH_EMAIL', TRUE));
        //temp password is not md5
        $password = $this->db->escape_str($this->input->post('AUTH_PW', TRUE));

        $loginstatus='F';
        try {
        	if ($auth_email!='') {
                $qry="select count(*) as count from auth_log where acc_username=? and log_ts>=subtime(now(),'".$this->auth_check_time."')";
                $query = $this->db->query($qry,array($auth_email));
                $result=$query->row_array();
                if ($result['count']<$this->auth_max_login) {

                	//main query to check based on email + temp_password
                    $qry="
					SELECT c.*, csa.expiry AS acc_expiry   
					FROM `customer` c  
					JOIN (
						SELECT acs.* FROM customer_termination_signature acs 
						JOIN (
							SELECT customer_no, MAX(idx) AS max_data_id  
							FROM customer_termination_signature 
							GROUP BY customer_no 
						) bcs ON (acs.idx = bcs.max_data_id) 
					) csa ON (csa.customer_no = c.customer_no) 
					LEFT JOIN profile p ON (p.acc_id = c.profile_id) 
					LEFT JOIN (
						SELECT acs.* FROM customer_termination_data acs 
						JOIN (
							SELECT customer_no, MAX(idx) AS max_data_id  
							FROM customer_termination_data 
							GROUP BY customer_no 
						) bcs ON (acs.idx = bcs.max_data_id) 
					) csa2 ON (csa2.customer_no = c.customer_no) 
					WHERE (p.acc_email = ? OR csa2.pic_email = ?) AND csa.`temp_password` = ? LIMIT 1
                    ";
                    $query = $this->db->query($qry,array($auth_email,$auth_email,$password));

                    if ($query->num_rows() > 0) {
                    	$authrow = $query->row_array();

                    	//check expiry
                    	if (strtotime(date("Y-m-d H:i:s")) < strtotime($authrow['acc_expiry'])) {
                    		$loginstatus = 'S';

                    		$user_agent = substr($this->input->user_agent(), 0, 100);

                            $this->db->query("insert into login_log (acc_id,log_ip,log_agent) values (".$authrow['customer_no'].",'".$this->input->ip_address()."','".$user_agent."')");

                    		$this->load->library('Log_maintenance');

                    		$auth_return = $authrow;
                    	} else {
                    		$err_msg = "Temporary password expired";
                    	}
               	 	} else {

                        $err_msg = "Invalid login or password";
               	 	}

                } else {
                    $err_msg = "Login too often";
                }
        	}
        } finally {
            $this->db->query("insert into auth_log (acc_username,log_ip,log_status) values (?,'".$this->input->ip_address()."','$loginstatus')",array($auth_email));
        }
    }

}
