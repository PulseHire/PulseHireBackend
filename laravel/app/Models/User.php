<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class User extends Model
{
    use HasFactory;

    protected $table = 'user';

    protected $fillable = [
        'email', 'password', 'role', 'email_verify_status', 'email_verify_token',
        'created_at', 'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
