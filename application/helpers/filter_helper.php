<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function get_session_filter($key) {
    return $_SESSION['filter'][$key] ?? [];
}

function set_session_filter($key, $value) {
    $_SESSION['filter'][$key] = $value;
}

function clear_session_filter($key) {
    if (isset($_SESSION['filter'][$key])) {
        unset($_SESSION['filter'][$key]);
    }
}

function get_filtered_ajax_data($key, $defaultVals = [], $post_data = []) {
    $data = [];
    $initial = 0;

    if(!empty($post_data)) {
        $data = $post_data;
    } else {
        $session_data = get_session_filter($key);
        if(empty($session_data)) {
            $initial = 1;
        }
        foreach ($defaultVals as $key => $default) {
            $data[$key] = $session_data[$key] ?? $default;
        }
    }  

    if(!$initial) {
        $session_array = array_intersect_key($data, $defaultVals);
        set_session_filter($key, $session_array);
    }
    
    $return_val = ['data' => $data, 'initial' => $initial];

    return $return_val;
}