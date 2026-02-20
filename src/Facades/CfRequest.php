<?php

namespace PDPhilip\CfRequest\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \PDPhilip\CfRequest\CfRequest
 */
class CfRequest extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PDPhilip\CfRequest\CfRequest::class;
    }
}
