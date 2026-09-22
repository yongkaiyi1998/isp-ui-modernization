<?php
class Profile_model extends MY_Model{

	protected $_table = 'profile';
	protected $_primary_key = 'acc_id';

	public function __construct()
	{
		parent::__construct();
		
	}

	public function get_profile_listing($page_item_no=0,$query_where=array()) {
		$return_val['total_row'] = 0;

		$where = '';
		$where_txt = '';
		//$query_where is array with search param
		if (isset($query_where['txt_search'])) {
			if (!empty($query_where['txt_search'])) {

				$query_where['txt_search'] = $this->db->escape_str($query_where['txt_search']);

				$where_txt .= " AND (
				p.acc_name LIKE '%".$query_where['txt_search']."%' 
				OR p.icno LIKE '%".$query_where['txt_search']."%' 
				OR p.acc_mobileno LIKE '%".$query_where['txt_search']."%'
				OR p.acc_email LIKE '%".$query_where['txt_search']."%' 
				OR p.comp_name LIKE '%".$query_where['txt_search']."%' 
				)";
			}
		}

		$where_txt .= " AND p.acc_type != 'A'";

		$query_str= "SELECT count(*) as total_row FROM profile p " .
					"WHERE 1=1 ".$where_txt." ".$where;
					
		$query = $this->db->query($query_str);
		$return_val['total_row'] = $query->row(0)->total_row;

		$query_str = "
		SELECT p.*, sys_pt.name AS acc_type_text, CASE WHEN p.acc_type = 'r' THEN p.acc_name ELSE p.comp_name END AS profile_name FROM profile p LEFT JOIN sys_profile_type sys_pt ON (p.acc_type = sys_pt.category_code) WHERE 1=1 ".$where_txt." ".$where." 
		ORDER BY profile_name ASC LIMIT $page_item_no, ".$_SESSION['config']['max_page_item'];

		$query = $this->db->query($query_str);
		
