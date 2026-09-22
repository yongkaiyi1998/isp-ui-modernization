<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('parse_email_template')) {

    function parse_email_template($email_title, $email_msg, $data) {
        $placeholders = [
            'customer_no' => ['%SUBSCRIBER_NO%', '%customer_no%'],
            'name' => ['%CUSTOMER_NAME%', '%customer_name%'],
            'isp_name' => ['%ISP_NAME%'],
            'company_full_name' => ['%COMPANY_NAME%'],
            'company_phone' => ['%COMPANY_PHONE%'],
            'company_email' => ['%COMPANY_EMAIL%']
        ];

        $return_array = ['email_title' => $email_title, 'email_msg' => $email_msg, 'unresolved_placeholder' => []];

        foreach ($placeholders as $field => $placeholder) {
            foreach ($placeholder as $key => $value) {
                if(strpos($email_title, $value) !== false && $data[$field] != '') {
                    $email_title = str_replace($value, $data[$field], $email_title);
                }
                if(strpos($email_msg, $value) !== false && $data[$field] != '') {
                    $email_msg = str_replace($value, $data[$field], $email_msg);
                }
            }
            $return_array['email_title'] = $email_title;
            $return_array['email_msg'] = $email_msg;
        }

        // get the remaining placeholders that not yet replaced
        $placeholder_pattern = "/%[A-Za-z0-9_]+%/";
        preg_match_all($placeholder_pattern, $email_title, $title_matches);
        preg_match_all($placeholder_pattern, $email_msg, $msg_matches);
        $return_array['unresolved_placeholder'] = array_merge($title_matches[0], $msg_matches[0]);

        return $return_array;
    }

}