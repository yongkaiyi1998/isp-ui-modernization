<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Acl extends DataPage_Controller 
{	
	function __construct() 
	{
        parent::__construct();
		check_acl('acl');
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		$this->load->model('acl_model');
    }
    
    public function index()
    {
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('acl_filter', $default_data, $post_data);

		$data = $return['data'];

		$row_html = $this->acl_rows(1);

		$data['page_title'] 	= 'ACL Listing';
		$data['form_action'] 	= base_url('acl');	
		$data['msg'] 			= $this->msg;
		$data['row_html']		= $row_html;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('acl/index',$data);
		$this->load->view('templates/footer');
	}

	public function acl_rows($returnOnly = 0)
	{
		$data = array();
		$post_data = $this->input->post() ?? array();
		$default_data = [
			'page_item_no' => 0,
			'txt_search' => ''
		];
		$return = get_filtered_ajax_data('acl_filter', $default_data, $post_data);

		$data = $return['data'];

		$limit_offset 	= array($_SESSION['config']['max_page_item'],$data['page_item_no']);
		$order_by 		= array('name','asc');	
		$arr 			= array("name LIKE '%".$this->db->escape_str($data['txt_search']) ."%' ");	
		$row	 		= $this->acl_model->get_listing($limit_offset,$order_by,$arr);

		$query = $this->db->query('SELECT FOUND_ROWS() AS `Count`');	
		$total_row = $query->row()->Count ?? 0;

		$data['pagination'] = paginationSettingsAjax('', $total_row, $data['page_item_no'], $_SESSION['config']['max_page_item']);
		$data['row_data'] = (empty($row))? array() : $row;

		$html = $this->parser->parse('acl/acl_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}
	
	function add_acl()
	{
		$data['input'] 				= $this->acl_model->get_acl();
		$data['sel_acl_role_list'] 	= $this->acl_model->get_acl_role_list();		
		$data['page_title'] 		= 'ACL Role';
		$data['form_action'] 		= base_url('acl/save_acl');
		$data['msg'] 				= $this->msg;
		 
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('acl/acl_detail',$data);
		$this->load->view('templates/footer');
	}
	
	function edit_acl($role_no='')
	{	
		$role = $this->acl_model->get_acl($role_no);
		if ($role['role_no']=='') {
			$this->session->set_flashdata("warning_msg", 'Role not found!');
			redirect('acl');
		}
		
		$data['input'] 				= $role;
		$data['page_title'] 		= 'Edit Role Permission';
		$data['form_action'] 		= base_url('acl/save_acl');
		$data['sel_acl_role_list'] 	= $this->acl_model->get_acl_role_list();	
		$data['msg'] 				= $this->msg;
		
		$this->vars['cssfiles'][] = 'css/theme/bootstrap-grid.css';
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('acl/acl_detail',$data);
		$this->load->view('templates/footer');
	}
	
	
	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'role_name', 'label' => 'Role Name', 'rules' => 'trim|required'),
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}
	
	function save_acl()
	{
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'acl', 'error_keys' => array());
		if(!$_POST) {
			$ajax_return['status'] = 'SUCC';
			echo json_encode($ajax_return);
			return false;
		}
		
		if ($this->input->post('btDelete') != '' && $this->input->post('role_no') != '') 
		{			
			$this->acl_model->acl_delete($this->input->post('role_no'));	
				
			//~ $query_str = "DELETE FROM acl_role WHERE role_no=".$this->input->post('role_no');
			
			//~ $model			= 'action_log_model';
			//~ $this->load->model($model);			
			//~ $ctrl			= $this->router->fetch_class();
			//~ $esc_query_str	= $this->db->escape_str($query_str);	
			//~ $method 		= $this->router->method; 				
			//~ $action_desc	= ' ACL Role # :'.$this->input->post('role_no') .' has been deleted.';
			//~ $action_category = 'delete';
			//~ $action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
			//~ 
			//~ $this->db->query($query_str);
			
			$this->session->set_flashdata("msg", 'Role deleted!');
			// redirect('acl');
			$ajax_return['status'] = 'SUCC';
			echo json_encode($ajax_return);
			return false;
		}
		else {
			$this->set_form_validation('save');
			if($this->form_validation->run() == false) {
				// $this->msg['error_msg'] = validation_errors();
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				// if ($this->input->post('role_no')=='') {
				// 	$this->add_acl();
				// }
				// else {
				// 	$this->edit_acl($this->input->post('role_no'));
				// }
				echo json_encode($ajax_return);
				return false;
			}
			else {
				$acl_str = "";
				$inputs = $this->input->post();
				foreach ($inputs as $key => $val)
				{
					if (strpos($key, 'chk_')===0)
					{
						$arr = explode("_", $key);

						if (isset($acl[$arr[1]]))
							$acl[$arr[1]] .= ":".$arr[2];
						else
							$acl[$arr[1]] = ":".$arr[2];
					}
				}
				foreach($acl as $key => $val)
				{
					$acl_str .= ",".$key.$val;
				}
				
				if ( $this->input->post('role_no') == '' ) {
					
					$this->acl_model->acl_insert(
											$this->db->escape_str($this->input->post('role_name')),
											$acl_str,
											$this->user['username']
											);	
					
					
					//~ $query_str = "INSERT INTO acl_role (name, acl_list, 
								//~ created_by, created_date) VALUES (".
								//~ "'" . $this->db->escape_str($this->input->post('role_name')) . "', ".
								//~ "'" . $acl_str . "', ".
								//~ "'" . $this->user['username'] . "', now() )";
					//~ 
					//~ $model			= 'action_log_model';
					//~ $this->load->model($model);			
					//~ $ctrl			= $this->router->fetch_class();
					//~ $esc_query_str	= $this->db->escape_str($query_str);	
					//~ $method 		= $this->router->method; 				
					//~ $action_desc	= 'New ACL Role Named "'.$this->db->escape_str($this->input->post('role_name')) .'" has been added.';
					//~ $action_category = 'insert';
					//~ $action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
								//~ 
					//~ $this->db->query($query_str);
					
					$role_no = $this->db->insert_id();
				}
				else {
					
					$this->acl_model->acl_update(
											$this->db->escape_str($this->input->post('role_name')),
											$acl_str,
											$this->user['username'],
											$this->db->escape_str($this->input->post('role_no'))
											);
											
					//~ $query_str = "UPDATE acl_role SET " .
								//~ "name = '" . $this->db->escape_str($this->input->post('role_name')) . "', " .
								//~ "acl_list = '" . $acl_str . "', " .
								//~ "modified_by = '" . $this->user['username'] . "', " .
								//~ "modified_date = now() " .
								//~ "WHERE role_no = ' " . $this->db->escape_str($this->input->post('role_no')) . "' ";
								//~ 
					//~ $model			= 'action_log_model';
					//~ $this->load->model($model);			
					//~ $ctrl			= $this->router->fetch_class();
					//~ $esc_query_str	= $this->db->escape_str($query_str);	
					//~ $method 		= $this->router->method; 				
					//~ $action_desc	= 'ACL of '.$this->db->escape_str($this->input->post('role_name')) .' role has been updated.';
					//~ $action_category = 'update';
					//~ $action_log =  $this->$model->save_action($ctrl,$method,$esc_query_str,$action_desc,$action_category);	
					//~ 
					//~ $this->db->query($query_str);
					
					$role_no = $this->db->escape_str($this->input->post('role_no'));
				}
				
				$this->msg['msg'] = "ACL Role Saved!";
				// redirect('acl');
				$ajax_return['status'] = 'SUCC';
				echo json_encode($ajax_return);
				return false;
			}
		}
	}
	
	function role_select()
	{
		$role_no = $this->input->post('role_no');
		$role = $this->acl_model->get_acl($role_no);

		echo json_encode($role);
	}

	/**
	 * This function have been copy to auth.
	 */
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
					//~ 'actions'=>$acl_arr,
					//~ );
		//~ }
