<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\CliMenu;

use BudgetCalculator\Cli\BudgetCalculator;
use BudgetCalculator\Cli\CliMenuBudgetCalculator;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Facade\UserFacade;
use BudgetCalculator\Helper\MoneyHelper;
use Money\Money;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuStyle;

class RegisterUserMenuBuilder implements MenuBuilder
{
    use MoneyHelper, FormatterHelper;

    private BudgetCalculator $app;

    private UserFacade $userFacade;
    private string $email;
    private string $password;
    private string $balanceAmount;
    private string $balanceCurrency;

    public function __construct(UserFacade $userFacade)
    {
        $this->userFacade = $userFacade;
    }

    public function name(): string
    {
        return 'Register';
    }

    public function attachApp(BudgetCalculator $app): void
    {
        $this->app = $app;
    }

    public function builder(): CliMenuBuilder
    {
        return (new CliMenuBuilder())
            ->setTitle('REGISTRATION')
            ->setWidth($this->app->terminal()->getWidth())
            ->setBackgroundColour(CliMenuBudgetCalculator::BG_COLOR)
            ->setForegroundColour(CliMenuBudgetCalculator::FG_COLOR)
            ->setExitButtonText('Quit')
            ->addLineBreak()
            ->addItem('Email 📧: e.g. john@doe.got', $this->emailCallback())
            ->addItem('Password 🗝:', $this->passwordCallback())
            ->addItem('Balance amount 💰: e.g. 1600', $this->balanceAmountCallback())
            ->addStaticItem('Balance currency 💱:')
            ->addRadioItem('€', $this->balanceCurrencyCallback())
            ->addRadioItem('$', $this->balanceCurrencyCallback())
            ->addLineBreak()
            ->addItem('Submit ✅', function () {
                $this->userFacade->register(
                    $this->userFacade->generateId()->toString(),
                    $this->email,
                    $this->password,
                    $this->buildMoney($this->balanceAmount, $this->balanceCurrency)
                );
            })
            ->addLineBreak()
            ->addLineBreak('-')
            ->addItem('Previous', new GoBackAction())
        ;
    }

    private function emailCallback(): callable
    {
        return function (CliMenu $menu) {
            $style = new MenuStyle();
            $style
                ->setBg('white')
                ->setFg('black')
                ->setWidth($menu->getTerminal()->getWidth())
            ;

            $this->email = $menu
                ->askText($style)
                ->setPromptText('Email:')
                ->setValidationFailedText('Please enter your email')
                ->ask()
                ->fetch()
            ;

            $currentItem = $menu->getSelectedItem();
            $currentItem->setText('Email 📧: '.$this->email);

            $menu->redraw();
        };
    }

    private function passwordCallback(): callable
    {
        return function (CliMenu $menu) {
            $style = new MenuStyle();
            $style
                ->setBg('white')
                ->setFg('black')
                ->setWidth($menu->getTerminal()->getWidth())
            ;

            $this->password = $menu
                ->askPassword($style)
                ->setPromptText('Password:')
                ->setValidator(static fn (string $password): bool => !empty($password))
                ->ask()
                ->fetch()
            ;

            $currentItem = $menu->getSelectedItem();
            $currentItem->setText('Password 🗝: 🔑');

            $menu->redraw();
        };
    }

    private function balanceAmountCallback(): callable
    {
        return function (CliMenu $menu) {
            $style = new MenuStyle();
            $style
                ->setBg('white')
                ->setFg('black')
                ->setWidth($menu->getTerminal()->getWidth())
            ;

            $this->balanceAmount = $this->replaceInString(',', '.', $menu
                ->askText($style)
                ->setPromptText('Balance amount:')
                ->ask()
                ->fetch()
            );

            $currentItem = $menu->getSelectedItem();
            $currentItem->setText('Balance amount 💰: '.$this->balanceAmount);

            $menu->redraw();
        };
    }

    private function balanceCurrencyCallback(): callable
    {
        return function (CliMenu $menu) {
            $style = new MenuStyle();
            $style
                ->setBg('white')
                ->setFg('black')
                ->setWidth($menu->getTerminal()->getWidth())
            ;

            $this->balanceCurrency = $menu->getSelectedItem()->getText();

            $menu->redraw();
        };
    }
}
