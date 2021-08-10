<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        $admin          = new User();
        $admin_response = $admin->where('id', '=', $user_id)
            ->where('branch_id', 0)
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
        $admin          = new User();
        $admin_response = $admin->where('id', '=', $user_id)
            ->first();

        if (!isset($admin_response)) {
            echo "call admin response";
            die;
            return false;
        }
        Log::info("token", [$token]);
        Log::info("ad token", [$admin_response->token]);
        Log::info("ad token", [$admin_response->token]);
        if (!in_array($token, [$admin_response->token, $admin_response->branch_token])) {
            echo "call token";
            die;
            return false;
        }

        $session_token_expired_at = $admin_response->token_expiry;
        $login_as_branch          = false;
        if ($admin_response->branch_token == $token) {
            $login_as_branch          = true;
            $session_token_expired_at = $admin_response->branch_token_expiry;
        }

//        if (empty($session_token_expired_at) || !is_token_active($session_token_expired_at)) {
//            return false;
//        }

        return [
            'login_as_branch' => $login_as_branch
        ];
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

if (!function_exists('random_characters')) {
    function random_characters($count = 2)
    {
        $seed = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ^@!-_=+');
        shuffle($seed);
        $random_2_letter = '';
        foreach (array_rand($seed, $count) as $k) {
            $random_2_letter .= $seed[ $k ];
        }

        return $random_2_letter;
    }
}

if (!function_exists('bank_balance')) {
    function bank_balance($bank_id)
    {
        $transaction_total = Transaction::where('bank_id', $bank_id)
            ->select(DB::raw("(sum(credit) - sum(debit)) as total"))
            ->first();

        return checkEmpty($transaction_total, 'total', 0);
    }
}

if (!function_exists('generateTokenWithExpiration')) {
    function generateTokenWithExpiration()
    {
        $encrypted_string = Hash::make(rand() . time() . rand());
        $encrypted_string = str_replace(' ', '-', $encrypted_string); // Replaces all spaces with hyphens.
        $token_string     = preg_replace('/[^A-Za-z0-9\-]/', '', $encrypted_string); // Removes special chars.

        $token_expiry = time() + 3600000;

        return [
            'token'  => $token_string,
            'expiry' => $token_expiry,
        ];
    }
}
