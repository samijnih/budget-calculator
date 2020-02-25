<?php

declare(strict_types=1);

namespace App\Cli\Transaction;

use App\Cli\Command;
use App\Cli\Helper\FormatterHelper;
use App\Cli\Security\CommandNeedsAuthentication;
use App\Cli\Security\UserProvider;
use App\Facade\TransactionFacade;
use App\Model\Transaction\Type;
use League\CLImate\CLImate;
use Money\Currency;
use Money\Parser\DecimalMoneyParser;
use Throwable;

class AddTransactionCommand implements Command, CommandNeedsAuthentication
{
    use FormatterHelper;

    private Climate $climate;
    private DecimalMoneyParser $decimalMoneyParser;
    private TransactionFacade $transactionFacade;
    private UserProvider $userProvider;

    public function __construct(
        Climate $climate,
        DecimalMoneyParser $decimalMoneyParser,
        TransactionFacade $transactionFacade,
        UserProvider $userProvider
    ) {
        $this->climate = $climate;
        $this->decimalMoneyParser = $decimalMoneyParser;
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
                    $this->decimalMoneyParser->parse(
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
