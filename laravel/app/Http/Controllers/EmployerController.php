<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employer;

class EmployerController extends Controller
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

        $employer = Employer::where('user_id', $user->id)
                ->first();

        $ret['info'] = $employer;
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

        $employer = Employer::where('user_id', $user->id)
                ->first();

        $employer->updated_at = now();
        $employer->update($request->all());

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

        $query = Employer::query();
        if (!empty($name)) {
            $query->where('company_name', 'like', "%$name%");
        }
        if (!empty($sort)) {
            if ($sort == '+id') {
                $query->orderBy('id', 'asc');
            } else if ($sort == '-id') {
                $query->orderBy('id', 'desc');
            }
        }

        $total = $query->count();
        if (!empty($page) && !empty($limit)) {
            $query->skip(($page - 1) * $limit)->take($limit);
        }
        $employers = $query->get();

        if (!$employers) {
            $ret['code'] = 10001;
            $ret['message'] = 'query fail';
        }

        $ret['code'] = 10000;
        $ret['info']['total'] = $total;
        $ret['info']['list'] = $employers;
        return response()->json($ret);
    }
}
