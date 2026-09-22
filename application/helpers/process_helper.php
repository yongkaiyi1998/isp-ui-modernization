<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// define ('SIGHUP',1);
// define ('SIGINT',2);
// define ('SIGQUIT',3);
// define ('SIGILL',4);
// define ('SIGTRAP',5);
// define ('SIGABRT',6);
// define ('SIGBUS',7);
// define ('SIGFPE',8);
// define ('SIGKILL',9);
// define ('SIGUSR1',10);
// define ('SIGSEGV',11);
// define ('SIGUSR2',12);
// define ('SIGPIPE',13);
// define ('SIGALRM',14);
// define ('SIGTERM',15);
// define ('SIGCHLD',17);
// define ('SIGCONT',18);
// define ('SIGSTOP',19);
// define ('SIGTSTP',20);
// define ('SIGTTIN',21);
// define ('SIGTTOU',22);
// define ('SIGURG',23);
// define ('SIGXCPU',24);
// define ('SIGXFSZ',25);
// define ('SIGVTALRM',26);
// define ('SIGPROF',27);
// define ('SIGWINCH',28);
// define ('SIGIO',29);
// define ('SIGPWR',30);
// define ('SIGSYS',31);

if ( ! function_exists('checkPID'))
{
	function checkPID($pid) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$processes = explode( "\n", shell_exec( "tasklist.exe" ));
			foreach( $processes as $process )
			{
				if (preg_match('/^(.*)\s+'.$pid.'/', $process)) { 
					return true; 
				} 
			}
			return false;
		} else {
			return file_exists( "/proc/$pid" );
		}
	}
}

if ( ! function_exists('startProcess'))
{
	function startProcess($cmd,$memory='64M') {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$command = "start /B php -qC -d log_errors=on -d memory_limit=$memory $cmd > NUL";
			pclose( popen( $command, 'r' ) );
		} else {
			shell_exec("nohup /usr/bin/php -qC -d log_errors=on -d memory_limit=$memory $cmd > /dev/null 2>/dev/null &");
		}
	}
}

if ( ! function_exists('stopProcess'))
{
	function stopProcess($pid) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			exec("taskkill /pid $pid /F"); 
		} else {
			posix_kill($pid,9);
		}
	}
}

if ( ! function_exists('execCmd'))
{
	function execCmd($cmd) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$command = "start /B $cmd > NUL";
			pclose( popen( $command, 'r' ) );
		} else {
			shell_exec("nohup $cmd > /dev/null 2>/dev/null &");
		}	
	}
}

if ( ! function_exists('execCmdWait'))
{
	function execCmdWait($cmd) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return shell_exec("$cmd");
		} else {
			return shell_exec("$cmd 2>/dev/null");
		}	
	}
}