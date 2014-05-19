<?php

namespace Karma;

interface Formatter
{
    const
        KEEP_LINE = '<keepline>',
        REMOVE_LINE = '<removeline>';
    
    public function format($value);
    
    public function getEmptyListStrategy();
}