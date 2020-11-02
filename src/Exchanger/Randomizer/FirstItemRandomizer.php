<?php

namespace App\Exchanger\Randomizer;

/**
 * Returns the first item in the list.
 *
 * Not really a randomizer at all, but useful for tests.
 */
class FirstItemRandomizer implements ExchangeRandomizer
{
    public function getValue(array $list): string
    {
        return reset($list);
    }
}
