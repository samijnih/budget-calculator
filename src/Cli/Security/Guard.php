<?php

declare(strict_types=1);

namespace App\Cli\Security;

use App\Security\Authenticator;
use League\CLImate\CLImate;
use RuntimeException;

class Guard
{
    private CLImate $climate;
    private Authenticator $authenticator;
    private UserProvider $userProvider;

    public function __construct(
        CLImate $climate,
        Authenticator $authenticator,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
        $this->authenticator = $authenticator;
        $this->userProvider = $userProvider;
    }

    public function authenticate(): void
    {
        $this->climate->br();
        $this->climate->whisper('You must authenticate first.');
        $this->climate->br();

        $credentials = [
            'email' => $this->climate->input('Email:')->prompt(),
            'password' => $this->climate->password('Password:')->prompt(),
        ];

        if (false === $this->authenticator->checkCredentials($credentials)) {
            $this->climate->br();
            $this->climate->to('error')->error('Bad credential.');

            exit(1);
        }

        $user = $this->authenticator->getUser($credentials);

        if ($user === null) {
            throw new RuntimeException('User not found.');
        }

        $this->userProvider->setUser($user);
    }
}
