<?php

namespace Karma\Formatters;

use Karma\Formatter;

class Raw implements Formatter
{
    public function format($value)
    {
        return $value;
    }    
    
    public function getEmptyListStrategy()
    {
        return self::KEEP_LINE;
    }
}
