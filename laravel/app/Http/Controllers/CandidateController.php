<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Candidate;

class CandidateController extends Controller
{
    public function find($userId)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $user = User::find($userId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $candidate = Candidate::where('user_id', $user->id)
                ->first();

        $ret['info'] = $candidate;
        $ret['code'] = 10000;

        return response()->json($ret);
    }

    public function update(Request $request, $userId)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $user = User::findOrFail($userId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $candidate = Candidate::where('user_id', $user->id)
                ->first();

        $candidate->updated_at = now();
        error_log("start to update can..." . json_encode($request->all()));
        $candidate->update($request->all());

        $ret['code'] = 10000;
        $ret['message'] = 'update success';

        return response()->json($ret);
    }
}
