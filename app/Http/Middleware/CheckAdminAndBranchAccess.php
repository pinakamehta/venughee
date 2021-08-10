<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckAdminAndBranchAccess
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info("calllllllllllllllllllllllllllllllllllllllllllllllllllll");
        $request->request->add(['token' => $request->header('Authorization')]);
        $data      = $request->all();
        $validator = Validator::make($data, ['user_id' => 'required|integer', 'token' => 'required']);
        if ($validator->fails()) {
            return response()
                ->json([
                    'success' => false,
                    'code'    => 201,
                    'message' => $validator->getMessageBag()->first()
                ]);
        }

        $response = validate_admin_or_branch_and_session_token($data['user_id'], $data['token']);

        if (!$response) {
            return response()
                ->json([
                    'success' => false,
                    'code'    => 406,
                    'message' => 'User not found or your session token has been expired.'
                ]);
        }

        if ($response['login_as_branch'] && $request->method() != 'GET') {
            return response()
                ->json([
                    'success' => false,
                    'code'    => 406,
                    'message' => 'You have no rights for this action'
                ]);
        }
        return $next($request);
    }
}
