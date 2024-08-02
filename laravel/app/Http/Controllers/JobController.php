<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employer;
use App\Models\Candidate;
use App\Models\Job;
use App\Models\JobApply;
use App\Models\JobSave;

use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    //
    public function create(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $userId = $request->get('post_user_id');
        $user = User::find($userId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $employer = Employer::where('user_id', $userId)
                ->first();

        $requestAllParams = $request->all();
        $requestAllParams['company_name'] = $employer->company_name;

        Job::create($requestAllParams);

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

        $searchTitle = $request->get('search_title');
        $searchLocation = $request->get('search_location');

        $filterPostUserId = $request->get('post_user_id');
        $filterMaxSalary = $request->get('filter_max_salary');
        $filterJobTypes = $request->get('filter_job_types');
        $filterRemoteJob = $request->get('filter_remote_job');
        $filterCities = $request->get('filter_cities');
        $filterIndustries = $request->get('filter_industries');

        $page = $request->get('page');
        $pagesize = $request->get('pagesize');
        $sort = $request->get('sort');

        $query = Job::query();
        if (!empty($searchTitle)) {
            $query->where('title', 'like', "%$searchTitle%");
        }
        if (!empty($searchLocation)) {
            $query->where('city', 'like', "%$searchLocation%")
                ->orWhere('province', 'like', "%$searchLocation%")
                ->orWhere('country', 'like', "%$searchLocation%");
        }
        if (!empty($filterPostUserId)) {
            $query->where('post_user_id', $filterPostUserId);
        }
        if (!empty($filterMaxSalary)) {
            $query->where('max_salary', '<=', $filterMaxSalary);
        }
        if (!empty($filterJobTypes)) {
            $parts = explode(",", $filterJobTypes);
            $filterJobTypesArr = array_map(function ($part) {
                return (int) str_replace("jt", "", $part);
            }, $parts);

            $query->whereIn('type', $filterJobTypesArr);
        }
        if (!empty($filterRemoteJob)) {
            $query->where('remote_job', 1);
        }
        if (!empty($filterCities)) {
            $filterCitiesArr = explode(",", $filterCities);
            $query->whereIn('city', $filterCitiesArr);
        }
        if (!empty($filterIndustries)) {
            $filterIndustriesArr = explode(",", $filterIndustries);
            if (!in_array('others', $filterIndustriesArr)) {
                $query->whereIn('company_industry', $filterIndustriesArr);
            } else {
                $diff = array_diff(['healthcare', 'technology', 'finance', 'manufacturing', 'retail', 'others'], $filterIndustriesArr);
                $query->whereNotIn('company_industry', $diff);
            }
        }

        if (!empty($sort)) {
            if ($sort == 'asc') {
                $query->orderBy('id', 'asc');
            } else if ($sort == 'desc') {
                $query->orderBy('id', 'desc');
            }
        }

        $total = $query->count();
        if (!empty($page) && !empty($pagesize)) {
            $query->skip(($page - 1) * $pagesize)->take($pagesize);
        }
        $jobs = $query->get();

        if (!$jobs) {
            $ret['code'] = 10002;
            $ret['message'] = 'query fail';
            return response()->json($ret);
        }

        $ret['code'] = 10000;
        $ret['message'] = 'query success';
        $ret['info']['page'] = $page;
        $ret['info']['pagesize'] = $pagesize;
        $ret['info']['total'] = $total;
        $ret['info']['list'] = $jobs;
        return response()->json($ret);
    }

    public function find(Request $request, $jobId)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $job = Job::find($jobId);
        if (!$job) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found job';
            return response()->json($ret);
        }

        $employer = Employer::where('user_id', $job->post_user_id)
                ->first();
        if (!$employer) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found job company info';
            return response()->json($ret);
        }
        $job['company_information'] = $employer->company_information;

        $ret['info'] = $job;
        $ret['code'] = 10000;

        return response()->json($ret);
    }

    public function detail(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $userId = $request->get('user_id');
        $user = User::find($userId);

        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $jobId = $request->get('job_id');
        $job = Job::find($jobId);
        if (!$job) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found job';
            return response()->json($ret);
        }

        $employer = Employer::where('user_id', $job->post_user_id)
                ->first();
        if (!$employer) {
            $ret['code'] = 10003;
            $ret['message'] = 'not found job company info';
            return response()->json($ret);
        }
        $job['company_information'] = $employer->company_information;

        $jobApply = JobApply::where('apply_user_id', $userId)
            ->where('job_id', $jobId)
            ->first();
        if ($jobApply) {
            $job['hasApply'] = 1;
        } else {
            $job['hasApply'] = 0;
        }

        $jobSave = JobSave::where('apply_user_id', $userId)
            ->where('job_id', $jobId)
            ->first();
        if ($jobSave) {
            $job['hasSave'] = 1;
        } else {
            $job['hasSave'] = 0;
        }

        $ret['info'] = $job;
        $ret['code'] = 10000;

        return response()->json($ret);
    }

    public function apply(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $userId = $request->get('apply_user_id');
        $user = User::find($userId);

        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $candidate = Candidate::where('user_id', $userId)
                ->first();
        if (!$candidate) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found candidate';
            return response()->json($ret);
        }

        $jobId = $request->get('job_id');
        $job = Job::find($jobId);

        if (!$job) {
            $ret['code'] = 10003;
            $ret['message'] = 'not found this job';
            return response()->json($ret);
        }

        $jobApplyParams = $request->all();
        $jobApplyParams['post_user_id'] = $job->post_user_id;
        $jobApplyParams['job_title'] = $job->title;
        $jobApplyParams['post_company_name'] = $job->company_name;
        $jobApplyParams['apply_user_first_name'] = $candidate->first_name;
        $jobApplyParams['apply_user_last_name'] = $candidate->last_name;
        $jobApplyParams['apply_user_email'] = $user->email;
        JobApply::create($jobApplyParams);

        $ret['code'] = 10000;
        $ret['message'] = 'apply success';

        return response()->json($ret);
    }

    public function applyInfo(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $postUserId = $request->get('post_user_id');
        $applyUserId = $request->get('apply_user_id');
        if (!empty($postUserId)) {
            $user = User::find($postUserId);
        } else if (!empty($applyUserId)) {
            $user = User::find($applyUserId);
        }

        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $page = $request->get('page');
        $pagesize = $request->get('pagesize');
        $sort = $request->get('sort');

        $query = JobApply::query();
        if (!empty($postUserId)) {
            $query->where('post_user_id', $postUserId);
        }
        if (!empty($applyUserId)) {
            $query->where('apply_user_id', $applyUserId);
        }

        if (!empty($sort)) {
            if ($sort == 'asc') {
                $query->orderBy('id', 'asc');
            } else if ($sort == 'desc') {
                $query->orderBy('id', 'desc');
            }
        }

        $total = $query->count();
        if (!empty($page) && !empty($pagesize)) {
            $query->skip(($page - 1) * $pagesize)->take($pagesize);
        }
        $jobApplys = $query->get();

        if (!$jobApplys) {
            $ret['code'] = 10002;
            $ret['message'] = 'query fail';
            return response()->json($ret);
        }

        $ret['code'] = 10000;
        $ret['message'] = 'query success';
        $ret['info']['page'] = $page;
        $ret['info']['pagesize'] = $pagesize;
        $ret['info']['total'] = $total;
        $ret['info']['list'] = $jobApplys;
        return response()->json($ret);
    }

    public function applyConfirm(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $jobApplyId = $request->get('job_apply_id');
        $jobApply = JobApply::find($jobApplyId);
        if (!$jobApply) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found this job application';
            return response()->json($ret);
        }

        if ($jobApply->status != 0) {
            $ret['code'] = 10002;
            $ret['message'] = 'can not change status of this job application';
            return response()->json($ret);
        }

        $confirmStatus = $request->get('confirm_status');
        $updateParams = [
            'status' => $confirmStatus
        ];
        $jobApply->update($updateParams);

        $toEmail = $jobApply->apply_user_email;
        if ($confirmStatus == 1) {
            $subject = "Update on Your Application for [$jobApply->job_title] at [$jobApply->post_company_name]";
            $message = "Dear [$jobApply->apply_user_first_name $jobApply->apply_user_last_name],

            Thank you for your interest in the [$jobApply->job_title] position at [$jobApply->post_company_name]. We 
            appreciate the time and effort you put into your application.

            After careful consideration, we regret to inform you that we have decided to proceed 
            with other candidates who more closely match our current needs and requirements. 
            Although we will not be moving forward with your application at this time, we encourage 
            you to apply for future opportunities that align with your qualifications and career 
            aspirations.

            We wish you the best of luck in your job search and future endeavors. Thank you once 
            again for your interest in [$jobApply->post_company_name].
            
            Best regards,
            PulseHire Team";
        } else if ($confirmStatus == 2) {
            $subject = "Interview Invitation for [$jobApply->job_title] at [$jobApply->post_company_name]";
            $message = "Dear [$jobApply->apply_user_first_name $jobApply->apply_user_last_name],

            Thank you for your interest in the [$jobApply->job_title] position at [$jobApply->post_company_name]. We are 
            pleased to inform you that your application has been reviewed, and we would like to 
            invite you for an interview.

            Please confirm your interest in scheduling an interview by replying to this email at your 
            earliest convenience. Once we receive your confirmation, we will provide you with the 
            available dates and times for the interview.

            We look forward to discussing how your skills and experiences align with our needs.

            Best regards,
            PulseHire Team";
        }

        Mail::raw($message, function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)
                    ->subject($subject);
        });

        $ret['code'] = 10000;
        $ret['message'] = 'update success';
        return response()->json($ret);
    }

    public function save(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $userId = $request->get('apply_user_id');
        $user = User::find($userId);

        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $candidate = Candidate::where('user_id', $userId)
                ->first();
        if (!$candidate) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found candidate';
            return response()->json($ret);
        }

        $jobId = $request->get('job_id');
        $job = Job::find($jobId);

        if (!$job) {
            $ret['code'] = 10003;
            $ret['message'] = 'not found this job';
            return response()->json($ret);
        }

        $jobSaveParams = $request->all();
        $jobSaveParams['job_title'] = $job->title;
        JobSave::create($jobSaveParams);

        $ret['code'] = 10000;
        $ret['message'] = 'save success';

        return response()->json($ret);
    }

    public function unsave(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $userId = $request->get('apply_user_id');
        $user = User::find($userId);

        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $candidate = Candidate::where('user_id', $userId)
                ->first();
        if (!$candidate) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found candidate';
            return response()->json($ret);
        }

        $jobId = $request->get('job_id');
        $job = Job::find($jobId);

        if (!$job) {
            $ret['code'] = 10003;
            $ret['message'] = 'not found this job';
            return response()->json($ret);
        }

        $jobSaveParams = $request->all();
        $jobSaveParams['job_title'] = $job->title;
        JobSave::where('apply_user_id', $userId)
                ->where('job_id', $jobId)
                ->delete();

        $ret['code'] = 10000;
        $ret['message'] = 'unsave success';

        return response()->json($ret);
    }

    public function saveInfo(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $applyUserId = $request->get('apply_user_id');
        $user = User::find($applyUserId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $page = $request->get('page');
        $pagesize = $request->get('pagesize');
        $sort = $request->get('sort');

        $query = JobSave::query();
        $query->where('apply_user_id', $applyUserId);

        if (!empty($sort)) {
            if ($sort == 'asc') {
                $query->orderBy('id', 'asc');
            } else if ($sort == 'desc') {
                $query->orderBy('id', 'desc');
            }
        }

        $total = $query->count();
        if (!empty($page) && !empty($pagesize)) {
            $query->skip(($page - 1) * $pagesize)->take($pagesize);
        }
        $jobApplys = $query->get();

        if (!$jobApplys) {
            $ret['code'] = 10002;
            $ret['message'] = 'query fail';
            return response()->json($ret);
        }

        $ret['code'] = 10000;
        $ret['message'] = 'query success';
        $ret['info']['page'] = $page;
        $ret['info']['pagesize'] = $pagesize;
        $ret['info']['total'] = $total;
        $ret['info']['list'] = $jobApplys;
        return response()->json($ret);
    }
}
