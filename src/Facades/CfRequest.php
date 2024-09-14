<?php

namespace PDPhilip\CfRequest\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \PDPhilip\CfRequest\CfRequest
 */
class CfRequest extends Facade
{
    public static function getFacadeRoot()
    {
        return new \PDPhilip\CfRequest\CfRequest(app('request'));
    }
}
