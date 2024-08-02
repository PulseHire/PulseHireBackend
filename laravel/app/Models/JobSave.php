<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class JobSave extends Model
{
    use HasFactory;

    protected $table = 'job_save';

    protected $fillable = [
        'apply_user_id', 'job_id', 'job_title',
        'created_at', 'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
