<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('dd')) {
    function dd(...$vars) {
        echo "<pre>";
        foreach ($vars as $var) {
            print_r($var);
        }
        echo "</pre>";
        die();
    }
}

if (!function_exists('_error_log')) {
    function _error_log($msg) {
        log_message('error', print_r($msg, true));
    }
}

if (!function_exists('_ci_log')) {
    function _ci_log($msg, $level = 'error') {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = isset($trace[1]) ? $trace[1] : [];

        $class    = isset($caller['class']) ? $caller['class'] : 'global';
        $function = isset($caller['function']) ? $caller['function'] : 'unknown';

        $context = sprintf("[%s::%s]", $class, $function);

        if (is_array($msg) || is_object($msg)) {
            $msg = print_r($msg, true);
        }

        log_message($level, "{$context} - {$msg}");
    }
}