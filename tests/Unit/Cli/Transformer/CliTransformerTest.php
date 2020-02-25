<?php

namespace Tests\Unit\Cli\Transformer;

use BudgetCalculator\Cli\Transformer\CliTransformer;
use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\TestCase;

final class CliTransformerTest extends TestCase
{
    private CliTransformer $sut;

    protected function setUp(): void
    {
        $object = new class () implements JsonSerializable {
            public function jsonSerialize(): array
            {
                return [
                    'key_one' => 'toDecorate',
                    'key_two' => 'doNotDecorate',
                ];
            }
        };

        $this->sut = new CliTransformer([$object]);
    }

    /** @test */
    public function constructorFails(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CliTransformer([['not' => 'json serialize']]);
    }

    /** @test */
    public function transformValues(): void
    {
        $key = 'key_one';
        $this->sut->addDecorator($key, fn (string $value): string => 'decorated');

        $actual = $this->sut->transform(['key_one', 'key_two']);

        $expected = [
            [
                'key_one' => 'decorated',
                'key_two' => 'doNotDecorate',
            ],
        ];
        static::assertSame($expected, $actual);
    }
}
