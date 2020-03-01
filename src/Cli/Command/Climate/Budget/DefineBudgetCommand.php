<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Budget;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Output\Question\RadioInput;
use BudgetCalculator\Cli\Output\Question\TextInput;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\BudgetFacade;
use Money\Currency;
use Money\MoneyParser;
use Throwable;

class DefineBudgetCommand implements Command, AuthenticationRequired
{
    use FormatterHelper;

    private Cli $cli;
    private MoneyParser $moneyParser;
    private BudgetFacade $budgetFacade;
    private UserProvider $userProvider;

    public function __construct(
        Cli $cli,
        MoneyParser $moneyParser,
        BudgetFacade $budgetFacade,
        UserProvider $userProvider
    ) {
        $this->cli = $cli;
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
        $this->cli->lineBreak();

        $answers = [
            'name' => $this->cli->prompt(new TextInput('name', 'What is the name?')),
            'currency' => $this->cli->prompt(new RadioInput('currency', 'What is the currency?', [
                'EUR' => '€',
                'USD' => '$',
            ])),
            'amount' => $this->cli->prompt(new TextInput('amount', 'What is the amount?')),
            'priority' => $this->cli->prompt(new TextInput('priority', 'What is the priority? From 1 to 10.')),
        ];

        $this->cli->lineBreak();

        if ($this->cli->confirm('Would you like to confirm?')) {
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
        $this->cli->output('Budget defined!', 'green');
    }

    public function label(): string
    {
        return 'Define a new budget';
    }
}
