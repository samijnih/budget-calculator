<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Transaction;

use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\TransactionFacade;
use League\CLImate\CLImate;
use Money\Money;
use Money\MoneyFormatter;
use Throwable;

class DeleteTransactionsCommand implements Command, AuthenticationRequired
{
    private Climate $climate;
    private MoneyFormatter $moneyFormatter;
    private TransactionFacade $transactionFacade;
    private UserProvider $userProvider;

    public function __construct(
        Climate $climate,
        MoneyFormatter $moneyFormatter,
        TransactionFacade $transactionFacade,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
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
        $this->climate->br();

        $transactions = $this->transactionFacade->listForUser($this->userProvider->getUser()->id());

        if (empty($transactions)) {
            $this->climate->info('You do not have any transaction. Please add some.');

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

        $transactions = $this->climate->checkboxes('Select one or more transaction:', $options)->prompt();

        $this->climate->br();

        if ($this->climate->confirm('Would you like to confirm?')->confirmed()) {
            try {
                $this->transactionFacade->deleteMany($transactions);
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
        $this->climate->green('Transactions deleted!');
    }

    public function label(): string
    {
        return 'Delete my transactions';
    }
}
