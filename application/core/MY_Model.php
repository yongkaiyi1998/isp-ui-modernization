<?php
//application/models/

//bread Lock ead
class MY_Model extends CI_Model
{
	protected $_table = null;
	protected $_primary_key = null;
	protected $user;

	//------------------------------------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();
        $this->user = $this->session->userdata("user");
	}

	//------------------------------------------------------------------------------------------------

	public function max($field = null)
	{		
		if($field  != null ){			
			$this->db->select_max($field);
		}		
		else
		{
			die('2 Parameter needed for edit()');
		}
		
		$q = $this->db->get($this->_table);
		return $q->result_array();
	}


	/**
	* //browse + read = get
	* @usage
	* Single : $this->news_model->get(2);
	* All : $this->news_model->get();
	* Custom: $this->news_model->get(['any' => 'param']);
	*/
	public function get($id = null,$limit=null, $order_by = null)
	{
		if (is_numeric($id))
		{
			$this->db->where($this->_primary_key,$id);
		}

		if (is_array($id))
		{
			foreach ($id as $_key => $_value)
			{
				$this->db->where($_key,$_value);
			}
		}
		if($limit != null)
		{
			//http://www.codeigniter.com/userguide3/database/query_builder.html#limiting-or-counting-results
			$this->db->limit($limit[0],$limit[1]);
		}
		if($order_by != null)
		{
			//http://www.codeigniter.com/userguide3/database/query_builder.html#ordering-results
			$this->db->order_by($order_by[0],$order_by[1]);
		}

		$q = $this->db->get($this->_table);
		return $q->result_array();
	}

	//------------------------------------------------------------------------------------------------

	/**
	* @usage $result = $this->user_model->edit(['username'=>'Markus'], 3);
	*					$this->user_model->edit(['username'=>'Markus'], ['date_created'=>'0']);
	*
	*/
	public function edit($new_data, $where)
	{
		if (is_numeric($where))
		{
			$this->db->where($this->_primary_key,$where);
		}
		elseif (is_array($where))
		{
			foreach ($where as $_key => $_value)
			{
				$this->db->where($_key,$_value);
			}
		}
		else
		{
			die('2 Parameter needed for edit()');
		}

		$this->db->update($this->_table, $new_data);
		return $this->db->affected_rows();
	}

	//------------------------------------------------------------------------------------------------

	/**
	* @param array $data
	*
	* @usage $result = $this->user_model->add(['xx'=>'data']);
	*
	*/
	public function add($data){
		$this->db->insert($this->_table,$data);
		return $this->db->insert_id();
	}

	//------------------------------------------------------------------------------------------------

	/*
	* @usage $this->user_model->delete(2);
	* @usage $this->user_model->delete(['username' => 'Markus']);
	*/
	public function delete($id)
	{
		if (is_numeric($id))
		{
			$this->db->where($this->_primary_key,$id);
		}
		else if(is_array($id))
		{
			foreach ($id as $_key => $_value)
			{
				$this->db->where($_key,$_value);
			}
		}
		else
		{
			die('Parameter needed for DELETE()');
		}
		$this->db->delete($this->_table);
		return $this->db->affected_rows();
	}

	//------------------------------------------------------------------------------------------------

	/**
	* @usage $result = $this->user_model->insertUpdate(['username'=>'Markus'], 3);
	*					$this->user_model->insertUpdate(['username'=>'Markus'], ['date_created'=>'0']);
	*
	*/
	public function insertUpdate($data, $id = false)
	{

		if(!$id){
			die('Parameter needed for insertUpdate()');
		}
		$this->db->select($this->_primary_key);
		$this->db->where($this->_primary_key,$id);
		$q = $this->db->get($this->_table);
		$result = $q->num_rows();

		if($result == 0){
			//add
			return $this->add($data);
		}
		//update
		return $this->edit($data, $id);
	}
	
	public function get_listing($limit='', $order_by = '', $arr = '')
	{
		$query_str 		= "SELECT SQL_CALC_FOUND_ROWS * ";
		$query_str 		.= " FROM (`".$this->_table."`) ";

		$where 			= '';
		$order_query 	= '';
		$limit_query 	= '';

		if((!empty($arr)))
		{			
			if(is_array($arr))
			{				
				//~ _debug_array($arr); exit;
				
				foreach ($arr as $arr_key => $arr_val)
				{				
					if(!empty($arr[$arr_key])){
						$where_str = ($where == '')?' WHERE ':' AND ';
						$where .= " ".$where_str." (".$arr[$arr_key].") ";
					}
				}
			}
		}		
		if($order_by != '' && is_array($order_by))
		{
			$order_key = (empty($order_by[0]))?$this->_primary_key:$order_by[0];
			$order_val = (empty($order_by[1]))?'ASC':$order_by[1];
			$order_query .= " ORDER BY `".$order_key."` ".$order_val." ";
		}
		if($limit != '' && is_array($limit))
		{
			$limit_val = (empty($limit[0]))?' LIMIT 0 ':" LIMIT ".$limit[0]." ";
			$offset_val = (empty($limit[1]))?' OFFSET 0 ':" OFFSET ".$limit[1]." ";
			$limit_query .= $limit_val.$offset_val;
		}

		$q = $query_str.$where.$order_query.$limit_query;
		$query = $this->db->query($q);
		return $query->result_array();
	}
	
	//------------------------------------------------------------------------------------------------

	/**
	* //count tables row from specific table
	* 
	* @usage
	* Single : $this->news_model->count();
	*/
	public function count()
	{
		return $this->db->get($this->_table)->num_rows();
	}
	
	//------------------------------------------------------------------------------------------------
}
