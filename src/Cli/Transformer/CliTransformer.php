<?php

declare(strict_types=1);

namespace App\Cli\Transformer;

use Assert\Assert;
use JsonSerializable;
use OutOfRangeException;

final class CliTransformer
{
    /** @var JsonSerializable[] */
    private iterable $transactions;
    /** @var callable[] */
    private array $decorators;

    public function __construct(iterable $transactions)
    {
        Assert::that($transactions)->all()->isInstanceOf(JsonSerializable::class);

        $this->transactions = $transactions;
    }

    public function setData(iterable $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function addDecorator(string $key, callable $decorator): void
    {
        $this->decorators[$key] = $decorator;
    }

    public function transform(array $keys): array
    {
        $iterable = [];

        foreach ($this->transactions as $iteration => $transaction) {
            $iterable[$iteration] = [];

            $transformed = [];
            foreach ($transaction->jsonSerialize() as $key => $value) {
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
