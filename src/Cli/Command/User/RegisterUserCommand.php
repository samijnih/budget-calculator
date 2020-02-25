<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\User;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Facade\UserFacade;
use League\CLImate\CLImate;
use Money\Currency;
use Money\MoneyParser;
use Throwable;

class RegisterUserCommand implements Command
{
    public const NAME = 'user:register';

    private Climate $climate;
    private MoneyParser $moneyParser;
    private UserFacade $userFacade;

    public function __construct(
        Climate $climate,
        MoneyParser $moneyParser,
        UserFacade $userFacade
    ) {
        $this->climate = $climate;
        $this->moneyParser = $moneyParser;
        $this->userFacade = $userFacade;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function execute(): void
    {
        $this->climate->br();

        $answers = [
            'email' => $this->climate->input('Email:')->prompt(),
            'password' => $this->climate->password('Password:')->prompt(),
            'balance_currency' => $this->climate->radio('Currency:', [
                'EUR' => '€',
                'USD' => '$',
            ])->prompt(),
            'balance_amount' => $this->climate->input('Balance:')->prompt(),
        ];

        $this->climate->br();

        if ($this->climate->confirm('Would you like to confirm?')->confirmed()) {
            try {
                $this->userFacade->register(
                    $this->userFacade->generateId()->toString(),
                    $answers['email'],
                    $answers['password'],
                    $this->moneyParser->parse(
                        str_replace(',', '.', $answers['balance_amount']),
                        new Currency($answers['balance_currency'])
                    ),
                );
            } catch (Throwable $e) {
                $this->climate->br();
                $this->climate->to('error')->error($e->getMessage());

                return;
            }
        } else {
            $this->climate->br();
            $this->climate->info('Operation cancelled.');

            return;
        }

        $this->climate->br();
        $this->climate->green('User registered!');
    }

    public function label(): string
    {
        return 'Register my account';
    }
}
