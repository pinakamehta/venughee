<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CheckAdminAccess
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
        $admin = validate_admin_and_session_token($data['user_id'], $data['token']);
        if (!$admin) {
            return response()
                ->json([
                    'success' => false,
                    'code'    => 406,
                    'message' => 'User not found or your session token has been expired 123.'
                ]);
        }

        return $next($request);
    }
}
