<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Transaction;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\TransactionFacade;
use BudgetCalculator\Model\Transaction\Type;
use League\CLImate\CLImate;
use Money\Currency;
use Money\MoneyParser;
use Throwable;

class AddTransactionCommand implements Command, AuthenticationRequired
{
    use FormatterHelper;

    private Climate $climate;
    private MoneyParser $moneyParser;
    private TransactionFacade $transactionFacade;
    private UserProvider $userProvider;

    public function __construct(
        Climate $climate,
        MoneyParser $moneyParser,
        TransactionFacade $transactionFacade,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
        $this->moneyParser = $moneyParser;
        $this->transactionFacade = $transactionFacade;
        $this->userProvider = $userProvider;
    }

    public function name(): string
    {
        return 'transaction:add';
    }

    public function execute(): void
    {
        $this->climate->br();

        $answers = [
            'label' => $this->climate->input('What is the label?')->prompt(),
            'currency' => $this->climate->radio('What is the currency?', [
                'EUR' => '€',
                'USD' => '$',
            ])->prompt(),
            'amount' => $this->climate->input('What is the amount?')->prompt(),
            'type' => $this->climate->radio('What kind of transaction is it?', [
                Type::debit()->toString() => 'debit',
                Type::credit()->toString() => 'credit',
            ])->prompt(),
            'date' => $this->climate->input('What is the date of your transaction? Format: MM-DD-YYYY')->prompt()
        ];

        $this->climate->br();

        if ($this->climate->confirm('Would you like to confirm?')->confirmed()) {
            try {
                $this->transactionFacade->add(
                    $this->transactionFacade->generateId()->toString(),
                    $this->userProvider->getUser()->id(),
                    $answers['label'],
                    $this->moneyParser->parse(
                        $this->replaceInString(',', '.', $answers['amount']),
                        new Currency($answers['currency'])
                    ),
                    $answers['type'],
                    $this->formatDate($answers['date'], 'm-d-Y', 'Y-m-d'),
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
        $this->climate->green('Transaction saved!');
    }

    public function label(): string
    {
        return 'Add a new transaction';
    }
}
