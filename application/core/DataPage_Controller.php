<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once( APPPATH . 'core/MY_Controller.php' );
class DataPage_Controller extends MY_Controller {
	function __construct() {
        parent::__construct();
        $this->db = $this->load->database('default',true,false);
        $this->load->library('parser');
        $this->load->helper('form');
        $this->load->helper('url');
        
		if((!$this->session->userdata('language'))){
			$this->session->set_userdata('language','english');
		}
		
    }

    function inserttable($table,$viewname='',$params=array(),$database='default',$tabledef=null)
    {
        $this->session->unset_userdata($table);
        if ($viewname!='')
        {
            if (is_null($tabledef))
            {
                include(APPPATH.'config/database.php');
                if ($database=='default')
                    $database = $active_group;
                $tabledef = $db[$database]['tabledefs'][$table];
            }
            $data = array();
            foreach ($tabledef as $field=>$def)
            {
                switch($def['type'])
                {
                    case 'int':
                    case 'float':
                        $data[$field] = '0';
                        break;
                    case 'datetime':
                    case 'boolean':
                    case 'string':
                    default:
                        $data[$field] = '';
                }
            }
            $data = array_merge($data,$params);
            $this->parser->parse($viewname,$data);
        }

    }

    // *************************************************
    // $key = string or array(keyfield1=>val1,keyfield2=>key2...)
    //
    //
    // *************************************************
    function edittable($table,$key,$viewname='',$params=array(),$database='default',$tabledef=null)
    {
        if (is_null($tabledef))
        {
            include(APPPATH.'config/database.php');
            if ($database=='default')
                $database = $active_group;
            $tabledef = $db[$database]['tabledefs'][$table];
        }

        $keyval='';
        if (is_array($key)) {
            foreach($key as $field=>$val)
                $keyval .= " and $field = ".$this->db->escape($val);
            $keyval=substr($keyval,5); //drop first and
        }
        else {
            foreach ($tabledef as $field=>$def)
                if ($def['iskey']) {
                    $keyval = $field.'='.$this->db->escape($key);
                    break;
                }
        }
        $qry="select * from $table where $keyval";
        $query = $this->db->query($qry);
        $data = $query->row_array();

        if (count($data)==0)
        {
            // record not found, insert new row
            $this->session->unset_userdata($table);
            foreach ($tabledef as $field=>$def)
            {
                switch($def['type'])
                {
                    case 'int':
                    case 'float':
                        $data[$field] = '0';
                        break;
                    case 'datetime':
                    case 'boolean':
                    case 'string':
                    default:
                        $data[$field] = '';
                }
                if (!is_array($key) && $def['iskey'])
                    $data[$field] = $key;
            }
            if (is_array($key))
               $data = array_merge($data,$key);
        }
        else
           $this->session->set_userdata($table, $data);

        if ($viewname!='')
        {
            $viewdata = array();
            foreach ($data as $field=>$val)
                if ($tabledef[$field]['type']=='boolean')
                    $viewdata[$field] = ($val=='1'?'checked':'');
                else
                    $viewdata[$field]=$val;

            $viewdata = array_merge($viewdata,$params);
            $this->parser->parse($viewname,$viewdata);
        }
        return $data;
    }

    // *************************************************
    // $tabledef = array(
    //      '<fieldname>' => array(
    //          'name'=>'<displayed name>',
    //          'type'=>'string/int/float/boolean/datetime',
    //          'iskey'=>true/false,
    //          'null'=>true/false,
    //      ),
    //      '<fieldname>' => array('type'=>'string/int/float/boolean/date/datetime','iskey'=>true/false,'null'=>true/false),
    // );
    // *************************************************
    function savetable($table,$data,$database='default',$tabledef=null)
    {
        $olddata = $this->session->userdata($table);
        $values = '';
        $updates = '';
        $where = '';
        if (is_null($tabledef))
        {
            include(APPPATH.'config/database.php');
            if ($database=='default')
                $database = $active_group;
            $tabledef = $db[$database]['tabledefs'][$table];
        }
        foreach ($tabledef as $fieldname=>$def)
        {
            if ($olddata===false) {
                // inserting
                if ((!$def['null'] || $def['iskey']) && (!isset($data[$fieldname]) || ($data[$fieldname]=='')) )
                {
                    return $def['name'].' cannot be empty';
                }
            }
            else {
                // editing
                if ($def['iskey'])
                    $where.=' and '.$fieldname.'='.$this->db->escape($olddata[$fieldname]);
                // unchecked checkbox is not returned
                if ($def['type']=='boolean' && !isset($data[$fieldname]))
                    $data[$fieldname]='0';
            }
        }

        foreach($data as $fieldname=>$value)
        {
            if ($olddata===false) {
                $fieldnames.=",$fieldname";
                switch ($tabledef[$fieldname]['type']) {
                    case 'boolean':
                        $values.=",'".($value?'1':'0')."'";
                        break;
                    case 'int':
                    case 'float':
                    case 'datetime':
                    default:
                        $values.=','.$this->db->escape($value);
                }
            }
            else {
                switch ($tabledef[$fieldname]['type']) {
                    case 'boolean':
                        $updates.=",$fieldname='".($value?'1':'0')."'";
                        break;
                    case 'int':
                    case 'float':
                    case 'datetime':
                    default:
                        $updates.=",$fieldname=".$this->db->escape($value);
                }
            }
        }

        if ($olddata===false) {
            // insert new record
            $fieldnames=substr($fieldnames, 1); // remove first comma .
            $values=substr($values, 1); // remove first comma .
            $qry="insert into $table ($fieldnames) values ($values)";
        }
        else {
            // update old record
            $updates=substr($updates, 1); // remove first comma .
            $where=substr($where, 5); // remove first comma ' and '
            $qry="update $table set $updates where $where";
        }

        $this->db->query($qry);
        return true;
    }

    public function clear_filter_session($session_filter_name)
    {
        clear_session_filter($session_filter_name);
    }
}
?>
