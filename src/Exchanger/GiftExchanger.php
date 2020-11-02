<?php

namespace App\Exchanger;

use App\Exchanger\Randomizer\ExchangeRandomizer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GiftExchanger implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ExchangeRandomizer $randomizer;

    public function __construct(ExchangeRandomizer $randomizer)
    {
        $this->randomizer = $randomizer;
    }

    public function getAssignment(string $name, array $excludeList, array $fullList): string
    {
        $excludeList[] = $name;

        $fullList = array_filter($fullList, function ($v) use ($excludeList) {
            return !in_array($v, $excludeList);
        });

        $fullList = array_values($fullList);

        $this->logger && $this->logger->debug(sprintf('Choosing for "%s"', $name), ['choices' => $fullList, 'excluded' => $excludeList]);

        return $this->randomizer->getValue($fullList);
    }
}
