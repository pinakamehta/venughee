<?php

use App\Models\Admin;

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

if (!function_exists('validate_admin_and_session_token')) {
    function validate_admin_and_session_token($user_id, $token)
    {
        $admin          = new Admin();
        $admin_response = $admin->where('id', '=', $user_id)
            ->first();

        if (!isset($admin_response)) {
            return false;
        }

        $session_token_expired_at = $admin_response->token_expiry;

        if (empty($session_token_expired_at) || !is_token_active($session_token_expired_at)) {
            return false;
        }

        return $admin_response;
    }
}

if (!function_exists('validate_admin_or_branch_and_session_token')) {
    function validate_admin_or_branch_and_session_token($user_id, $token)
    {
        $admin          = new Admin();
        $admin_response = $admin->where('id', '=', $user_id)
            ->first();

        if (!isset($admin_response)) {
            return false;
        }

        $session_token_expired_at = $admin_response->token_expiry;

        if (empty($session_token_expired_at) || !is_token_active($session_token_expired_at)) {
            return false;
        }

        return $admin_response;
    }
}

if (!function_exists('is_token_active')) {
    function is_token_active($token_expiry)
    {
        if ($token_expiry >= time()) {
            return true;
        } else {
            return false;
        }
    }
}
