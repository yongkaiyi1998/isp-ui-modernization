<?php
class Sms_outgoing_model extends MY_Model{

	protected $_table		= 'sms_outgoing';
	protected $_primary_key	= 'sms_id';

	public function __construct()
	{
		parent::__construct();
	}	
	
	function insert_sms_to_outgoing($data,$scheduler_id = '')
	{	
		$return_val = '';
		if(!empty($scheduler_id))
		{
			$is_delete 			= '0';
			$is_record_empty 	= $this->get_outgoing_by(array('scheduler_id',$scheduler_id));		
			if(!empty($is_record_empty)) $is_delete = $this->delete_outgoing_by(array('scheduler_id',$scheduler_id));			
			
			if(($is_delete == '1') || (empty($is_record_empty)))
			{
				
				$scheduled_time = $data['sms_schedule_on'];
				$sms_msg 		= $data['sms_msg'];
				
				$qry_str = "INSERT INTO `sms_outgoing`(`sms_phone`,`sms_msg`,`scheduler_id`,`scheduled_time`) VALUES ";
				
				if( $data['is_building'] === 1 ){
					
					$phone_num_arr 	= $this->get_phone_number($data,$scheduler_id);
					if(!empty($phone_num_arr))
					{
						foreach ($phone_num_arr as $pkey => $sms_phone)
						{
							$qry_str .="('".$sms_phone."','".$this->db->escape_str($sms_msg)."','".$scheduler_id."','".$scheduled_time."'),";
						}
					}
					
				}else{//by individual
					$phone_num_arr = explode( ";" , $data['sms_to'] );
					foreach( $phone_num_arr AS $sms_phone ){
						if( $sms_phone != "" ){
							$qry_str .="('".trim($sms_phone)."','".$this->db->escape_str($sms_msg)."','".$scheduler_id."','".$scheduled_time."'),";
						}
					}
				}
				
				$qry_str	= rtrim($qry_str,',');
				
				$return_val	= $this->db->query($qry_str);
				
			}		
		}
		return $return_val;		
	}
	
		
	function delete_outgoing_by($where = array())
	{
		$return_val = '';
		if(!empty($where))
		{
			$query_str	= "DELETE FROM ".$this->_table." WHERE `".$where[0]."` ='".$where[1]."'";
			$return_val = $this->db->query($query_str);
		}
		return $return_val;	
	}
	
	function get_outgoing_by($where = array())
	{	
		$return_val = '';
		if(!empty($where)){
			$query_str = "SELECT * FROM ".$this->_table." WHERE `".$where[0]."` =".$where[1];
			$query 		= $this->db->query($query_str);
			$return_val = $query->result_array();
		}
		return $return_val;		
	}
	
	function get_phone_number($data,$scheduler_id)
	{
		$building_no 	= (empty($data['sms_building']))?'':explode(",",rtrim($data['sms_building'],',')); 
		$category 		= (empty($data['sms_cust_cat']))?'':explode(",",rtrim($data['sms_cust_cat'],',')); 
		$status 		= (empty($data['sms_cust_status']))?'':explode(",",rtrim($data['sms_cust_status'],',')); 
		
		$where 		= '';		
		$qry_str 	=  " SELECT c.mobile_num FROM customer as c ";
		
		if( !empty($building_no) ){
			$qry_str 	.= " INNER JOIN building as b ON c.building= b.building_no ";
		}
		
		if(!empty($category))
		{
			$where_str	= ($where == '')?' WHERE ':' AND ';
			
			$cat_val	= '';
			foreach ($category as $cat)$cat_val .= "'".$cat."',";
			$cat_val 	= rtrim($cat_val,',');
						
			$where .= $where_str."  c.category IN(".$cat_val.")";
		}
		
		if(!empty($status))
		{
			$where_str = ($where == '')?' WHERE ':' AND ';
			
			$state_val	= '';
			foreach ($status as $state) $state_val .= "'".$state."',";
			$state_val 	= rtrim($state_val,',');
			
			$where .= $where_str." c.status IN(".$state_val.") ";
		}
		
		if(!empty($building_no))
		{
			$where_str = ($where == '')?' WHERE ':' AND ';
			
			$building_val	= '';
			foreach ($building_no as $building) $building_val .= "'".$building."',";			
			$building_val 	= rtrim($building_val,',');
			
			$where .= $where_str." b.building_no IN(".$building_val.")";
		}
		$qry_str .= $where;
		
		$query 			= $this->db->query($qry_str);
		$phone_num_arr  = $query->result_array();
		
		$final_phone_num = array();
		if(!empty($phone_num_arr))
		{
			foreach ($phone_num_arr as $pn_key => $pn_val){
				if(!empty($phone_num_arr[$pn_key]['mobile_num']))
				{					
					$final_phone_num[] = $phone_num_arr[$pn_key]['mobile_num'];				
				}
			}
		}
		
		return $final_phone_num;
	}
	
