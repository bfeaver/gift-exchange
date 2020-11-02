<?php

namespace App\Exchanger;

use App\Exchanger\Randomizer\ExchangeRandomizer;

class GiftExchanger
{
    private ExchangeRandomizer $randomizer;

    public function __construct(ExchangeRandomizer $randomizer)
    {
        $this->randomizer = $randomizer;
    }

    public function getAssignment(string $name, array $excludeList, array $fullList): string
    {
        $excludeList += [$name];

        $fullList = array_filter($fullList, function ($v) use ($excludeList) {
            return !in_array($v, $excludeList);
        });

        $fullList = array_values($fullList);

        return $this->randomizer->getValue($fullList);
    }
}
