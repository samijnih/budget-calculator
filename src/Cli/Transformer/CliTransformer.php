<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Transformer;

use Assert\Assert;
use JsonSerializable;

final class CliTransformer
{
    /** @var JsonSerializable[] */
    private iterable $rows;
    /** @var callable[] */
    private array $decorators;

    /** @param JsonSerializable[] $rows */
    public function __construct(iterable $rows)
    {
        Assert::that($rows)->all()->isInstanceOf(JsonSerializable::class);

        $this->rows = $rows;
    }

    public function addDecorator(string $key, callable $decorator): void
    {
        $this->decorators[$key] = $decorator;
    }

    public function transform(array $keys): array
    {
        $iterable = [];

        foreach ($this->rows as $iteration => $row) {
            $iterable[$iteration] = [];

            $transformed = [];
            foreach ($row->jsonSerialize() as $key => $value) {
                if (!in_array($key, $keys, true)) {
                    continue;
                }

                $transformed[$key] = $this->decorate($key, $value);
            }

            $iterable[$iteration] = $transformed;
        }

        return $iterable;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    private function decorate(string $key, $value)
    {
        if (true === array_key_exists($key, $this->decorators)) {
            return $this->decorators[$key]($value);
        }

        return $value;
    }
}
