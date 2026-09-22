<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/DataPage_Controller.php' );
class Advertisement extends DataPage_Controller {

	function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
		$this->load->library(array('form_validation','session','upload'));
		
		check_acl('config');
		$this->load->model('advertisement_model');
		$this->load->model('common_model');	

		$this->load->helper('image_helper');
    }

	function set_form_validation($mode = 'search')
	{
		$config = array(
					'search' => array (
						array('field' => 'txt_search', 'label' => 'Search', 'rules' => 'trim'),
					),
					'save' => array (
						array('field' => 'id', 'label' => 'ID', 'rules' => 'trim'),
						array('field' => 'lineno', 'label' => 'Line No.', 'rules' => 'trim'),
						array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|required'),
						array('field' => 'start_date', 'label' => 'Start Date', 'rules' => 'trim|required'),
						array('field' => 'end_date', 'label' => 'End Date', 'rules' => 'trim|required'),
						array('field' => 'file_path', 'label' => 'File Path', 'rules' => 'trim|required'),
						array('field' => 'link', 'label' => 'Link', 'rules' => 'trim'),
						array('field' => 'is_active', 'label' => 'Is Active', 'rules' => 'trim|numeric'),
					),
				);
		
		$this->form_validation->set_rules($config[$mode]);
	}

    public function index()
    {
		$row_html = $this->advertisement_rows(1);
		$post_data = array();

		if ($this->input->post()) {
			$post_data = $this->input->post();
			$data['page_item_no'] = $post_data['page_item_no'];
			$data['txt_search'] = $post_data['txt_search'];
			$data['txt_date_start'] = $post_data['txt_date_start'];
			$data['txt_date_end'] = $post_data['txt_date_end'];
		} else {
			$advertisement_filter           = get_session_filter('advertisement_filter');
			$data['page_item_no'] = $advertisement_filter['page_item_no'] ?? 0;
			$data['txt_search'] = $advertisement_filter['txt_search'] ?? '';
			$data['txt_date_start'] = $advertisement_filter['txt_date_start'] ?? date('Y-m-01');
			$data['txt_date_end'] = $advertisement_filter['txt_date_end'] ?? date('Y-m-t');
		}

		$data['page_title'] 	= 'Advertisement';
		$data['form_action'] 	= base_url('advertisement');
		$data['msg']			= $this->msg;
		$data['row_html']		= $row_html;

		$data['this_month_start'] = date('Y-m-01');
		$data['this_month_end'] = date('Y-m-t');

		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('advertisement/index',$data);
		$this->load->view('templates/footer');
	}

	public function advertisement_rows($returnOnly = 0)
	{
		$post_data = array();
		if ($this->input->post()) {
			$post_data = $this->input->post();
		} else {
			$advertisement_filter           = get_session_filter('advertisement_filter');
			$post_data['page_item_no'] = $advertisement_filter['page_item_no'] ?? 0;
			$post_data['txt_search'] = $advertisement_filter['txt_search'] ?? '';
			$post_data['txt_date_start'] = $advertisement_filter['txt_date_start'] ?? date('Y-m-01');
			$post_data['txt_date_end'] = $advertisement_filter['txt_date_end'] ?? date('Y-m-t');
		}

		if (!isset($post_data['page_item_no'])) $post_data['page_item_no'] = 0;

		if (!isset($post_data['txt_search'])) $post_data['txt_search'] = '';

		if (!isset($post_data['txt_date_start'])) $post_data['txt_date_start'] = date('Y-m-01');

		if (!isset($post_data['txt_date_end'])) $post_data['txt_date_end'] = date('Y-m-t');

		$total_row 		= 0;
		$page_item_no  = ($post_data['page_item_no'] == '')? 0 : $post_data['page_item_no'];
		$txt_search    = $post_data['txt_search'];
		$txt_date_start  = $post_data['txt_date_start'];
		$txt_date_end    = $post_data['txt_date_end'];

		$session_array = array(
			'page_item_no' => $page_item_no,
			'txt_search' => $txt_search,
			'txt_date_start' => $txt_date_start,
			'txt_date_end' => $txt_date_end
		);

		set_session_filter('advertisement_filter', $session_array);

		$query_where = "";
		
		$result = $this->advertisement_model->get_advertisement_listing($txt_search,$txt_date_start,$txt_date_end,$page_item_no,$_SESSION['config']['max_page_item'],$query_where);
		$total_row = $result['total_row'];
		$row = $result['row'];

		$data['pagination'] = paginationSettingsAjax('', $total_row, $page_item_no, $_SESSION['config']['max_page_item']);

		foreach ($row as $rkey => $rvalue) {
			if ($rvalue['is_active'] == '1') {
				$row[$rkey]['is_active_html'] = '<i class="fa fa-check green fa-1g" ></i>';
			} else {
				$row[$rkey]['is_active_html'] = '<i class="fa fa-ban orange fa-1g" ></i>';
			}

			$image_html = '';

			$local_path = stripslashes($rvalue['file_path']);
			$local_path = str_replace("%", "%25", $local_path);

			$file_info = pathinfo($local_path);
			$extension = strtolower($file_info['extension']);

			$thumbnail_filename = 'thumbnail_' . $file_info['filename'] . '.jpeg';
			$thumbnail_path = dirname($local_path) . '/' . $thumbnail_filename;
			
			$thumbnail_src = (file_exists($this->config->item('upload_path').$thumbnail_path)) ? $this->config->item('upload_url').$thumbnail_path : base_url("images/image.png");

			$image_html = "<img src='".$thumbnail_src."' style='width:200px; height:200px;'>";

			$row[$rkey]['image_html'] = $image_html;
		}

		$data['row_data']	= $row;
		
		$html = $this->parser->parse('advertisement/advertisement_rows',$data, true);

		if (!empty($returnOnly)) return $html;
		echo $html;
	}

	function add_advertisement()
	{
		$data['input'] 				= $this->advertisement_model->get_advertisement();
		$data['page_title'] 		= 'Add Advertisement';
		$data['form_action'] 		= base_url('advertisement/save_advertisement');		

		$data['msg'] 				= $this->msg;
		
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('advertisement/advertisement_detail',$data);
		$this->load->view('templates/footer');
	}  

	function edit_advertisement($id='')
	{
		$advertisement = $this->advertisement_model->get_advertisement($id);
		
		if ($advertisement['id']=='') {
			$this->session->set_flashdata("warning_msg", 'Advertisement not found!');
			redirect('advertisement');
		}
		
		$data['input'] 				= $advertisement;
		$data['page_title'] 		= 'Edit Advertisement';
		$data['form_action'] 		= base_url('advertisement/save_advertisement');

		$data['msg'] 				= $this->msg;
			
		$this->load->view('templates/header', $this->vars);
		$this->load->view('templates/menu',$this->menu);
		$this->parser->parse('advertisement/advertisement_detail',$data);
		$this->load->view('templates/footer');
	}	

	function save_advertisement()
	{
		//_debug_array($_POST); _debug_array($_FILES); exit;
		$ajax_return = array('status' => 'ER', 'err_msg' => '', 'url' => 'package', 'error_keys' => array());

		if ($this->input->post('btDelete') != '' && $this->input->post('id') != '') {
			$this->advertisement_model->advertisement_delete($this->input->post('id'));			
			
			$this->session->set_flashdata("msg", 'Advertisement deleted!');

			$ajax_return['status'] = 'SUCC';
			$ajax_return['url'] = 'advertisement';
			echo json_encode($ajax_return);
			return false;
		}
		else 
		{
			$this->set_form_validation('save');
			if($this->form_validation->run() == false) 
			{
				$ajax_return['err_msg'] = validation_errors();
				$ajax_return['error_keys'] = validation_error_array();
				echo json_encode($ajax_return);
				return false;
			}
			else 
			{
				if ( $this->input->post('id') == '0' ) 
				{
					$result = $this->advertisement_model->advertisement_insert($this->input->post(),$this->user['username']);	
					$id = $result['id'];
				}
				else 
				{
					$result = $this->advertisement_model->advertisement_update($this->input->post(),$this->user['username'],$this->input->post('id'));	
					$id = $this->input->post('id');
				}

				//handling attachments	
				if (isset($_FILES['file_path'])) {
					// log_message('error',print_r($_FILES['file_path'],true));

				  	//Get the temp file path
				  	$tmp_path = $_FILES['file_path']['tmp_name'];

				  	//Make sure we have a file path
				  	if ($tmp_path != "") {
						$prefix 		= date('YmdHis');
						$file_name 		= $_FILES['file_path']['name'];
						$prefix_n_file_name = $prefix . "_" . $file_name;
						$file_path 		= $this->config->item('upload_path')."/upload/" . $prefix_n_file_name;
						$local_path 	= "/upload/" . $prefix_n_file_name;

						if (move_uploaded_file($tmp_path, $file_path)) {
							$file_info = pathinfo($local_path);
							$extension = strtolower($file_info['extension']);
							
							list($width, $height) = getimagesize($this->config->item('upload_path').$local_path);
							resize_image($local_path, $width, $height, $file_info['filename'], FALSE, $extension, $this->config->item('upload_path'));

							//update file_path into record
							$this->advertisement_model->update_file_path($id, $local_path);
						}
			    	}

				}

				$this->session->set_flashdata("msg", 'Advertisement Saved!');

				$ajax_return['status'] = 'SUCC';
				$ajax_return['url'] = 'advertisement';
				echo json_encode($ajax_return);
				return false;
			}
		}

	}

}