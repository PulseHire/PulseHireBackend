<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job';

    protected $fillable = [
        'post_user_id', 'title', 'description', 'responsibility', 'requirement', 'benefit',
        'type', 'min_salary', 'max_salary', 'work_experience', 'education_required',
        'address_line1', 'address_line2', 'city', 'province',
        'country', 'postal_code', 'remote_job', 'application_deadline', 'company_name',
        'company_industry', 'created_at', 'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
