<?php

if ( ! function_exists('get_client_ip'))
{
	function get_client_ip() {
		// check for shared internet/ISP IP
		if (!empty($_SERVER['HTTP_X_REAL_IP']) && validate_ip($_SERVER['HTTP_X_REAL_IP'])) {
			return $_SERVER['HTTP_X_REAL_IP'];
		}

		if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// check for IPs passing through proxies
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// check if multiple ips exist in var
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
				$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($iplist as $ip) {
					if (validate_ip($ip))
						return $ip;
				}
			} else {
				if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
					return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
			return $_SERVER['HTTP_X_FORWARDED'];
		if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
			return $_SERVER['HTTP_FORWARDED_FOR'];
		if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
			return $_SERVER['HTTP_FORWARDED'];

		// return unreliable ip since all else failed
		return $_SERVER['REMOTE_ADDR'];
	}

	function validate_ip($ip) {
		/*if (filter_var($ip, FILTER_VALIDATE_IP,
                         FILTER_FLAG_IPV4 |
                         FILTER_FLAG_IPV6 |
                         FILTER_FLAG_NO_PRIV_RANGE |
                         FILTER_FLAG_NO_RES_RANGE) === false)
			return false;
		else
			return true;
		*/
		if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
			return false;
		} else {
			return true;
		}
	}
}

if ( ! function_exists('getOS'))
{
	function getOS() { 

	    $user_agent = $_SERVER['HTTP_USER_AGENT'];

	    $os_platform  = "Unknown OS Platform";

	    $os_array     = array(
	                          '/windows nt 10/i'      =>  'Windows 10',
	                          '/windows nt 6.3/i'     =>  'Windows 8.1',
	                          '/windows nt 6.2/i'     =>  'Windows 8',
	                          '/windows nt 6.1/i'     =>  'Windows 7',
	                          '/windows nt 6.0/i'     =>  'Windows Vista',
	                          '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
	                          '/windows nt 5.1/i'     =>  'Windows XP',
	                          '/windows xp/i'         =>  'Windows XP',
	                          '/windows nt 5.0/i'     =>  'Windows 2000',
	                          '/windows me/i'         =>  'Windows ME',
	                          '/win98/i'              =>  'Windows 98',
	                          '/win95/i'              =>  'Windows 95',
	                          '/win16/i'              =>  'Windows 3.11',
	                          '/macintosh|mac os x/i' =>  'Mac OS X',
	                          '/mac_powerpc/i'        =>  'Mac OS 9',
	                          '/linux/i'              =>  'Linux',
	                          '/ubuntu/i'             =>  'Ubuntu',
	                          '/iphone/i'             =>  'iPhone',
	                          '/ipod/i'               =>  'iPod',
	                          '/ipad/i'               =>  'iPad',
	                          '/android/i'            =>  'Android',
	                          '/blackberry/i'         =>  'BlackBerry',
	                          '/webos/i'              =>  'Mobile'
	                    );

	    foreach ($os_array as $regex => $value)
	        if (preg_match($regex, $user_agent))
	            $os_platform = $value;

	    return $os_platform;
	}

}