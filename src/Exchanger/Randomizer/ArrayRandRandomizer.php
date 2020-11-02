<?php

namespace App\Exchanger\Randomizer;

class ArrayRandRandomizer implements ExchangeRandomizer
{
    public function getValue(array $list): string
    {
        $key = array_rand($list);

        return $list[$key];
    }
}
