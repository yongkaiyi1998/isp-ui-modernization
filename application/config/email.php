<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| Email Preferences
| -------------------------------------------------------------------
|
*/
$config['email_preferences_defined'] = FALSE;

if ($config['email_preferences_defined']) {
	$config['useragent'] = 'Codeigniter';
	$config['protocol'] = 'mail'; //mail, sendmail, or smtp
	$config['mailpath'] = '/usr/sbin/sendmail';
	$config['smtp_host'] = '';
	$config['smtp_user'] = '';
	$config['smtp_pass'] = '';
	$config['smtp_port'] = 25;
	$config['smtp_timeout'] = 5;
	$config['wordwrap'] = TRUE;
	$config['wrapchars'] = 76;
	$config['mailtype'] = 'text'; //text, html
	$config['charset'] = 'utf-8'; 
	$config['validate'] = FALSE;
	$config['priority'] = 3; // 1,2,3,4,5 , 1 = highest
	$config['crlf'] = "\r\n"; //"\r\n" or "\n" or "\r"
	$config['newline'] = '\n';
	$config['bcc_batch_mode'] = FALSE;
	$config['bcc_batch_size'] = 200;
}

/* End of file email.php */
/* Location: ./application/config/email.php */
