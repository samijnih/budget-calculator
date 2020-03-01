<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Transaction;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Output\Question\CheckboxesInput;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\TransactionFacade;
use Money\Money;
use Money\MoneyFormatter;
use Throwable;

class DeleteTransactionsCommand implements Command, AuthenticationRequired
{
    private Cli $cli;
    private MoneyFormatter $moneyFormatter;
    private TransactionFacade $transactionFacade;
    private UserProvider $userProvider;

    public function __construct(
        Cli $cli,
        MoneyFormatter $moneyFormatter,
        TransactionFacade $transactionFacade,
        UserProvider $userProvider
    ) {
        $this->cli = $cli;
        $this->moneyFormatter = $moneyFormatter;
        $this->transactionFacade = $transactionFacade;
        $this->userProvider = $userProvider;
    }

    public function name(): string
    {
        return 'transaction:delete';
    }

    public function execute(): void
    {
        $this->cli->lineBreak();

        $transactions = $this->transactionFacade->listForUser($this->userProvider->getUser()->id());

        if (empty($transactions)) {
            $this->cli->outputInfo('You do not have any transaction. Please add some.');

            return;
        }

        $formatter = new CliTransformer($transactions);
        $formatter->addDecorator('amount', fn (Money $amount): string => $this->moneyFormatter->format($amount));

        $transactions = $formatter->transform([
            'id',
            'label',
            'amount',
            'type',
            'date',
        ]);

        $options = [];
        foreach ($transactions as $transaction) {
            $options[$transaction['id']] = sprintf('%s: %s of %s on %s',
                $transaction['type'],
                $transaction['label'],
                $transaction['amount'],
                $transaction['date'],
            );
        }

        $transactions = $this->cli->prompt(new CheckboxesInput('transactions', 'Select one or more transaction:', $options));

        $this->cli->lineBreak();

        if ($this->cli->confirm('Would you like to confirm?')) {
            try {
                $this->transactionFacade->deleteMany($transactions);
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
        $this->cli->output('Transactions deleted!', 'green');
    }

    public function label(): string
    {
        return 'Delete my transactions';
    }
}
