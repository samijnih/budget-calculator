<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Transaction;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\Facade\TransactionFacade;
use Money\Money;
use Money\MoneyFormatter;

class DisplayTransactionsCommand implements Command, AuthenticationRequired
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
        return 'transaction:display';
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

        $this->cli->table($formatter->transform([
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
