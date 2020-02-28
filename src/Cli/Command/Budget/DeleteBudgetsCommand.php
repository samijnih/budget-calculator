<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Budget;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\BudgetFacade;
use League\CLImate\CLImate;
use Money\Money;
use Money\MoneyFormatter;
use Throwable;

class DeleteBudgetsCommand implements Command, AuthenticationRequired
{
    use FormatterHelper;

    private Climate $climate;
    private MoneyFormatter $moneyFormatter;
    private BudgetFacade $budgetFacade;
    private UserProvider $userProvider;

    public function __construct(
        Climate $climate,
        MoneyFormatter $moneyFormatter,
        BudgetFacade $budgetFacade,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
        $this->moneyFormatter = $moneyFormatter;
        $this->budgetFacade = $budgetFacade;
        $this->userProvider = $userProvider;
    }

    public function name(): string
    {
        return 'budget:delete';
    }

    public function execute(): void
    {
        $this->climate->br();

        $budgets = $this->budgetFacade->listForUser($this->userProvider->getUser()->id());

        if (empty($budgets)) {
            $this->climate->info('You do not have any budget. Please add some.');

            return;
        }

        $formatter = new CliTransformer($budgets);
        $formatter->addDecorator('amount', fn (Money $amount): string => $this->moneyFormatter->format($amount));

        $budgets = $formatter->transform([
            'id',
            'name',
            'amount',
            'priority',
        ]);

        $options = [];
        foreach ($budgets as $budget) {
            $options[$budget['id']] = sprintf('Priority %s: %s of %s',
                $budget['priority'],
                $budget['name'],
                $budget['amount'],
            );
        }

        $budgets = $this->climate->checkboxes('Select one or more budget:', $options)->prompt();

        $this->climate->br();

        if ($this->climate->confirm('Would you like to confirm?')->confirmed()) {
            try {
                $this->budgetFacade->deleteMany($budgets);
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
        $this->climate->green('Budget deleted!');
    }

    public function label(): string
    {
        return 'Delete my budgets';
    }
}
