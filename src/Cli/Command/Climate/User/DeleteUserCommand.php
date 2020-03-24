<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\User;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\BudgetFacade;
use BudgetCalculator\Facade\TransactionFacade;
use BudgetCalculator\Facade\UserFacade;
use Throwable;

class DeleteUserCommand implements Command, AuthenticationRequired
{
    private Cli $cli;
    private UserFacade $userFacade;
    private UserProvider $userProvider;
    private TransactionFacade $transactionFacade;
    private BudgetFacade $budgetFacade;

    public function __construct(
        Cli $cli,
        UserFacade $userFacade,
        UserProvider $userProvider,
        TransactionFacade $transactionFacade,
        BudgetFacade $budgetFacade
    ) {
        $this->cli = $cli;
        $this->userFacade = $userFacade;
        $this->userProvider = $userProvider;
        $this->transactionFacade = $transactionFacade;
        $this->budgetFacade = $budgetFacade;
    }

    public function name(): string
    {
        return 'user:delete';
    }

    public function execute(): void
    {
        $this->cli->lineBreak();

        $confirmed = $this->cli->confirm('Are you sure you want to delete your account?');

        if ($confirmed) {
            try {
                $userId = $this->userProvider->getUser()->id();

                $this->budgetFacade->deleteForUser($userId);
                $this->transactionFacade->deleteForUser($userId);
                $this->userFacade->delete($userId);
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
        $this->cli->output('Your account has been removed!', 'green');
    }

    public function label(): string
    {
        return 'Delete my account';
    }
}
