<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class JobApply extends Model
{
    use HasFactory;

    protected $table = 'job_apply';

    protected $fillable = [
        'post_user_id', 'post_company_name',
        'apply_user_id', 'job_id', 'job_title', 'status', 'apply_user_first_name',
        'apply_user_last_name', 'apply_user_email', 'apply_at', 'created_at', 'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
