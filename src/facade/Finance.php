<?php

namespace finance\facade;

use think\Facade;

/**
 * Class Finance
 * @package finance\facade
 * @mixin \finance\Finance
 */
class Finance extends Facade
{
    protected static function getFacadeClass()
    {
        return 'finance\Finance';
    }
}
