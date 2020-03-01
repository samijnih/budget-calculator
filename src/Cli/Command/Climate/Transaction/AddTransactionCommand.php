<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Transaction;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Output\Question\RadioInput;
use BudgetCalculator\Cli\Output\Question\TextInput;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\TransactionFacade;
use BudgetCalculator\Model\DateFormatter;
use BudgetCalculator\Model\Transaction\Type;
use DateTime;
use Money\Currency;
use Money\MoneyParser;
use Throwable;

class AddTransactionCommand implements Command, AuthenticationRequired
{
    use FormatterHelper, DateFormatter;

    private Cli $cli;
    private MoneyParser $moneyParser;
    private TransactionFacade $transactionFacade;
    private UserProvider $userProvider;

    public function __construct(
        Cli $cli,
        MoneyParser $moneyParser,
        TransactionFacade $transactionFacade,
        UserProvider $userProvider
    ) {
        $this->cli = $cli;
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
        $this->cli->lineBreak();

        $answers = [
            'label' => $this->cli->prompt(new TextInput('label', 'What is the label?')),
            'currency' => $this->cli->prompt(new RadioInput('currency', 'What is the currency?', [
                'EUR' => '€',
                'USD' => '$',
            ])),
            'amount' => $this->cli->prompt(new TextInput('amount', 'What is the amount?')),
            'type' => $this->cli->prompt(new RadioInput('type', 'What kind of transaction is it?', [
                Type::debit()->toString() => 'debit',
                Type::credit()->toString() => 'credit',
            ])),
            'date' => $this->cli->prompt(new TextInput(
                'date',
                'What is the date of your transaction? Format: MM-DD-YYYY',
                [],
                $this->format(new DateTime(), 'm-d-Y')
            )),
        ];

        $this->cli->lineBreak();

        if ($this->cli->confirm('Would you like to confirm?')) {
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
        $this->cli->output('Transaction saved!', 'green');
    }

    public function label(): string
    {
        return 'Add a new transaction';
    }
}
