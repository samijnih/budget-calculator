<?php

declare(strict_types=1);

namespace App\Cli\Budget;

use App\Cli\Command;
use App\Cli\Helper\FormatterHelper;
use App\Cli\Security\CommandNeedsAuthentication;
use App\Cli\Security\UserProvider;
use App\Facade\BudgetFacade;
use App\Model\Budget\Priority;
use League\CLImate\CLImate;
use Money\Currency;
use Money\Parser\DecimalMoneyParser;
use Throwable;

class AddBudgetCommand implements Command, CommandNeedsAuthentication
{
    use FormatterHelper;

    private Climate $climate;
    private DecimalMoneyParser $decimalMoneyParser;
    private BudgetFacade $budgetFacade;
    private UserProvider $userProvider;

    public function __construct(
        Climate $climate,
        DecimalMoneyParser $decimalMoneyParser,
        BudgetFacade $budgetFacade,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
        $this->decimalMoneyParser = $decimalMoneyParser;
        $this->budgetFacade = $budgetFacade;
        $this->userProvider = $userProvider;
    }

    public function name(): string
    {
        return 'budget:add';
    }

    public function execute(): void
    {
        $this->climate->br();

        $answers = [
            'label' => $this->climate->input('What is the name?')->prompt(),
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
                    $answers['label'],
                    $this->decimalMoneyParser->parse(
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
        $this->climate->green('Budget saved!');
    }

    public function label(): string
    {
        return 'Add a new budget';
    }
}
