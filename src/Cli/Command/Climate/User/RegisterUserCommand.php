<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\User;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Output\Question\PasswordInput;
use BudgetCalculator\Cli\Output\Question\RadioInput;
use BudgetCalculator\Cli\Output\Question\TextInput;
use BudgetCalculator\Facade\UserFacade;
use Money\Currency;
use Money\MoneyParser;
use Throwable;

class RegisterUserCommand implements Command
{
    public const NAME = 'user:register';

    private Cli $cli;
    private MoneyParser $moneyParser;
    private UserFacade $userFacade;

    public function __construct(
        Cli $cli,
        MoneyParser $moneyParser,
        UserFacade $userFacade
    ) {
        $this->cli = $cli;
        $this->moneyParser = $moneyParser;
        $this->userFacade = $userFacade;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function execute(): void
    {
        $this->cli->lineBreak();

        $answers = [
            'email' => $this->cli->prompt(new TextInput('email', 'Email:')),
            'password' => $this->cli->prompt(new PasswordInput('password', 'Password:')),
            'balance_currency' => $this->cli->prompt(new RadioInput('balance_currency', 'Currency:', [
                'EUR' => '€',
                'USD' => '$',
            ])),
            'balance_amount' => $this->cli->prompt(new TextInput('balance_amount', 'Balance:')),
        ];

        $this->cli->lineBreak();

        if ($this->cli->confirm('Would you like to confirm?')) {
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
                $this->cli->lineBreak();
                $this->cli->outputError($e->getMessage());

                return;
            }
        } else {
            $this->cli->lineBreak();
            $this->cli->outputInfo('Operation cancelled.');

            return;
        }

        $this->cli->lineBreak();
        $this->cli->output('User registered!', 'green');
    }

    public function label(): string
    {
        return 'Register my account';
    }
}
