<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Budget;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\BudgetFacade;
use Money\Money;
use Money\MoneyFormatter;

class DisplayBudgetsCommand implements Command, AuthenticationRequired
{
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
        return 'budget:display';
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

        $this->cli->table($formatter->transform([
            'name',
            'amount',
            'priority',
        ]));
    }

    public function label(): string
    {
        return 'Display my budgets';
    }
}
