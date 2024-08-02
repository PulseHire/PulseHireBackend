<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Employer extends Model
{
    use HasFactory;

    protected $table = 'employer';

    protected $fillable = [
        'user_id', 'company_name', 'company_industry', 'avatar', 'contact_person', 
        'website_url', 'phone', 'street', 'address_line1', 'address_line2', 
        'city', 'province', 'country', 'postal_code',
        'company_information', 'created_at', 'updated_at'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
