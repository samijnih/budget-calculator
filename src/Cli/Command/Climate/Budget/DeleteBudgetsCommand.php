<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Budget;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Output\Question\CheckboxesInput;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\BudgetFacade;
use Money\Money;
use Money\MoneyFormatter;
use Throwable;

class DeleteBudgetsCommand implements Command, AuthenticationRequired
{
    use FormatterHelper;

    private Cli $cli;
    private MoneyFormatter $moneyFormatter;
    private BudgetFacade $budgetFacade;
    private UserProvider $userProvider;

    public function __construct(
        Cli $cli,
        MoneyFormatter $moneyFormatter,
        BudgetFacade $budgetFacade,
        UserProvider $userProvider
    ) {
        $this->cli = $cli;
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
        $this->cli->lineBreak();

        $budgets = $this->budgetFacade->listForUser($this->userProvider->getUser()->id());

        if (empty($budgets)) {
            $this->cli->outputInfo('You do not have any budget. Please add some.');

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

        $budgets = $this->cli->prompt(new CheckboxesInput('budgets', 'Select one or more budget:', $options));

        $this->cli->lineBreak();

        if ($this->cli->confirm('Would you like to confirm?')) {
            try {
                $this->budgetFacade->deleteMany($budgets);
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
        $this->cli->output('Budget deleted!', 'green');
    }

    public function label(): string
    {
        return 'Delete my budgets';
    }
}
