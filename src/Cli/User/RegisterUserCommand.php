<?php

declare(strict_types=1);

namespace App\Cli\User;

use App\Cli\Command;
use App\Facade\UserFacade;
use League\CLImate\CLImate;
use Money\Currency;
use Money\Parser\DecimalMoneyParser;
use Throwable;

class RegisterUserCommand implements Command
{
    public const NAME = 'user:register';

    private Climate $climate;
    private DecimalMoneyParser $decimalMoneyParser;
    private UserFacade $userFacade;

    public function __construct(
        Climate $climate,
        DecimalMoneyParser $decimalMoneyParser,
        UserFacade $userFacade
    ) {
        $this->climate = $climate;
        $this->decimalMoneyParser = $decimalMoneyParser;
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
                    $this->decimalMoneyParser->parse(
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
