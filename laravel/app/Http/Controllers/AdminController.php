<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $username = $request->get('username');
        $password = $request->get('password');

        $admin = Admin::where('name', $username)
                ->where('password', $password)
                ->first();

        if (!$admin) {
            $ret['code'] = 10001;
            $ret['message'] = 'Account or password not correct';
            return response()->json($ret);
        }
        
        $bytes = random_bytes(16);
        $token = bin2hex($bytes);
        $admin->token = $token;
        $admin->save();

        $ret['info'] = $admin;
        $ret['info']['token'] = $token;
        $ret['code'] = 10000;

        return response()->json($ret);
    }

    public function infoByToken(Request $request)
    {
        $token = $request->get('token');

        $admin = Admin::where('token', $token)
                ->first();

        $ret = [
            'info' => [],
            'code' => 10001,
            'message' => 'token is not correct',
        ];
        if ($admin) {
            $admin['roles'] = explode(",", $admin['role']);
            $ret['info'] = $admin;
            $ret['code'] = 10000;
        }

        return response()->json($ret);
    }

    public function logout(Request $request)
    {
        $token = $request->get('token');

        $admin = Admin::where('token', $token)
                ->first();

        $ret = [
            'info' => [],
            'code' => 10001,
            'message' => 'token is not correct',
        ];
        if ($admin) {
            $admin->token = '';
            $admin->save();

            $ret['code'] = 10000;
            $ret['message'] = 'logout successfully';
        }

        return response()->json($ret);
    }
}
