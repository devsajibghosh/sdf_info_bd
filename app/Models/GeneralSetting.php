<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_title',
        'site_description',
        'site_email',
        'site_phone',
        'software_version',
        'site_logo',
        'site_favicon',
        'currency',
        'default_paginate',
        'timezone',
        'sms_sender_id',
        'sms_api_key',
        
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];
}
