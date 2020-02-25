<?php

declare(strict_types=1);

namespace App\Cli\User;

use App\Cli\Command;
use App\Cli\Security\CommandNeedsAuthentication;
use App\Cli\Security\UserProvider;
use App\Facade\TransactionFacade;
use App\Facade\UserFacade;
use League\CLImate\CLImate;
use Throwable;

class DeleteUserCommand implements Command, CommandNeedsAuthentication
{
    private Climate $climate;
    private UserFacade $userFacade;
    private UserProvider $userProvider;
    private TransactionFacade $transactionFacade;

    public function __construct(
        Climate $climate,
        UserFacade $userFacade,
        UserProvider $userProvider,
        TransactionFacade $transactionFacade
    ) {
        $this->climate = $climate;
        $this->userFacade = $userFacade;
        $this->userProvider = $userProvider;
        $this->transactionFacade = $transactionFacade;
    }

    public function name(): string
    {
        return 'user:delete';
    }

    public function execute(): void
    {
        $confirmation = $this->climate->confirm('Are you sure you want to delete your account?');

        $this->climate->br();

        if ($confirmation->confirmed()) {
            try {
                $userId = $this->userProvider->getUser()->id();

                $this->transactionFacade->deleteForUser($userId);
                $this->userFacade->delete($userId);
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
        $this->climate->green('Your account has been removed!');
    }

    public function label(): string
    {
        return 'Delete my account';
    }
}
