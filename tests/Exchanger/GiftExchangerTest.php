<?php

namespace App\Tests\Exchanger;

use App\Exchanger\GiftExchanger;
use App\Exchanger\Randomizer\ExchangeRandomizer;
use App\Exchanger\Randomizer\FirstItemRandomizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class GiftExchangerTest extends TestCase
{
    public function testGetAssignment()
    {
        $exchanger = new GiftExchanger(new FirstItemRandomizer());

        $assignment = $exchanger->getAssignment('foo', [], ['foo', 'bar', 'foobar', 'barfoo']);

        self::assertEquals('bar', $assignment);
    }

    public function testGetAssignmentRemovesName()
    {
        $randomizer = $this->prophesize(ExchangeRandomizer::class);
        $exchanger = new GiftExchanger($randomizer->reveal());

        $randomizer->getValue(Argument::any())->willReturn('bar');

        $exchanger->getAssignment('foo', [], ['foo', 'bar', 'foobar', 'barfoo']);

        $randomizer->getValue(Argument::not(Argument::containing('foo')))->shouldHaveBeenCalled();
    }

    public function testExclusionList()
    {
        $randomizer = $this->prophesize(ExchangeRandomizer::class);
        $exchanger = new GiftExchanger($randomizer->reveal());

        $randomizer->getValue(Argument::any())->willReturn('bar');

        $exchanger->getAssignment('foo', ['bar'], ['foo', 'bar', 'foobar', 'barfoo']);

        $randomizer->getValue(Argument::not(Argument::containing('foo')))->shouldHaveBeenCalled();
        $randomizer->getValue(Argument::not(Argument::containing('bar')))->shouldHaveBeenCalled();
    }
}