	function update_sms_outgoing_attempt( $sms_id ){
		
		$return_val = 0;
		$sql = " UPDATE sms_outgoing SET sms_attempt = sms_attempt + 1 WHERE sms_id = '".$sms_id."' ; ";
		
		$query = $this->db->query($query_str);
		
		if( $this->db->affected_rows() ){
			$return_val = 1; 
		}
		
		return $return_val; 
		
	}
	
	function get_pending_outgoing_sms(){
	
		$return_val = array();
	
		$sql = " SELECT o.*, s.sms_title
				 FROM sms_outgoing o
				 LEFT JOIN sms_scheduler s ON o.scheduler_id = s.scheduler_id
				 WHERE UNIX_TIMESTAMP( o.scheduled_time ) <= UNIX_TIMESTAMP('".date("Y-m-d H:i:s")."')
				 AND ( o.sms_status = 'P' OR o.sms_status = 'N' ) 
				 AND o.sms_attempt < 4 
				 LIMIT 1000 ; ";
						
		$query = $this->db->query($sql);
		if( $query->num_rows() > 0 ) 
			$return_val = $query->result_array();
			
		return $return_val;	
		
	}
	
	function update_sms_outgoing( $outgoing_id, $attempt, $status ,$remark = '' ){
		
		if( $status == 1 ){
			/*		
			$sql = " UPDATE sms_outgoing 
						SET sms_status = 'S' ,
							sms_timestamp = NOW() ,
							remark = ''
						WHERE sms_id = '".$outgoing_id."' ";
			*/
			
			$sql = " INSERT INTO sms_outarchive ( acc_id, scheduler_id, scheduled_time, sms_phone, sms_msg, 
						sms_timestamp, sms_processed, sms_status, sms_total, sms_transport, sms_attempt )
						SELECT 	acc_id, scheduler_id, scheduled_time, sms_phone, sms_msg, sms_timestamp, NOW(), 'S' ,
								sms_total, sms_transport, sms_attempt
						FROM sms_outgoing 
						WHERE sms_id = '".$outgoing_id."' ; ";
			
			$query = $this->db->query($sql);

			$sql = " DELETE FROM sms_outgoing WHERE sms_id = '".$outgoing_id."' ; ";
			
		}else{
			
			if( $attempt <= 3 ){

			$sql = " UPDATE sms_outgoing SET 	sms_status = 'P', 
												sms_attempt = sms_attempt + 1 , 
												remark = '".$this->db->escape_str( $remark )."'
					 WHERE sms_id = '".$outgoing_id."' ";
				
			}elseif( $attemp > 3 ){
				
			$sql = " UPDATE sms_outgoing SET 	sms_status = 'F', 
												sms_attempt = 4 ,
												remark = '".$this->db->escape_str( $remark )."'
					 WHERE sms_id = '".$outgoing_id."' ";
				
			}
			
		}
		
		$query = $this->db->query($sql);
		
	}
	
	function sms_send( $phone = '' , $msg = '' , $maxis_id = '', $maxis_pwd = ''){
		
		$result = array( "success" => 0 , "remark" => "" ) ;
		
		if( trim($phone) != "" && trim($msg) != "" ){
			
			$ch = curl_init();
			
			//~ $maxis_id 	= 'pgfon_sms_service' ;
			//~ $maxis_pwd 	= 'I1ovemy!sp' ;
			$phone		= str_replace( "-" , "" , $phone ) ;
			$phone 		= trim( $phone ) ; //in 60xxxx format
			if( substr($phone,0,1) == 0 ){
				$phone = '6' . $phone ;
			}
			
			$msg		= trim( $msg ) ;
			
			$param  = 'ID=' . urlencode( $maxis_id ) ;
			$param .= '&Password=' . urlencode( $maxis_pwd ) ;
			$param .= '&Mobile=' . ( $phone ) ;
			$param .= '&Type=LA&Message=' . urlencode( $msg ) ;
			
			$options = array(
				CURLOPT_URL				=> "https://s-esms.maxis.net.my:8443/servlet/smsdirect.jsp?" . $param ,
				CURLOPT_SSL_VERIFYPEER  => true,
				CURLOPT_RETURNTRANSFER 	=> true,     // return web page
			);
			
			curl_setopt_array( $ch, $options );
			$return = curl_exec( $ch );
			$err    = curl_errno( $ch );
			$errmsg = curl_error( $ch );

			curl_close( $ch );
			
			// Further processing ...
			if ( trim($return) == "OK" || trim($return) == '01010'){
				$result['success'] = 1 ;
			}else{
				$result['success'] = 0 ;
				$result['remark'] = "Return msg => " . trim($return) . " error # => " . $err . " error => " . $errmsg ;
			}
			
		}
		
		return $result ;
		
	}
	
	
}