//~ 
		//~ return $acl_list;
	//~ }
	
	
	/**
	 *  has be moved to acl_model
	 */

	//~ function get_acl_entry()
	//~ {
		//~ $acl_list = array();
		//~ $sql = "SELECT * FROM acl_entry ORDER BY idx";
		//~ $query = $this->db->query($sql);
		//~ foreach ($query->result_array() as $row)
		//~ {
			//~ $acl_list[$row['idx']] = array(
										//~ 'name'=>$row['name'],
										//~ 'display_name'=>$row['display_name'],
										//~ 'V'=>0,
										//~ 'M'=>0,
										//~ 'D'=>0,
										//~ 'O'=>0,
										//~ );
		//~ }
		//~ return $acl_list;
	//~ }
	//~ 
	
	
	/**
	 *  has be moved to acl_model
	 */
	 
	//~ function get_acl($role_no='')
	//~ {
		//~ $acl_list = $this->get_acl_entry();
//~ 
		//~ $query = $this->db->query("SELECT ar.role_no, ar.name as role_name, ar.acl_list 
							//~ FROM acl_role ar  
							//~ WHERE ar.role_no='".$this->db->escape_str($role_no)."'
							//~ LIMIT 1");
		//~ if ($query->num_rows() > 0)	{
			//~ $return_val = $query->row_array();
			//~ 
			//~ $role_acl_list = explode(',', $return_val['acl_list']);
			//~ $role_acl_list = array_slice($role_acl_list, 1);
//~ 
			//~ foreach ($role_acl_list as $row) {
				//~ $val = explode(':', $row);
				//~ for ($i=1;$i<count($val);$i++)
				//~ {
					//~ $acl_list[$val[0]][$val[$i]] = 1;
				//~ }
			//~ }
//~ 
			//~ $return_val['btn_delete']='enabled';
		//~ }
		//~ else {
			//~ $return_val['role_no'] = '';
			//~ $return_val['role_name'] = '';
			//~ 
			//~ $return_val['btn_delete']='disabled';
		//~ }
		//~ $return_val['acl_list'] = $acl_list;
		//~ return $return_val;
	//~ }

}

