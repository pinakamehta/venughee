<?php

if (!function_exists('prepare_response')) {
    function prepare_response($code, $status, $message, $data = [], $extra_data = [])
    {
        if (!empty($extra_data)) {
            $data = array_merge($data, $extra_data);
        }

        return response()->json([
            'code'    => $code,
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ]);
    }
}

if (!function_exists('checkEmpty')) {
    /**
     * Check if given field is empty or not
     * @param $data
     * @param string $field_name
     * @param null $default_response
     * @return null
     */
    function checkEmpty($data, $field_name = '', $default_response = null)
    {
        if (gettype($data) == 'object') {
            $data = $data->toArray();
        }
        return !empty($data) && !empty($data[ $field_name ]) ? $data[ $field_name ] : $default_response;
    }
}
