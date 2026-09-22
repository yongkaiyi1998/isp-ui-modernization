<?php
class Ldap_model extends CI_Model{

	private $ds;
	private $bind;

	public function __construct()
	{
		parent::__construct();
	}

	function test()
    {
    	$this->config->load('ldap');
    	print_r($this->config->item('ldap'));
    }

    private function connect_ldap() 
    {
    	if (!$this->ds) {
    		$this->config->load("ldap");
			$ldap_config = $this->config->item("ldap");

			$this->ds = ldap_connect($ldap_config['server']);
			if (!$ds) return false;

			//$this->bind = ldap_bind($ds,$ldap_config['username'],$ldap_config['password']);
			$this->bind = ldap_bind($ds,'cn=Manager,dc=penangfon,dc=net',$ldap_config['password']);
			if (!$this->bind) return false;
    	}

		return true;
    }

    private function close_ldap()
    {
		ldap_close($this->ds);
    }

    public function search_user($user)
	{
		if ($this->connect_ldapdb()) 
		{
			$sr=ldap_search($this->ds, "o=My Company, c=US", "sn=S*");  
		    echo "Search result is " . $sr . "<br />";
		    echo "Number of entries returned is " . ldap_count_entries($this->ds, $sr) . "<br />";

		    echo "Getting entries ...<p>";
		    $info = ldap_get_entries($this->ds, $sr);
		    echo "Data for " . $info["count"] . " items returned:<p>";

		    for ($i=0; $i<$info["count"]; $i++) {
		        echo "dn is: " . $info[$i]["dn"] . "<br />";
		        echo "first cn entry is: " . $info[$i]["cn"][0] . "<br />";
		        echo "first email entry is: " . $info[$i]["mail"][0] . "<br /><hr />";
		    }

			$this->close_ldap();
		}
	}
    
    /*
    	@param string $username
    	@param string $password
    	@param boolean $enabled
    */
    public function create_new_user($username, $password, $enabled=true)
    {
		if ($this->connect_ldapdb()) 
		{
			$entry = array("username"=>$username, "password"=>$password, "status"=>$enabled);
			ldap_add($this->dn, "users", $entry);
			$this->close_ldap();
		}
	}
	
	/*
    	@param string $username
    	@param boolean $enabled
    */
	public function update_user_status($username, $enabled)
	{
		if ($this->connect_ldapdb()) 
		{
			ldap_modify($this->ds, 'username='.$username, array("status"=>$enabled));
			$this->close_ldap();
		}
	}
	
	public function update_user_password($username, $password)
	{
		
	}

	public function update_group_user_status($params)
	{
		if ($this->connect_ldapdb()) 
		{
			foreach($params as $key => $val)
				ldap_modify($this->ds, 'username='.$key, array("status"=>$val));

			$this->close_ldap();
		}
	}

}