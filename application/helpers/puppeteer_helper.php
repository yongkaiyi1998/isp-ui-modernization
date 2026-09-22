<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('puppeteer_print_preview'))
{
	function puppeteer_print_preview($url, $filename, $proj_path='', $chrome_loc='', $orientation='portrait', $format='A4') {
        if (empty($url) || empty($filename)) {
            return array(false, 'did not specify url or filename');
        }

        if (strpos($url, "?") !== false) {
            $url = $url."&".rand(1,100);
        } else {
            //"?" not found
            $url = $url."?".rand(1,100);
        }

        log_message('error', NODE_PATH.'node '.$proj_path.'node/generate_pdf.js --url="'.$url.'" --file_name="'.$filename.'" --chrome_path="'.$chrome_loc.'" --orientation="'.$orientation.'" --format="'.$format.'"');

        $exec_return = execCmdWaitDebug(NODE_PATH.'node '.$proj_path.'node/generate_pdf.js --url="'.$url.'" --file_name="'.$filename.'" --chrome_path="'.$chrome_loc.'" --orientation="'.$orientation.'" --format="'.$format.'"');
        log_message('error', print_r($exec_return, true));
        return array(true, '');
	}
}

if ( ! function_exists('puppeteer_print_preview_forpost'))
{
    function puppeteer_print_preview_forpost($url, $filename, $post, $proj_path='', $chrome_loc='') {
        if (empty($url) || empty($filename)) {
            return array(false, 'did not specify url or filename');
        }

        if (empty($post)) {
            return array(false, 'did not specify post values');
        }

        if (strpos($url, "?") !== false) {
            $url = $url."&".rand(1,100);
        } else {
            //"?" not found
            $url = $url."?".rand(1,100);
        }
        
        //log_message('error', $post);
        log_message('error', NODE_PATH.'node '.$proj_path.'node/generate_pdf_post.js --url="'.$url.'" --file_name="'.$filename.'" --post_values="'.addslashes($post).'" --chrome_path="'.$chrome_loc.'"');

        $exec_return = execCmdWaitDebug(NODE_PATH.'node '.$proj_path.'node/generate_pdf_post.js --url="'.$url.'" --file_name="'.$filename.'" --post_values="'.addslashes($post).'" --chrome_path="'.$chrome_loc.'"');

        log_message('error', print_r($exec_return, true));

        if ($exec_return == 'ERROR') {
            return array(false, '');
        } else {
            return array(true, '');
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

if ( ! function_exists('execCmdWaitDebug'))
{
    function execCmdWaitDebug($cmd) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return shell_exec("$cmd");
        } else {
            return shell_exec("$cmd");
        }   
    }
}