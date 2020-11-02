<?php

namespace App\Exchanger;

class GiftExchanger
{
    public function getAssignment(string $name, array $excludeList, array $fullList): string
    {
        $excludeList += [$name];

        $fullList = array_filter($fullList, function ($v) use ($excludeList) {
            return !in_array($v, $excludeList);
        });

        $key = array_rand($fullList);

        return $fullList[$key];
    }
}
