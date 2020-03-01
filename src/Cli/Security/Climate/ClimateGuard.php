<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Security\Climate;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Output\Question\PasswordInput;
use BudgetCalculator\Cli\Output\Question\TextInput;
use BudgetCalculator\Cli\Security\Authenticator;
use BudgetCalculator\Cli\Security\Guard;
use BudgetCalculator\Cli\Security\UserProvider;
use RuntimeException;

class ClimateGuard implements Guard
{
    private Cli $cli;
    private Authenticator $authenticator;
    private UserProvider $userProvider;

    public function __construct(
        Cli $cli,
        Authenticator $authenticator,
        UserProvider $userProvider
    ) {
        $this->cli = $cli;
        $this->authenticator = $authenticator;
        $this->userProvider = $userProvider;
    }

    public function authenticate(): void
    {
        $this->cli->lineBreak();

        $credentials = [
            'email' => $this->cli->prompt(new TextInput('email', 'Email:')),
            'password' => $this->cli->prompt(new PasswordInput('password', 'Password:')),
        ];

        if (false === $this->authenticator->checkCredentials($credentials)) {
            $this->cli->lineBreak();
            $this->cli->outputError('Bad credential.');

            exit(1);
        }

        $user = $this->authenticator->getUserByCredentials($credentials);

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $this->userProvider->setUser($user);
    }
}
