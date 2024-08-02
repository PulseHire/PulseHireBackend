<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Candidate extends Model
{
    use HasFactory;

    protected $table = 'candidate';

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'avatar', 'phone', 'street',
        'address_line1', 'address_line2',
        'country', 'city', 'province', 'postal_code', 'current_job', 'skill',
        'degree', 'university', 'experience_name', 'experience_description',
        'resume_url', 'created_at', 'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
