<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('compare_field_change')) {
    /**
     * Compare old vs new field values and return change text if different.
     *
     * @param string $label   Human-readable field name
     * @param mixed  $oldValue Old DB value
     * @param mixed  $newValue New posted value
     * @param array  $lookup   Optional lookup array (e.g. category/status arrays)
     * @param bool   $escape   If true, escape using CI's db escape_str
     * @param object $db       CodeIgniter DB instance
     * @return string
     */
    function compare_field_change($label, $oldValue, $newValue, $lookup = [], $escape = false, $db = null)
    {
        if ($escape && $db) {
            $newValue = $db->escape_str($newValue);
        }

        if ($oldValue == $newValue) {
            return '';
        }

        if (!empty($lookup)) {
            $oldValue = isset($lookup[$oldValue]) ? $lookup[$oldValue] : $oldValue;
            $newValue = isset($lookup[$newValue]) ? $lookup[$newValue] : $newValue;
        }

        return "{$label} Changed from {$oldValue} to {$newValue}. ";
    }
}

if (!function_exists('compare_json_change')) {
    /**
     * Compare old vs new JSON arrays and return change text if different.
     *
     * @param string $label   Human-readable field name
     * @param string $oldJson Old JSON string from DB
     * @param string $newJson New JSON string from POST
     * @param array  $lookup  Optional lookup array to translate IDs to names
     * @return string
     */
    function compare_json_change($label, $oldJson, $newJson, $lookup = [])
    {
        $oldArr = json_decode($oldJson, true) ?: [];
        $newArr = json_decode($newJson, true) ?: [];

        sort($oldArr);
        sort($newArr);
        
        if ($oldArr == $newArr) {
            return '';
        }

        if (!empty($lookup)) {
            $oldArr = array_map(fn($k) => $lookup[$k] ?? $k, $oldArr);
            $newArr = array_map(fn($k) => $lookup[$k] ?? $k, $newArr);
        }

        return "{$label} Changed from " . implode(',', $oldArr) . " to " . implode(',', $newArr) . ". ";
    }
}
