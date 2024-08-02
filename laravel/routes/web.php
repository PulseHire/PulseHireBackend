<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/user', [UserController::class, 'create']);
Route::get('/users', [UserController::class, 'list']);
Route::get('/user/{id}', [UserController::class, 'find']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::delete('/user/{id}', [UserController::class, 'delete']);
Route::post('/user/signup', [UserController::class, 'signup']);
Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/logout', [UserController::class, 'logout']);
Route::post('/user/email/verify', [UserController::class, 'emailVerify']);

Route::get('/employer/{userid}', [EmployerController::class, 'find']);
Route::put('/employer/{userid}', [EmployerController::class, 'update']);

Route::get('/candidate/{userid}', [CandidateController::class, 'find']);
Route::put('/candidate/{userid}', [CandidateController::class, 'update']);

Route::post('/job', [JobController::class, 'create']);
Route::get('/jobs', [JobController::class, 'list']);
Route::get('/job/{id}', [JobController::class, 'find']);
Route::post('/job/detail', [JobController::class, 'detail']);
Route::post('/job/apply', [JobController::class, 'apply']);
Route::post('/job/apply/info', [JobController::class, 'applyInfo']);
Route::post('/job/apply/confirm', [JobController::class, 'applyConfirm']);
Route::post('/job/save', [JobController::class, 'save']);
Route::post('/job/unsave', [JobController::class, 'unsave']);
Route::post('/job/save/info', [JobController::class, 'saveInfo']);

Route::post('/ai/interview/start', [AiController::class, 'interviewStart']);
Route::post('/ai/interview/answer', [AiController::class, 'interviewAnswer']);
Route::post('/ai/interview/summary', [AiController::class, 'interviewSummary']);