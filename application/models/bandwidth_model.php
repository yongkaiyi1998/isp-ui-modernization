<?php

class Bandwidth_model extends MY_Model {

    protected $_table = 'bandwidth_stats';
	protected $_primary_key = 'id';

    public function insert_batch_data($data) {
        if (empty($data)) return;

        $table = $this->_table;
        $fields = array_keys($data[0]);

        $columns = '`' . implode('`, `', $fields) . '`';

        $values = [];
        $updates = [];

        foreach ($data as $row) {
            $escaped = array_map([$this->db, 'escape'], $row);
            $values[] = '(' . implode(', ', $escaped) . ')';

            $rowUpdates = [];
            foreach ($fields as $field) {
                if (!in_array($field, ['customer_no', 'timestamp'])) {
                    $rowUpdates[] = "`$field` = VALUES(`$field`)";
                }
            }
            $updates = $rowUpdates;
        }

        $updateClause = implode(', ', $updates);

        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES " . implode(', ', $values) .
            " ON DUPLICATE KEY UPDATE {$updateClause}";

        $this->db->query($sql);
    }
}
