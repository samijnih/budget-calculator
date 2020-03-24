<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Adapter;

use BudgetCalculator\Cli\Output\Input;
use League\CLImate\CLImate;
use RuntimeException;

class ClimateAdapter implements Cli
{
    private CLImate $climate;

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    /**
     * @param  Input $input
     *
     * @return string|array
     */
    public function prompt(Input $input)
    {
        $label = $input->label();
        $options = $input->choices();

        switch ($input->type()) {
            case Input::TYPE_TEXT:
                $climateInput = $this->climate->input($label);

                if (!empty($input->accept())) {
                    $climateInput->accept($input->accept(), true);
                }
                if (!empty($input->defaultValue())) {
                    $climateInput->defaultTo($input->defaultValue());
                }

                return $climateInput->prompt();
                break;
            case Input::TYPE_PASSWORD:
                return $this->climate->password($label)->prompt();
                break;
            case Input::TYPE_RADIO:
                return $this->climate->radio($label, $options)->prompt();
                break;
            case Input::TYPE_CHECKBOXES:
                return $this->climate->checkboxes($label, $options)->prompt();
                break;
            default:
                $type = $input->type();
                throw new RuntimeException("Unknown type \"$type\".");
        }
    }

    public function lineBreak(int $number = 1): void
    {
        $this->climate->br($number);
    }

    public function tab(int $number): void
    {
        $this->climate->tab($number);
    }

    public function confirm(string $text): bool
    {
        return $this->climate->confirm($text)->confirmed();
    }

    public function outputError(string $error): void
    {
        $this->climate->to('error')->error($error);
    }

    public function outputInfo(string $text): void
    {
        $this->climate->info($text);
    }

    public function output(string $text, ?string $color = null): void
    {
        if (null === $color) {
            $this->climate->out($text);

            return;
        }

        $this->climate->$color($text);
    }

    public function table(array $data): void
    {
        $this->climate->table($data);
    }

    public function clear(): void
    {
        $this->climate->clear();
    }

    public function getWrappedCli(): CLImate
    {
        return $this->climate;
    }
}
