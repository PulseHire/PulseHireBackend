<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Job;
use App\Models\Employer;
use App\Models\Candidate;
use Illuminate\Support\Facades\Redis;
use App\Services\ChatGptService;

class AiController extends Controller
{
    /**
     * request to start simulate interview
     */
    public function interviewStart(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $interviewUserId = $request->get('interview_user_id');
        $user = User::find($interviewUserId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $candidate = Candidate::where('user_id', $interviewUserId)
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
            $ret['message'] = 'not found job';
            return response()->json($ret);
        }

        $jobTypeDesc = '';
        switch ($job->type) {
            case 0:
                $jobTypeDesc = 'Full-time';
                break;
            case 1:
                $jobTypeDesc = 'Part-time';
                break;
            case 2:
                $jobTypeDesc = 'Contract';
                break;
            case 3:
                $jobTypeDesc = 'Volunteer';
                break;
            case 4:
                $jobTypeDesc = 'Freelance';
                break;
            default:
                $jobTypeDesc = 'Full-time';
                break;
        }
        $jobBenefitPlainText = strip_tags($job->benefit);
        $jobDescriptionPlainText = strip_tags($job->description);
        $jobResponsibilityPlainText = strip_tags($job->responsibility);
        $jobRequirementPlainText = strip_tags($job->requirement);


        $randomUuid = Str::random(16);
        $chatId = "${interviewUserId}_${randomUuid}";
        Redis::setex("chatInfo_${chatId}", 7 * 24 * 60 * 60, json_encode([]));

        $messages = [
            [
                'role' => "assistant",
                'content' => "I have a tech candidate's resume and a related job post. Please conduct a mock interview by asking me 10 questions one by one based on this information.
                    Resume:
                    Candidate name
                    {$candidate->first_name} {$candidate->last_name} 

                    Current job
                    {$candidate->current_job}

                    Location
                    {$candidate->city}, {$candidate->province}, {$candidate->country}

                    Top Skills
                    {$candidate->skill}

                    Degree
                    {$candidate->degree}

                    University
                    {$candidate->university}
                    
                    Experiences
                    {$candidate->experience_name}
                    {$candidate->experience_description}
                    
                    Job Post:
                    Job title
                    {$job->title}

                    Company name
                    {$job->company_name}

                    Company location
                    {$job->city}, {$job->province}, {$job->country}

                    Salary range
                    \\$ {$job->min_salary}-\\$ {$job->max_salary} a year
                    
                    Job type
                    {$jobTypeDesc}
                    
                    Job benefits
                    {$jobBenefitPlainText}
                    
                    Job description
                    {$jobDescriptionPlainText}
                    
                    Job responsibilities
                    {$jobResponsibilityPlainText}

                    Job requirements
                    {$jobRequirementPlainText}
                    '''
                    
                    Please start the interview and ask questions one by one. You should also adjust the question base on my answer but most of the question should related to job post"
            ],
            [
                'role' => 'assistant',
                'content' => "When the mock interview is ended, please return message as 'Interview Finished.'"
            ],
            [
                'role' => 'assistant',
                'content' => "There are some rules must be satistified:
                        1, Each response text started with this prefix: [PulseHire]
                        2, User can only answer questions, if he asks a question, please notify him/her to answer interview question properly
                        3, Do not prompt the user to ask questions
                        4, Avoid leading or suggesting questions in your responses
                    "
            ]    
        ];
        
        $chatGptService = new ChatGptService();
        $gptMsg = $chatGptService->generateText($messages);
        $chatHistory = $messages;
        $chatHistory[] = [
            "role" => "system",
            "content" => $gptMsg,
        ];
        Redis::setex("chatInfo_${chatId}", 7 * 24 * 60 * 60, json_encode($chatHistory));

        $ret['code'] = 10000;
        $ret['info'] = [
            'chatId' => $chatId,
            'chatHistory' => $chatHistory
        ];
        $ret['message'] = 'start interview successfully.';

        return response()->json($ret);
    }

    public function interviewAnswer(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $interviewUserId = $request->get('interview_user_id');
        $user = User::find($interviewUserId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $chatId = $request->get('chat_id');
        $cacheKey = "chatInfo_${chatId}";
        $chatHistory = Redis::get($cacheKey);
        if (!$chatHistory) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found interview';
            return response()->json($ret);
        }

        $userAnswer = $request->get('user_answer');
        $messages = json_decode($chatHistory, true);
        $messages[] = [
            'role' => "user",
            "content" => $userAnswer
        ];

        $chatGptService = new ChatGptService();
        $gptMsg = $chatGptService->generateText($messages);

        $chatHistory = $messages;
        $chatHistory[] = [
            "role" => "system",
            "content" => $gptMsg,
        ];
        Redis::setex("chatInfo_${chatId}", 7 * 24 * 60 * 60, json_encode($chatHistory));

        $ret['code'] = 10000;
        $ret['info'] = [
            'chatId' => $chatId,
            'chatHistory' => $chatHistory
        ];
        $ret['message'] = 'user answer successfully.';

        return response()->json($ret);
    }

    public function interviewSummary(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $interviewUserId = $request->get('interview_user_id');
        $user = User::find($interviewUserId);
        if (!$user) {
            $ret['code'] = 10001;
            $ret['message'] = 'not found user';
            return response()->json($ret);
        }

        $chatId = $request->get('chat_id');
        $cacheKey = "chatInfo_${chatId}";
        $chatHistory = Redis::get($cacheKey);
        if (!$chatHistory) {
            $ret['code'] = 10002;
            $ret['message'] = 'not found interview';
            return response()->json($ret);
        }

        $messages = json_decode($chatHistory, true);
        $messages[] = [
            'role' => "assistant",
            "content" => "Please give this candidate some comment and rating scores on his/her interview."
        ];

        $chatGptService = new ChatGptService();
        $gptMsg = $chatGptService->generateText($messages);

        $chatHistory = $messages;
        $chatHistory[] = [
            "role" => "system",
            "content" => $gptMsg,
        ];
        Redis::setex("chatInfo_${chatId}", 7 * 24 * 60 * 60, json_encode($chatHistory));

        $ret['code'] = 10000;
        $ret['info'] = [
            'chatId' => $chatId,
            'chatHistory' => $chatHistory
        ];
        $ret['message'] = 'interview summary successfully.';

        return response()->json($ret);
    }

    /**
     * request to match recommend
     */
    public function matchRecommend(Request $request)
    {
        $ret = [
            'info' => [],
            'code' => -1,
            'message' => '',
        ];

        $postUserId = $request->get('post_user_id');
        $user = User::find($postUserId);
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

        $jobTypeDesc = '';
        switch ($job->type) {
            case 0:
                $jobTypeDesc = 'Full-time';
                break;
            case 1:
                $jobTypeDesc = 'Part-time';
                break;
            case 2:
                $jobTypeDesc = 'Contract';
                break;
            case 3:
                $jobTypeDesc = 'Volunteer';
                break;
            case 4:
                $jobTypeDesc = 'Freelance';
                break;
            default:
                $jobTypeDesc = 'Full-time';
                break;
        }
        $jobBenefitPlainText = strip_tags($job->benefit);
        $jobDescriptionPlainText = strip_tags($job->description);
        $jobResponsibilityPlainText = strip_tags($job->responsibility);
        $jobRequirementPlainText = strip_tags($job->requirement);

        $randomUuid = Str::random(16);
        $chatId = "${postUserId}_${randomUuid}";
        Redis::setex("chatInfo_${chatId}", 7 * 24 * 60 * 60, json_encode([]));

        $query = Candidate::query();
        $candidates = $query->get();
        $candidateInfos = [];
        foreach ($candidates as $candidate) {
            $candidateInfo = [
                'id' => $candidate->id,
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'address' => "{$candidate->city}, {$candidate->province}, {$candidate->country}",
                'current_job' => $candidate->current_job,
                'skill' => $candidate->skill,
                'degree' => $candidate->degree,
                'university' => $candidate->university,
                'experience' => $candidate->experience,
                'experience_name' => $candidate->experience_name,
            ];
            $candidateInfos[] = $candidateInfo; 
        }
        $candidateInfosJsonStr = json_encode($candidateInfos);

        $messages = [
            [
                'role' => "assistant",
                'content' => "I have a candidate dataset and a job post. Please analyze and find out the result.
                    Candidate dataset:
                    {$candidateInfosJsonStr}

                    Job Post:
                    Job title
                    {$job->title}

                    Company name
                    {$job->company_name}

                    Company location
                    {$job->city}, {$job->province}, {$job->country}

                    Company Industry
                    {$job->company_industry}

                    Salary range
                    \\$ {$job->min_salary}-\\$ {$job->max_salary} a year
                    
                    Job type
                    {$jobTypeDesc}
                    
                    Job benefits
                    {$jobBenefitPlainText}
                    
                    Job description
                    {$jobDescriptionPlainText}
                    
                    Job responsibilities
                    {$jobResponsibilityPlainText}

                    Job requirements
                    {$jobRequirementPlainText}
                    "
            ],
            [
                'role' => 'assistant',
                'content' => "When the mock interview is ended, please return message as 'Interview Finished.'"
            ],
            [
                'role' => 'assistant',
                'content' => "There are some rules must be satistified:
                        1, You must choose 5 candidates, scored from high match to low match (10 highest, 1 lowest)
                        2, Leave comment and score to inform us why they are the matched ones.
                        3, If no one matches perfectly, find the closest match.
                        4, You do not need to tell us what you are doing, just show the result
                        5, At the end of the response, please print a new line like this: CandidateIDs: [1,2,3,4,5], which tell us the recommend candidate IDs.
                        6, CandidateIDs must corerespond to the recommended order, and the count must be 5
                    "
            ]    
        ];
        
        $chatGptService = new ChatGptService();
        $gptMsg = $chatGptService->generateText($messages);
        $chatHistory = $messages;
        $chatHistory[] = [
            "role" => "system",
            "content" => $gptMsg,
        ];
        Redis::setex("chatInfo_${chatId}", 7 * 24 * 60 * 60, json_encode($chatHistory));

        $ret['code'] = 10000;
        $ret['info'] = [
            'chatId' => $chatId,
            'chatHistory' => $chatHistory
        ];
        $ret['message'] = 'match recommend successfully.';

        return response()->json($ret);
    }
}
