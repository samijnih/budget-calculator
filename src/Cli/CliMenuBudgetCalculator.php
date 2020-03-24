<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli;

use BudgetCalculator\Cli\CliMenu\MenuBuilder;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\Style\SelectableStyle;
use PhpSchool\CliMenu\Terminal\TerminalFactory;
use PhpSchool\Terminal\Terminal;

final class CliMenuBudgetCalculator implements BudgetCalculator
{
    public const BG_COLOR = 'black';
    public const FG_COLOR = 'green';

    private Terminal $terminal;
    private CliMenuBuilder $cliMenuBuilder;
    /** @var MenuBuilder[] */
    private array $menuBuilders;

    public function __construct(string $appName)
    {
        $this->terminal = TerminalFactory::fromSystem();

        $this->cliMenuBuilder = (new CliMenuBuilder($this->terminal))
            ->setTitle($appName)
            ->setWidth($this->terminal->getWidth())
            ->setBackgroundColour(self::BG_COLOR)
            ->setForegroundColour(self::FG_COLOR)
            ->setExitButtonText($this->getExitButtonText())
            ->addLineBreak()
            ->modifySelectableStyle(static function (SelectableStyle $style) {
                $style->setUnselectedMarker('  ')
                    ->setSelectedMarker('→ ');
            })
        ;
    }

    public function terminal(): Terminal
    {
        return $this->terminal;
    }

    public function registerMenuBuilder(MenuBuilder $menuBuilder): void
    {
        $menuBuilder->attachApp($this);

        $this->menuBuilders[] = $menuBuilder;
    }

    public function run(): void
    {
        foreach ($this->menuBuilders as $menuBuilder) {
            $this
                ->cliMenuBuilder
                ->addSubMenuFromBuilder($menuBuilder->name(), $menuBuilder->builder());
        }

        $this
            ->cliMenuBuilder
            ->addLineBreak()
            ->addLineBreak('-')
            ->build()
            ->open();
    }

    private function getExitButtonText(): string
    {
        return 'Quit';
    }
}
