<?php 

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class System extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'system';
    }
}
