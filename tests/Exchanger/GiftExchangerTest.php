<?php

namespace App\Tests\Exchanger;

use App\Exchanger\GiftExchanger;
use PHPUnit\Framework\TestCase;

class GiftExchangerTest extends TestCase
{
    public function testGetAssignment()
    {
        $exchanger = new GiftExchanger();

        $assignment = $exchanger->getAssignment('foo', [], ['foo', 'bar', 'foobar', 'barfoo']);

        self::assertNotEquals('foo', $assignment);
    }
}
