<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Employer;
use App\Models\Candidate;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    //
    public function create(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $user = User::create($request->all());

        $ret['code'] = 10000;
        $ret['message'] = 'create success';

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

        $query = User::query();
        if (!empty($name)) {
            $query->where('name', 'like', "%$name%");
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
        $users = $query->get();

        if (!$users) {
            $ret['code'] = 10001;
            $ret['message'] = 'query fail';
        }

        $ret['info']['total'] = $total;
        $ret['info']['list'] = $users;
        return response()->json($ret);
    }

    public function find($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $user = User::findOrFail($id);
        $user->updated_at = now();
        $user->update($request->all());

        $ret['code'] = 10000;
        $ret['message'] = 'update success';

        return response()->json($ret);
    }

    public function delete($id)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $user = User::findOrFail($id);
        $user->delete();

        $ret['code'] = 10000;
        $ret['message'] = 'delete success';
        return response()->json($ret);
    }

    public function login(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $email = $request->get('email');
        $password = $request->get('password');
        $role = $request->get('role');

        if (!in_array($role, ['candidate', 'employer'])) {
            $ret['code'] = 10001;
            $ret['message'] = 'illegal role';
            return response()->json($ret);
        }

        $user = User::where('email', $email)
                ->where('password', $password)
                ->where('role', $role)
                ->first();

        if (!$user) {
            $ret['code'] = 10002;
            $ret['message'] = 'account not exist or password wrong';
            return response()->json($ret);
        }

        $retInfo = clone $user;

        if ($role == 'candidate') {
            $candidate = Candidate::where('user_id', $user->id)
            ->first();
            $retInfo['name'] = $candidate->first_name . ' ' . $candidate->last_name;
            $retInfo['avatar'] = $candidate->avatar;
        } else if ($role == 'employer') {
            $employer = Employer::where('user_id', $user->id)
                ->first();
            $retInfo['name'] = $employer->company_name;
            $retInfo['avatar'] = $employer->avatar;
        }
        error_log('user: ' . json_encode($user));

        $bytes = random_bytes(16);
        $token = bin2hex($bytes);
        $user->token = $token;
        $user->save();

        $ret['info'] = $retInfo;
        $ret['info']['token'] = $token;
        $ret['code'] = 10000;

        return response()->json($ret);
    }

    public function logout(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $token = $request->get('token');

        $user = User::where('token', $token)
                ->first();

        // $ret['code'] = 10001;
        // $ret['message'] = 'token not correct';

        if ($user) {
            $user->token = '';
            $user->save();

            $ret['code'] = 10000;
            $ret['message'] = 'logout success';
        }

        return response()->json($ret);
    }

    public function signup(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $role = $request->get('role');
        if (!in_array($role, ['candidate', 'employer'])) {
            $ret['code'] = 10001;
            $ret['message'] = 'illegal role';
            return response()->json($ret);
        }

        $email = $request->get('email');
        $password = $request->get('password');
        $user = User::where('email', $email)
                ->where('password', $password)
                ->where('role', $role)
                ->first();

        if ($user) {
            $ret['code'] = 10002;
            $ret['message'] = 'user already exist';
            return response()->json($ret);
        }

        $user = new User;
        $user->email = $email;
        $user->password = $password;
        $user->role = $role;
        $user->email_verify_token = Str::uuid();
        $user->save();

        $dearName = '';
        if ($role == 'employer') {
            $companyName = $request->get('companyName');
            $phone = $request->get('phone');
            $addressLine1 = $request->get('addressLine1');
            $addressLine2 = $request->get('addressLine2');
            $city = $request->get('city');
            $province = $request->get('province');
            $country = $request->get('country');
            $postalCode = $request->get('postalCode');
            $companyInformation = $request->get('companyInformation');

            $contactPerson = $request->get('contactPerson');
            $companyIndustry = $request->get('companyIndustry');
            $websiteUrl = $request->get('websiteUrl');

            $employer = new Employer;
            $employer->user_id = $user->id;
            $employer->company_name = $companyName;
            $dearName = $companyName;
            $employer->company_industry = $companyIndustry;
            $employer->contact_person = $contactPerson;
            $employer->website_url = $websiteUrl;
            $employer->phone = $phone;
            $employer->address_line1 = $addressLine1;
            $employer->address_line2 = $addressLine2;
            $employer->city = $city;
            $employer->country = $country;
            $employer->province = $province;
            $employer->postal_code = $postalCode;
            $employer->company_information = $companyInformation;
            $employer->save();
        } else if ($role == 'candidate') {
            $firstName = $request->get('firstName');
            $lastName = $request->get('lastName');
            $phone = $request->get('phone');
            $addressLine1 = $request->get('addressLine1');
            $addressLine2 = $request->get('addressLine2');
            $city = $request->get('city');
            $province = $request->get('province');
            $country = $request->get('country');
            $currentJob = $request->get('currentJob');
            $university = $request->get('university');
            $experienceName = $request->get('experienceName');
            $experienceDescription = $request->get('experienceDescription');

            $candidate = new Candidate;
            $candidate->user_id = $user->id;
            $candidate->first_name = $firstName;
            $candidate->last_name = $lastName;
            $dearName = $firstName . ' ' . $lastName;
            $candidate->phone = $phone;
            $candidate->address_line1 = $addressLine1;
            $candidate->address_line2 = $addressLine2;
            $candidate->city = $city;
            $candidate->province = $province;
            $candidate->country = $country;
            $candidate->current_job = $currentJob;
            $candidate->university = $university;
            $candidate->experience_name = $experienceName;
            $candidate->experience_description = $experienceDescription;
            $candidate->save();
        }

        try {
            $toEmail = $email;
            $subject = "Email verify notification";
            $verifyUrl = "https://www.pulsehire.ca/copy-of-ai-matching?email_verify_token=$user->email_verify_token";
            $message = "Dear [$dearName],

            Please click below link to verify your email as soon as possible.
            
            $verifyUrl

            Best regards,
            PulseHire Team";

            Mail::raw($message, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)
                        ->subject($subject);
            });
        } catch (\Exception $e) {

        }
        
        $ret = [
            'info' => [
                'id' => $user->id
            ],
            'code' => 10000,
            'message' => 'signup success',
        ];

        return response()->json($ret);
    }

    public function emailVerify(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $emailVerifyToken = $request->get('email_verify_token');
        $user = User::where('email_verify_token', $emailVerifyToken)
                ->first();

        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $user->email_verify_status = 1;
        $user->save();
        
        $ret['code'] = 10000;
        $ret['info'] = [
            'role' => $user->role
        ];

        return response()->json($ret);
    }
}