		$return_val['row'] = ($query->num_rows() > 0) ? $query->result_array() : array();
		$return_val['sql'] = $query_str ;
		return $return_val;

	}

	public function get_profile($acc_id = '')
	{
		$query = $this->db->query("SELECT p.*, pa.acc_username, '' AS acc_password, p.acc_id AS temp_id  
							FROM profile p 
							LEFT JOIN profile_auth pa ON (p.acc_id = pa.acc_id) 
							WHERE p.acc_id='" . $this->db->escape_str($acc_id) . "'
							LIMIT 1");

		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();

			/*if($return_val['signup_date'] == 0){
				$return_val['signup_date'] = '';
			}
			if($return_val['activated_date'] == 0){
				$return_val['activated_date'] = '';
			}
			if($return_val['suspended_date'] == 0){
				$return_val['suspended_date'] = '';
			}
			if($return_val['terminated_date'] == 0){
				$return_val['terminated_date'] = '';
			}
			if($return_val['next_bill_date'] == 0){
				$return_val['next_bill_date'] = '';
			}*/
			$return_val['btn_delete'] = 'enabled';
		} else {
			//Asign blank data for add new customer
			$return_val['acc_id'] = 0;
			$return_val['acc_name'] = '';
			$return_val['acc_type'] = '';
			$return_val['grp_id'] = '';
			$return_val['icno'] = '';
			$return_val['acc_mobileno'] = '';
			$return_val['agent_id'] = 0;
			$return_val['acc_email'] = '';
			$return_val['comp_name'] = '';
			$return_val['ssm'] = '';
			$return_val['tin'] = '';
			$return_val['sst'] = '';
			$return_val['ttx'] = '';
			$return_val['acc_lang'] = 'EN';
			$return_val['acc_config'] = '';
			$return_val['bill_unit_no'] = '';
			$return_val['bill_addr_1'] = '';
			$return_val['bill_addr_2'] = '';
			$return_val['bill_addr_3'] = '';
			$return_val['bill_postcode'] = '';
			$return_val['bill_city'] = '';
			$return_val['bill_state'] = 'pg';

			$return_val['so_id'] = '0';

			$return_val['acc_username'] = '';
			$return_val['acc_password'] = '';

			$return_val['pic_designation'] = '';
			$return_val['pic_name'] = '';
			$return_val['pic_mobile'] = '';
			$return_val['pic_email_1'] = '';
			$return_val['pic_email_2'] = '';
			$return_val['pic_nric_passport'] = '';
			$return_val['pic_dob'] = '';
			$return_val['pic_gender'] = '';
			$return_val['pic_race'] = '';

			$return_val['marital_status'] = '';
			$return_val['household'] = '';
			$return_val['nationality'] = 'Malaysian';
			$return_val['no_of_employee'] = '0';
			$return_val['no_of_branches'] = '0';

			$return_val['btn_delete'] = 'disabled';

			$return_val['temp_id'] = rand(10000, 99999);
		}

		return $return_val;
	}

	public function profile_insert($post_back)
	{
		// Set default for empty value
		$post_back['no_of_employee'] = normalize_input($post_back['no_of_employee'], 'int', 0);
		$post_back['no_of_branches'] = normalize_input($post_back['no_of_branches'], 'int', 0);

		//need to create auth first if add

		//change log text here
		$new = 0;
		$change_username = 0;
		$change_password = 0;
		$change_text = '';
		if (empty($post_back['acc_id'])) {
			//new
			$new = 1;

			//if new, need to craete auth
			$query_str = "INSERT INTO `profile_auth` (`acc_username`, `acc_password`, `acc_status`) VALUES (?, ?, ?)";

			$this->db->query($query_str, array(
				$post_back['acc_username'],
				md5($post_back['acc_password']),
				'A'
			));

			$acc_id = $this->db->insert_id();
			$post_back['acc_id'] = $acc_id;

		} else {
			//update
			$profileInfo = $this->get_profile($post_back['acc_id']);

			if (!empty($profileInfo)) {
				$this->load->helper('change_log');

				$state_arr = array_column($this->common_model->get_state_list() ?? [], 'name', 'state_code');

				$change_text .= compare_field_change('Name', $profileInfo['acc_name'], $post_back['acc_name'], [], true, $this->db);
				$change_text .= compare_field_change('IC No.', $profileInfo['icno'], $post_back['icno'], [], true, $this->db);
				$change_text .= compare_field_change('Mobile No.', $profileInfo['acc_mobileno'], $post_back['acc_mobileno'], [], true, $this->db);
				$change_text .= compare_field_change('Email', $profileInfo['acc_email'], $post_back['acc_email'], [], true, $this->db);
				$change_text .= compare_field_change('Company Name', $profileInfo['comp_name'], $post_back['comp_name'], [], true, $this->db);
				$change_text .= compare_field_change('SSM#', $profileInfo['ssm'], $post_back['ssm'], [], true, $this->db);
				$change_text .= compare_field_change('TIN', $profileInfo['tin'], $post_back['tin'], [], true, $this->db);
				$change_text .= compare_field_change('Bill Unit No', $profileInfo['bill_unit_no'], $post_back['bill_unit_no'], [], true, $this->db);
				$change_text .= compare_field_change('Bill Address Line 1', $profileInfo['bill_addr_1'], $post_back['bill_addr_1'], [], true, $this->db);
				$change_text .= compare_field_change('Bill Address Line 2', $profileInfo['bill_addr_2'], $post_back['bill_addr_2'], [], true, $this->db);
				$change_text .= compare_field_change('Bill Address Line 3', $profileInfo['bill_addr_3'], $post_back['bill_addr_3'], [], true, $this->db);
				$change_text .= compare_field_change('Bill Postcode', $profileInfo['bill_postcode'], $post_back['bill_postcode'], [], true, $this->db);
				$change_text .= compare_field_change('Bill City', $profileInfo['bill_city'], $post_back['bill_city'], [], true, $this->db);
				$change_text .= compare_field_change('Billing State', $profileInfo['bill_state'], $post_back['bill_state'], $state_arr, true, $this->db);
				$change_text .= compare_field_change('PIC Designation', $profileInfo['pic_designation'], $post_back['pic_designation'][0], [], true, $this->db);
				$change_text .= compare_field_change('PIC Name', $profileInfo['pic_name'], $post_back['pic_name'][0], [], true, $this->db);
				$change_text .= compare_field_change('PIC Mobile', $profileInfo['pic_mobile'], $post_back['pic_mobile'][0], [], true, $this->db);
				$change_text .= compare_field_change('PIC Email 1', $profileInfo['pic_email_1'], $post_back['pic_email_1'][0], [], true, $this->db);
				$change_text .= compare_field_change('PIC Email 2', $profileInfo['pic_email_2'], $post_back['pic_email_2'][0], [], true, $this->db);
				$change_text .= compare_field_change('PIC IC/Passport', $profileInfo['pic_nric_passport'], $post_back['pic_nric_passport'][0], [], true, $this->db);
				
				$username_text = compare_field_change('Username', $profileInfo['acc_username'], $post_back['acc_username'], [], true, $this->db);
				if ($username_text) {
					$change_text .= $username_text;
					$change_username = 1;
				}

				$post_acc_password = $this->db->escape_str($post_back['acc_password']);
				if (!empty($post_acc_password)) {
					$change_text .= "Password Reset to {$post_acc_password}. ";
					$change_password = 1;
				}
			}
		}

		if (isset($post_back['pic_dob'][0])) {
			if (empty($post_back['pic_dob'][0])) {
				$date_of_birth = NULL;
			} else {
				$date_of_birth = $post_back['pic_dob'][0];
			}
		} else {
			$date_of_birth = NULL;
		}

		$sql = "INSERT INTO `profile` (
			acc_id, `acc_name`, icno, acc_mobileno, acc_email, `acc_type`, comp_name, ssm, tin, sst, ttx, bill_unit_no, bill_addr_1, bill_addr_2, bill_addr_3, bill_postcode, bill_city, bill_state, pic_designation, pic_name, pic_mobile, pic_email_1, pic_email_2, pic_nric_passport, pic_dob, pic_gender, pic_race, marital_status, household, nationality, no_of_employee, no_of_branches, so_id, created_at, created_by, agent_id
		) VALUES (
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?,?,?,?,?,
			?     
		) ON DUPLICATE KEY UPDATE 
			`acc_name` = ?, 
			icno = ?,
			acc_mobileno = ?,
			acc_email = ?,
			`acc_type` = ?,
			comp_name = ?,
			ssm = ?,
			tin = ?,
			sst = ?,
			ttx = ?,
			bill_unit_no = ?,
			bill_addr_1 = ?,
			bill_addr_2 = ?,
			bill_addr_3 = ?,
			bill_postcode = ?,
			bill_city = ?,
			bill_state = ?, 
			pic_designation=?,
			pic_name=?,
			pic_mobile=?,
			pic_email_1=?,
			pic_email_2=?,
			pic_nric_passport=?,
			pic_dob=?,
			pic_gender=?,
			pic_race=?,
			marital_status=?,
			household=?,
			nationality=?,
			no_of_employee=?,
			no_of_branches=?,
			so_id=?,
			updated_at=?,
			updated_by=? ,
			agent_id=?
		";

		$values_array = array(
			/*insert*/
			$post_back['acc_id'],
			$post_back['acc_name'],
			$post_back['icno'],
			$post_back['acc_mobileno'],
			$post_back['acc_email'],
			$post_back['acc_type'],
			$post_back['comp_name'],
			$post_back['ssm'],
			$post_back['tin'],
			$post_back['sst'],
			$post_back['ttx'],
			$post_back['bill_unit_no'],
			$post_back['bill_addr_1'],
			$post_back['bill_addr_2'],
			$post_back['bill_addr_3'],
			$post_back['bill_postcode'],
			$post_back['bill_city'],
			$post_back['bill_state'],
			$post_back['pic_designation'][0] ?? '',
			$post_back['pic_name'][0] ?? '',
			$post_back['pic_mobile'][0] ?? '',
			$post_back['pic_email_1'][0] ?? '',
			$post_back['pic_email_2'][0] ?? '',
			$post_back['pic_nric_passport'][0] ?? '',
			$date_of_birth,
			$post_back['pic_gender'][0] ?? '',
			$post_back['pic_race'][0] ?? '',
			$post_back['marital_status'],
			$post_back['household'],
			$post_back['nationality'],
			$post_back['no_of_employee'],
			$post_back['no_of_branches'],
			$post_back['so_id'],
			date('Y-m-d H:i:s'),
			$post_back['user_idx'],
			$post_back['agent_id'],
			/*update*/
			$post_back['acc_name'],
			$post_back['icno'],
			$post_back['acc_mobileno'],
			$post_back['acc_email'],
			$post_back['acc_type'],
			$post_back['comp_name'],
			$post_back['ssm'],
			$post_back['tin'],
			$post_back['sst'],
			$post_back['ttx'],
			$post_back['bill_unit_no'],
			$post_back['bill_addr_1'],
			$post_back['bill_addr_2'],
			$post_back['bill_addr_3'],
			$post_back['bill_postcode'],
			$post_back['bill_city'],
			$post_back['bill_state'],
			$post_back['pic_designation'][0] ?? '',
			$post_back['pic_name'][0] ?? '',
			$post_back['pic_mobile'][0] ?? '',
			$post_back['pic_email_1'][0] ?? '',
			$post_back['pic_email_2'][0] ?? '',
			$post_back['pic_nric_passport'][0] ?? '',
			$date_of_birth,
			$post_back['pic_gender'][0] ?? '',
			$post_back['pic_race'][0] ?? '',
			$post_back['marital_status'],
			$post_back['household'],
			$post_back['nationality'],
			$post_back['no_of_employee'],
			$post_back['no_of_branches'],
			$post_back['so_id'],
			date('Y-m-d H:i:s'),
			$post_back['user_idx'],
			$post_back['agent_id'],
		);

		$this->db->query($sql, $values_array);

		if (empty($post_back['acc_id'])) {
			$post_back['acc_id'] = $this->db->insert_id();
		}

		if ($change_username == 1) {
			$this->change_login_id($post_back['acc_id'], $post_back['acc_username']);
		}

		if ($change_password == 1) {
			$this->change_password($post_back['acc_id'], $post_back['acc_password']);
		}

		$customer_action_model			= 'customer_action_log_model';
		$this->load->model($customer_action_model);

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($sql.print_r($values_array, true));
		$method 		= $this->router->method;
		if ($new == 1) {
			$action_desc	= 'a new profile (' . $this->db->escape_str($post_back['acc_name']) . ') has been added';
			$action_category = 'insert';
		} else {
			$action_desc	= 'a profile(' . $this->db->escape_str($post_back['acc_name']) . ') has been edited.' . $change_text;
			$action_category = 'update';

			//also update log in customer accounts related to this profile
			$customer_action_log =  $this->$customer_action_model->profile_update_log($ctrl, $method, $esc_query_str, $action_desc, $action_category, $post_back['acc_id']);
		}
		$action_log =  $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category);

		//other pic
		foreach( $post_back['pic_id'] AS $key => $val ){
			if ($key == 0) {
				continue;
			}

			//if delete
			if ( $post_back['pic_id'][$key] != '' && !empty($post_back['delete'][$key])) {
				$pic_sql = "DELETE FROM profile_pic WHERE pic_id = ? ";
				$this->db->query( $pic_sql, array($post_back['pic_id'][$key]) );

				$esc_query_str	= $this->db->escape_str($pic_sql);					
				$action_desc	= 'Profile('.$this->db->escape_str($post_back['acc_name']).') PIC '.($key+1).' is deleted.';	
				$action_category = 'delete';
				$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$post_back['acc_id']);

				//also update log in customer accounts related to this profile
				$customer_action_log =  $this->$customer_action_model->profile_update_log($ctrl, $method, $esc_query_str, $action_desc, $action_category, $post_back['acc_id']);

				continue;
			}

			if( $post_back['pic_id'][$key] == '' ){

				if (isset($post_back['pic_dob'][$key])) {
					if (empty($post_back['pic_dob'][$key])) {
						$date_of_birth = "NULL";
					} else {
						$date_of_birth = "'".$this->db->escape_str($post_back['pic_dob'][$key])."'";
					}
				} else {
					$date_of_birth = "NULL";
				}

				$pic_sql =	"INSERT INTO profile_pic 
								SET acc_id = '".$post_back['acc_id']."' , 
									pic_designation = '".($post_back['pic_designation'][$key] ?? '')."' , 
									pic_name = '".($post_back['pic_name'][$key] ?? '')."' , 
									pic_mobile = '".($post_back['pic_mobile'][$key] ?? '')."' , 
									pic_email_1 = '".($post_back['pic_email_1'][$key] ?? '')."' , 
									pic_email_2 = '".($post_back['pic_email_2'][$key] ?? '')."' , 
									pic_nric_passport = '".($post_back['pic_nric_passport'][$key] ?? '')."' , 
									pic_dob = ".$date_of_birth." , 
									pic_gender = '".$this->db->escape_str( ($post_back['pic_gender'][$key] ?? '') )."' , 
									pic_race = '".$this->db->escape_str( ($post_back['pic_race'][$key] ?? '') )."' , 
									created_on = NOW() , 
									created_by = '".$this->db->escape_str( $post_back['user_idx'] )."'; ";

				$esc_query_str	= $this->db->escape_str($pic_sql);					
				$action_desc	= 'Profile('.$this->db->escape_str($post_back['acc_name']).') added new PIC ('.($key+1).').';	
				$action_category = 'insert';
				$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$post_back['acc_id']);

				//also update log in customer accounts related to this profile
				$customer_action_log =  $this->$customer_action_model->profile_update_log($ctrl, $method, $esc_query_str, $action_desc, $action_category, $post_back['acc_id']);

			}else{

				if (isset($post_back['pic_dob'][$key])) {
					if (empty($post_back['pic_dob'][$key])) {
						$date_of_birth = "NULL";
					} else {
						$date_of_birth = "'".$this->db->escape_str($post_back['pic_dob'][$key])."'";
					}
				} else {
					$date_of_birth = "NULL";
				}

				//log text
				$change_text = '';
				$picInfo = $this->get_profile_pic_by_pic_id( $post_back['acc_id'], $post_back['pic_id'][$key]);
				if (!empty($picInfo)) {

					$post_pic_name = $this->db->escape_str( $post_back['pic_name'][$key]);
					if ($picInfo['pic_name'] != $post_pic_name) {
						$change_text .= ' PIC Name changed from '.$picInfo['pic_name'].' to '.$post_pic_name.'. ';
					}

					$post_pic_designation = $this->db->escape_str( $post_back['pic_designation'][$key]);
					if ($picInfo['pic_designation'] != $post_pic_designation) {
						$change_text .= ' PIC Designation changed from '.$picInfo['pic_designation'].' to '.$post_pic_designation.'. ';
					}

					$post_pic_mobile = $this->db->escape_str( $post_back['pic_mobile'][$key]);
					if ($picInfo['pic_mobile'] != $post_pic_mobile) {
						$change_text .= ' PIC Mobile changed from '.$picInfo['pic_mobile'].' to '.$post_pic_mobile.'. ';
					}

					$post_pic_email_1 = $this->db->escape_str( $post_back['pic_email_1'][$key]);
					if ($picInfo['pic_email_1'] != $post_pic_email_1) {
						$change_text .= ' PIC Email 1 changed from '.$picInfo['pic_email_1'].' to '.$post_pic_email_1.'. ';
					}

					$post_pic_email_2 = $this->db->escape_str( $post_back['pic_email_2'][$key]);
					if ($picInfo['pic_email_2'] != $post_pic_email_2) {
						$change_text .= ' PIC Email 2 changed from '.$picInfo['pic_email_2'].' to '.$post_pic_email_2.'. ';
					}

					$post_pic_nric_passport = $this->db->escape_str( $post_back['pic_nric_passport'][$key]);
					if ($picInfo['pic_nric_passport'] != $post_pic_nric_passport) {
						$change_text .= ' PIC NRIC/Passport changed from '.$picInfo['pic_nric_passport'].' to '.$post_pic_nric_passport.'. ';
					}

					$post_pic_dob = $this->db->escape_str( $post_back['pic_dob'][$key]);
					if ($picInfo['pic_dob'] != $post_pic_dob) {
						$change_text .= ' PIC DOB changed from '.$picInfo['pic_dob'].' to '.$post_pic_dob.'. ';
					}

				}

				$pic_sql =	"UPDATE profile_pic 
								SET pic_designation = '".($post_back['pic_designation'][$key] ?? '')."' , 
									pic_name = '".($post_back['pic_name'][$key] ?? '')."' , 
									pic_mobile = '".($post_back['pic_mobile'][$key] ?? '')."' , 
									pic_email_1 = '".($post_back['pic_email_1'][$key] ?? '')."' , 
									pic_email_2 = '".($post_back['pic_email_2'][$key] ?? '')."' , 
									pic_nric_passport = '".($post_back['pic_nric_passport'][$key] ?? '')."' , 
									pic_dob = ".$date_of_birth." , 
									pic_gender = '".$this->db->escape_str( ($post_back['pic_gender'][$key] ?? '') )."' , 
									pic_race = '".$this->db->escape_str( ($post_back['pic_race'][$key] ?? '') )."' , 
									updated_on = NOW() , 
									updated_by = '". $this->db->escape_str( $post_back['user_idx'] ) ."'
								WHERE pic_id = '".$post_back['pic_id'][$key]."'; ";

				if (!empty($change_text)) {
					$esc_query_str	= $this->db->escape_str($pic_sql);					
					$action_desc	= 'Profile('.$this->db->escape_str($post_back['acc_name']).') PIC '.($key+1).' updated.'.$change_text;	
					$action_category = 'update';
					$action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category,$post_back['acc_id']);

					//also update log in customer accounts related to this profile
					$customer_action_log =  $this->$customer_action_model->profile_update_log($ctrl, $method, $esc_query_str, $action_desc, $action_category, $post_back['acc_id']);

				}

			}

			$this->db->query( $pic_sql );

		}

		return $post_back['acc_id'];

	}

	public function register($data)
	{
		$return = array();
		$return['err'] = '';
		$return['acc_id'] = 0;

		//validation
		if (empty($data['login'])) {
			$return['err'] = 'Missing login id';
			return $return;
		}

		$query_str = "INSERT INTO `profile_auth` (`acc_username`, `acc_password`, `acc_status`) VALUES (?, ?, ?)";

		$this->db->query($query_str, array(
			$data['login'],
			md5($data['password']),
			'A'
		));

		$acc_id = $this->db->insert_id();
		$return['acc_id'] = $acc_id;

		$query_str	= "INSERT INTO `profile` (acc_id, acc_name, acc_type, grp_id, acc_mobileno, 
					acc_email, acc_lang, acc_config, created_at, created_by) VALUES (
					?,?,?,?,?,
					?,?,?,NOW(),? 
				)";

		$this->db->query($query_str, array(
			$acc_id,
			$data['name'],
			$data['type'],
			0,
			$data['mobile'],
			$data['email'],
			'EN',
			NULL, 
			$data['created_by'] 
		));

		$model			= 'action_log_model';
		$this->load->model($model);
		$ctrl			= $this->router->fetch_class();
		$esc_query_str	= $this->db->escape_str($query_str);
		$method 		= $this->router->method;
		$action_desc	= 'a new profile (' . $this->db->escape_str($data['name']) . ') has been added';
		$action_category = 'insert';
		$action_log =  $this->$model->save_action($ctrl, $method, $esc_query_str, $action_desc, $action_category);

		return $return;

	}

	public function change_login_id($acc_id, $login_id)
	{
		$this->db->query("UPDATE `profile_auth` SET acc_username = ? WHERE acc_id = ? ", array($login_id, $acc_id));
	}

	public function change_password($acc_id, $password)
	{
		$this->db->query("UPDATE `profile_auth` SET acc_password = ? WHERE acc_id = ? ", array(md5($password), $acc_id));
	}

	public function get_profile_listing_all()
	{
		$sql = "SELECT * FROM `profile` ";

		$query = $this->db->query( $sql ) ;
		$results = $query->result_array();

		$return = array();
		foreach ($results as $row) {
			$return[$row['acc_id']] = $row;
		}

		return $return;
	}

	public function get_profile_pic( $acc_id ){
		
		$return_val = array();
		
		$sql = " SELECT *, acc_id AS pic_acc_id FROM profile_pic WHERE acc_id = ? ";
		$query = $this->db->query($sql, array($acc_id));
		if ($query->num_rows() > 0) {
			$return_val = $query->result_array();
		}
	
		return $return_val;
		
	}

	function get_profile_pic_by_pic_id($acc_id, $pic_id) {
		$return_val = array();
		
		$sql = " SELECT * FROM profile_pic WHERE acc_id = '".$acc_id."' and pic_id = '".$pic_id."' ";
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
		}
	
		return $return_val;
	}

	function check_profile_by_ic($icno) {
		$acc_id = 0;
		
		$sql = " SELECT * FROM profile WHERE icno = ? ";
		$query = $this->db->query($sql, array(trim($icno)));
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$acc_id = $return_val['acc_id'];
		}
	
		return $acc_id;
	}

	function check_profile_by_email_from_so($email, $so_id) {
		$acc_id = 0;

		$qwhere = '';
		$val_arr = array(trim($email));
		/*if (!empty($so_id)) {
			$qwhere = ' AND so.so_id != ? ';
			$val_arr = array(trim($email), $so_id);
		}*/
		
		$sql = " 
		SELECT p.* FROM profile p 
		LEFT JOIN so_head so ON (so.so_id = p.so_id) 
		WHERE p.acc_email = ? ".$qwhere;

		$query = $this->db->query($sql, $val_arr);
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$acc_id = $return_val['acc_id'];
		}
	
		return $acc_id;
	}

	function check_profile_by_ssm_from_so($ssm, $type, $so_id) {
		$profile = [];

		$qwhere = '';
		$val_arr = array(trim($ssm), $type);
		
		$sql = " 
		SELECT p.* FROM profile p 
		LEFT JOIN so_head so ON (so.so_id = p.so_id) 
		WHERE p.ssm = ? AND p.acc_type = ? ".$qwhere;

		$query = $this->db->query($sql, $val_arr);
		if ($query->num_rows() > 0) {
			$profile = $query->row_array();
		}
	
		return $profile;
	}

	function check_profile_by_ic_from_so($icno, $type, $so_id) {
		$profile = [];

		$qwhere = '';
		$val_arr = array(trim($icno), $type);

		
		$sql = " 
		SELECT p.* FROM profile p 
		LEFT JOIN so_head so ON (so.so_id = p.so_id) 
		WHERE p.icno = ? AND p.acc_type = ? ".$qwhere;

		$query = $this->db->query($sql, $val_arr);
		if ($query->num_rows() > 0) {
			$profile = $query->row_array();
		}
	
		return $profile;
	}

	function check_profile_by_ic_and_type($icno, $type, $acc_id) {
		$return_acc_id = 0;

		$qwhere = '';
		$val_arr = array(trim($icno), $type);
		if (!empty($acc_id)) {
			$qwhere = ' AND acc_id != ? ';
			$val_arr = array(trim($icno), $type, $acc_id);
		}
		
		$sql = " SELECT * FROM profile WHERE icno = ? AND acc_type = ? ".$qwhere;
		$query = $this->db->query($sql, $val_arr);
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$return_acc_id = $return_val['acc_id'];
		}
	
		return $return_acc_id;
	}

	function check_profile_by_ssm_and_type($ssm, $type, $acc_id) {
		$return_acc_id = 0;

		$qwhere = '';
		$val_arr = array(trim($ssm), $type);
		if (!empty($acc_id)) {
			$qwhere = ' AND acc_id != ? ';
			$val_arr = array(trim($ssm), $type, $acc_id);
		}
		
		$sql = " SELECT * FROM profile WHERE ssm = ? AND acc_type = ? ".$qwhere;
		$query = $this->db->query($sql, $val_arr);
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$return_acc_id = $return_val['acc_id'];
		}
	
		return $return_acc_id;
	}

	function check_profile_by_email($email, $acc_id) {
		$return_acc_id = 0;

		$qwhere = '';
		$val_arr = array(trim($email));
		if (!empty($acc_id)) {
			$qwhere = ' AND acc_id != ? ';
			$val_arr = array(trim($email), $acc_id);
		}
		
		$sql = " SELECT * FROM profile WHERE acc_email = ?".$qwhere;
		$query = $this->db->query($sql, $val_arr);
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$return_acc_id = $return_val['acc_id'];
		}
	
		return $return_acc_id;
	}

	function check_profile_by_username($username, $ignore_acc_id = 0) {
		$acc_id = 0;
		
		$sql = " SELECT * FROM profile_auth WHERE acc_username = ? ";
		
		if (!empty($ignore_acc_id)) {
			$sql .= " AND acc_id != ? ";
			$query = $this->db->query($sql, array(trim($username), $ignore_acc_id));
		} else {
			$query = $this->db->query($sql, array(trim($username)));
		}

		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$acc_id = $return_val['acc_id'];
		}
	
		return $acc_id;
	}

	function check_profile_by_ssm($ssm) {
		$acc_id = 0;
		
		$sql = " SELECT * FROM profile WHERE ssm = ? ";
		$query = $this->db->query($sql, array(trim($ssm)));
		if ($query->num_rows() > 0) {
			$return_val = $query->row_array();
			$acc_id = $return_val['acc_id'];
		}
	
		return $acc_id;
	}

	function chk_username_exists($new_username, $current_username) {
		$sql = "SELECT acc_username FROM profile_auth WHERE acc_username = ? AND acc_username != ?";
		$query = $this->db->query($sql, array($new_username, $current_username));

		return ($query->num_rows() > 0);
	}

	function update_dealer_profile_acc_status($acc_id, $acc_status) {
		if($acc_status == 'r') $acc_status = 'A';
		$this->db->query("UPDATE `profile_auth` SET acc_status = ? WHERE acc_id = ? ", array(strtoupper($acc_status), $acc_id));
	}

	function get_user_profile_notification_info($acc_id) {
		$profile = [];
		$sql = "SELECT acc_id, acc_name, acc_email, 
				CASE
				WHEN allow_whatsapp = 1 THEN acc_mobileno
				ELSE '' END AS acc_phone,
				CASE 
				WHEN allow_telegram = 1 THEN telegram_id
				ELSE '' END AS acc_telegram
				FROM `profile` WHERE acc_id = ?
				";
		$query = $this->db->query($sql, [$acc_id]);
		$result = $query->row_array();
		if(!empty($result)) {
			$profile = [
				'acc_id' => $result['acc_id'],
				'acc_name' => $result['acc_name'],
				'acc_email' => $result['acc_email'],
				'acc_phone' => $result['acc_phone'],
				'acc_telegram' => $result['acc_telegram']
			];
		}
		return $profile;
	}
}