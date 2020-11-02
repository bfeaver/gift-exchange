<?php

namespace App\Exchanger\Randomizer;

interface ExchangeRandomizer
{
    public function getValue(array $list): string;
}
