<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public function getFlagAttribute()
    {
        $code = $this->code;

        if ($code == 'en') $code = 'GB';
        if ($code == 'hi') $code = 'IN';
        if ($code == 'bn') $code = 'BD';

        $code   = strtoupper($code);
        $offset = 127397;
        $emoji  = '';
        foreach (str_split($code) as $char) {
            $emoji .= mb_chr(ord($char) + $offset, 'UTF-8');
        }

        return $emoji;
    }
}
