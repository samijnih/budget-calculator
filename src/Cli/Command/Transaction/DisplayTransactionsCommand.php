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

class DisplayTransactionsCommand implements Command, AuthenticationRequired
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
        return 'transaction:display';
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

        $this->climate->table($formatter->transform([
            'label',
            'amount',
            'type',
            'date',
        ]));
    }

    public function label(): string
    {
        return 'Display my transactions';
    }
}
