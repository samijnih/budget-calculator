<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Budget;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\BudgetFacade;
use League\CLImate\CLImate;
use Money\Money;
use Money\MoneyFormatter;

class DisplayBudgetsCommand implements Command, AuthenticationRequired
{
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
        return 'budget:display';
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

        $this->climate->table($formatter->transform([
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
