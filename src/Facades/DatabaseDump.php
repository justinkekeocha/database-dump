<?php

namespace Justinkekeocha\DatabaseDump\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Justinkekeocha\DatabaseDump\DatabaseDump
 */
class DatabaseDump extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Justinkekeocha\DatabaseDump\DatabaseDump::class;
    }
}
