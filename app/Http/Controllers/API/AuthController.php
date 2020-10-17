<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AuthRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        try {
            $data = $request->all();

            $user_type = 'admin';

            $login_user = User::where('phone', $data['phone'])
                ->where('is_active', 1)
                ->first();

            if (empty($login_user) || !Hash::check($data['password'], $login_user->password)) {
                return prepare_response(200, false, 'Please enter correct login details');
            }

            $customer_type = $email = '';
            if ($login_user->branch_id > 0) {
                $user_type = 'branch';

                $branch = Branch::where('id', $login_user->branch_id)->first();

                if (!empty($branch)) {
                    $customer_type = $branch->customer_type;
                    $email         = checkEmpty($branch, 'branch_email', '');
                }
            }

            $login_token_data = $this->generateTokenWithExpiration();

            User::where('id', $login_user->id)->update([
                'token'        => $login_token_data['token'],
                'token_expiry' => $login_token_data['expiry']
            ]);

            $login_data = [
                'id'            => $login_user->id,
                'first_name'    => checkEmpty($login_user, 'first_name', ''),
                'last_name'     => checkEmpty($login_user, 'last_name', ''),
                'phone'         => checkEmpty($login_user, 'phone', ''),
                'email'         => $email,
                'user_type'     => $user_type,
                'token'         => $login_token_data['token'],
                'customer_type' => $customer_type
            ];

            return prepare_response(200, true, 'Login successfully', $login_data);
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function generateTokenWithExpiration()
    {
        $encrypted_string = Hash::make(rand() . time() . rand());
        $encrypted_string = str_replace(' ', '-', $encrypted_string); // Replaces all spaces with hyphens.
        $token_string     = preg_replace('/[^A-Za-z0-9\-]/', '', $encrypted_string); // Removes special chars.
        $token_expiry     = time() + 3600000;

        return [
            'token'  => $token_string,
            'expiry' => $token_expiry,
        ];
    }

    public function register(AuthRequest $request)
    {
        DB::beginTransaction();
        try {
            $data             = $request->all();
            $login_token_data = $this->generateTokenWithExpiration();

            $customer = Customer::create([
                'first_name'   => $data['first_name'],
                'last_name'    => $data['last_name'],
                'email'        => $data['email'],
                'phone'        => $data['phone'],
                'password'     => Hash::make($data['password']),
                'token'        => $login_token_data['token'],
                'token_expiry' => $login_token_data['expiry']
            ]);

            $customer_data = [
                'first_name' => checkEmpty($customer, 'first_name', ''),
                'last_name'  => checkEmpty($customer, 'last_name', ''),
                'phone'      => checkEmpty($customer, 'phone', ''),
                'email'      => checkEmpty($customer, 'email', ''),
                'token'      => $login_token_data['token']
            ];
            DB::commit();
            return prepare_response(200, true, 'You have been successfully registered with us', $customer_data);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
