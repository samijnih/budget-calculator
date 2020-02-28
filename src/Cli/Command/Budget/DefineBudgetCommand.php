<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Budget;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\BudgetFacade;
use League\CLImate\CLImate;
use Money\Currency;
use Money\MoneyParser;
use Throwable;

class DefineBudgetCommand implements Command, AuthenticationRequired
{
    use FormatterHelper;

    private Climate $climate;
    private MoneyParser $moneyParser;
    private BudgetFacade $budgetFacade;
    private UserProvider $userProvider;

    public function __construct(
        Climate $climate,
        MoneyParser $moneyParser,
        BudgetFacade $budgetFacade,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
        $this->moneyParser = $moneyParser;
        $this->budgetFacade = $budgetFacade;
        $this->userProvider = $userProvider;
    }

    public function name(): string
    {
        return 'budget:define';
    }

    public function execute(): void
    {
        $this->climate->br();

        $answers = [
            'name' => $this->climate->input('What is the name?')->prompt(),
            'currency' => $this->climate->radio(
                'What is the currency?', [
                'EUR' => '€',
                'USD' => '$',
            ])->prompt(),
            'amount' => $this->climate->input('What is the amount?')->prompt(),
            'priority' => $this->climate->input('What is the priority? From 1 to 10.')->prompt(),
        ];

        $this->climate->br();

        if ($this->climate->confirm('Would you like to confirm?')->confirmed()) {
            try {
                $this->budgetFacade->add(
                    $this->budgetFacade->generateId()->toString(),
                    $this->userProvider->getUser()->id(),
                    $answers['name'],
                    $this->moneyParser->parse(
                        $this->replaceInString(',', '.', $answers['amount']),
                        new Currency($answers['currency'])
                    ),
                    (int) $answers['priority']
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
        $this->climate->green('Budget defined!');
    }

    public function label(): string
    {
        return 'Define a new budget';
    }
}
