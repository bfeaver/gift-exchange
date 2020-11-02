<?php

namespace App\Exchanger\Randomizer;

class ArrayRandRandomizer implements ExchangeRandomizer
{
    public function getValue(array $list): string
    {
        if (empty($list)) {
            throw new \InvalidArgumentException('List cannot be empty.');
        }

        $key = array_rand($list);

        return $list[$key];
    }
}
