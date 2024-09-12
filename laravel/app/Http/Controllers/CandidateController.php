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

    public function getByEmployer(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $candidateId = $request->get('candidate_id');
        $candidate = Candidate::where('id', $candidateId)
                ->first();
        if (!$candidate) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found candidate';
            return response()->json($ret);
        }

        $user = User::find($candidate->user_id);
        if (!$user) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }
        $candidate['email'] = $user->email;

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
        $candidate->update($request->all());

        $ret['code'] = 10000;
        $ret['message'] = 'update success';

        return response()->json($ret);
    }

    public function list(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $name = $request->get('name');
        $page = $request->get('page');
        $limit = $request->get('limit');
        $sort = $request->get('sort');
        $candidateIDs = $request->get('candidate_ids');

        $query = Candidate::query();
        if (!empty($name)) {
            $query->where('first_name', 'like', "%$name%")
            ->orWhere('last_name', 'like', "%$name%");
        }
        if (!empty($sort)) {
            if ($sort == '+id') {
                $query->orderBy('id', 'asc');
            } else if ($sort == '-id') {
                $query->orderBy('id', 'desc');
            }
        }

        if (!empty($candidateIDs)) {
            $candidateIDArr = explode(",", $candidateIDs);
            $query->whereIn('id', $candidateIDArr)
            ->orderByRaw('FIELD(id, ' . implode(',', $candidateIDArr) . ')');
        }

        $total = $query->count();
        if (!empty($page) && !empty($limit)) {
            $query->skip(($page - 1) * $limit)->take($limit);
        }
        $candidates = $query->get();

        if (!$candidates) {
            $ret['code'] = 10001;
            $ret['message'] = 'query fail';
        }

        $ret['code'] = 10000;
        $ret['info']['total'] = $total;
        $ret['info']['list'] = $candidates;
        return response()->json($ret);
    }
}
