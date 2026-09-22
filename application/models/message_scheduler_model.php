<?php

class Message_scheduler_model extends MY_Model {

    function __construct()
    {
    	parent::__construct();
    }

    public function insert_new_scheduled_message($data, $log_data)
    {
        if (!$this->isWhatsappEnabled() && $data['msg_type'] === 'whatsapp') {
            return false;
        }

        $this->db->trans_begin();

        $username = $this->user['username'] ?? 'System';

        $data['created_by'] = $username;
        $data['modified_by'] = $username;

        $this->db->insert('message_scheduler', $data);
        $scheduler_id = $this->db->insert_id();

        $outgoing_data = [
            'scheduler_id'               => $scheduler_id,
            'msg_type'                   => $data['msg_type'],
            'msg_to'                     => $data['msg_to'],
            'remark'                     => json_encode($log_data)
        ];

        $this->db->insert('message_outgoing', $outgoing_data);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();

        return $scheduler_id;
    }

    private function isWhatsappEnabled()
    {
        $config = $this->db
                    ->select('val')
                    ->where('key', 'whatsapp_notification')
                    ->limit(1)
                    ->get('sys_config')
                    ->row_array();

        return ($config['val'] ?? 1) == 1;
    }

    public function get_pending_outgoing_msg($msg_type = 'whatsapp', $limit = 10) {
        $return_val = [];

        $sql = "SELECT mo.*, ms.* FROM message_outgoing mo LEFT JOIN message_scheduler ms ON mo.scheduler_id = ms.scheduler_id WHERE UNIX_TIMESTAMP(ms.msg_schedule_on) <= UNIX_TIMESTAMP(NOW()) AND mo.msg_sent_on IS NULL AND mo.msg_status != 'S' AND mo.msg_attempt <= 2 AND ms.msg_type = ? ORDER BY mo.msg_attempt ASC, mo.outgoing_id ASC, ms.msg_schedule_on ASC LIMIT ?";

        $query = $this->db->query($sql, [$msg_type, $limit]);
        if($query->num_rows() > 0) {
            $return_val = $query->result_array();
        }

        return $return_val;
    }

    public function get_message_list($txt_search = '', $date_from = '', $date_to = '', $status, $page_item_no = 0, $row_per_page = '') {

        $txt_search = $this->db->escape_str($txt_search);
        $return_val['total_row'] = 0;
		$return_val['row'] = [];

        $qwhere = "";
        if($status != 'all')
            $qwhere .= " AND mo.msg_status = '$status' ";

		if($date_from != '')
			$qwhere .= " AND ms.msg_schedule_on >= '".date('Y-m-d 00:00:00' , strtotime( $date_from))."' ";
		
		if($date_to != '')
			$qwhere .= " AND ms.msg_schedule_on <= '".date('Y-m-d 23:59:59' , strtotime( $date_to))."' ";

        if($page_item_no == '')
            $page_item_no = 0;
		
		if($row_per_page == '') 
			$row_per_page = $_SESSION['config']['max_page_item'];

        $count_sql = "SELECT count(*) AS total_row FROM message_scheduler ms LEFT JOIN message_outgoing mo ON ms.scheduler_id = mo.scheduler_id WHERE ms.message LIKE '%$txt_search%' $qwhere ORDER BY ms.msg_schedule_on DESC, mo.scheduler_id DESC";
        $query = $this->db->query($count_sql);
        $return_val['total_row'] = $query->row(0)->total_row;

        $sql = "SELECT mo.*, ms.* FROM message_scheduler ms LEFT JOIN message_outgoing mo ON ms.scheduler_id = mo.scheduler_id WHERE ms.message LIKE '%$txt_search%' $qwhere ORDER BY ms.msg_schedule_on DESC, mo.scheduler_id DESC LIMIT $page_item_no, $row_per_page";

        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $return_val['row'] = $query->result_array();
        }

        return $return_val;
    }

    public function get_message_details($scheduler_id) {
        $return_val = [];

        if(!empty($scheduler_id)) {
            $sql = "SELECT mo.*, ms.* FROM message_outgoing mo LEFT JOIN message_scheduler ms ON mo.scheduler_id = ms.scheduler_id WHERE ms.scheduler_id = ?";

            $query = $this->db->query($sql, [$scheduler_id]);
            if($query->num_rows() > 0) {
                $return_val = $query->row_array();
            } else {
                
            }

            return $return_val;
        }
    }

    public function update_scheduled_message($data)
    {
        $scheduler_data = [
            'msg_schedule_on' => $data['msg_schedule_on'],
            'message' => $data['full_message'],
            'modified_by' => $this->user['username']
        ];
        $this->db->where('scheduler_id', $data['scheduler_id']);
        $this->db->update('message_scheduler', $scheduler_data);

        $outgoing_data = [
            'remark' => $data['remark']
        ];
        $this->db->where('outgoing_id', $data['outgoing_id']);
        $this->db->update('message_outgoing', $outgoing_data);
    }

    public function delete_scheduled_message($scheduler_id)
    {
        $this->db->where('scheduler_id', $scheduler_id);
        $this->db->delete('message_scheduler');

        $this->db->where('scheduler_id', $scheduler_id);
        $this->db->delete('message_outgoing');

        return $this->db->affected_rows();
    }
}
