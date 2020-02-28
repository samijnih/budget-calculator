<?php

declare(strict_types=1);

namespace App\Cli\Transaction;

use App\Cli\Command;
use App\Cli\Helper\FormatterHelper;
use App\Cli\Security\CommandNeedsAuthentication;
use App\Cli\Security\UserProvider;
use App\Cli\Transformer\CliTransformer;
use App\Facade\TransactionFacade;
use App\Helper\MoneyHelper;
use App\Model\Date;
use App\Model\Transaction\TransactionRepository;
use App\Model\Transaction\Type;
use App\Service\Clock;
use League\CLImate\CLImate;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;
use Ramsey\Uuid\Uuid;
use Throwable;

class EditTransactionsCommand implements Command, CommandNeedsAuthentication
{
    use FormatterHelper, MoneyHelper;

    private Climate $climate;
    private MoneyFormatter $moneyFormatter;
    private MoneyParser $moneyParser;
    private TransactionFacade $transactionFacade;
    private TransactionRepository $transactionRepository;
    private UserProvider $userProvider;
    private Clock $clock;

    public function __construct(
        Climate $climate,
        MoneyFormatter $moneyFormatter,
        MoneyParser $moneyParser,
        TransactionFacade $transactionFacade,
        TransactionRepository $transactionRepository,
        UserProvider $userProvider,
        Clock $clock
    ) {
        $this->climate = $climate;
        $this->moneyFormatter = $moneyFormatter;
        $this->moneyParser = $moneyParser;
        $this->transactionFacade = $transactionFacade;
        $this->transactionRepository = $transactionRepository;
        $this->userProvider = $userProvider;
        $this->clock = $clock;
    }

    public function name(): string
    {
        return 'transaction:edit';
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
        $formatter->addDecorator('label', fn (string $label): string => ucfirst($label));
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

        $transactionId = $this->climate->radio('Select a transaction:', $options)->prompt();

        $this->climate->br();

        $fields = $this->climate->checkboxes(
            'What do you want to edit?', [
            'label',
            'amount',
            'type',
            'date',
        ])->prompt();
        $fields = array_combine($fields, array_fill(0, count($fields), null));

        $questions = [
            'label' => function (): string {
                return $this->climate->input('What is the label?')->prompt();
            },
            'amount' => function (): Money {
                $currency = $this->climate->radio('What is the currency?', [
                    'EUR' => '€',
                    'USD' => '$',
                ])->prompt();

                $this->climate->br();

                $amount = $this->replaceInString(
                    ',',
                    '.',
                    $this->climate->input('What is the amount?')->prompt()
                );

                return $this->moneyParser->parse($amount, new Currency($currency));
            },
            'type' => function (): string {
                return $this->climate->radio('What kind of transaction is it?', [
                    Type::debit()->toString() => 'debit',
                    Type::credit()->toString() => 'credit',
                ])->prompt();
            },
            'date' => function (): string {
                $date = $this->climate->input('What is the date of your transaction? Format: MM-DD-YYYY')->prompt();

                return $this->formatDate($date, 'm-d-Y', 'Y-m-d');
            },
        ];

        $transaction = $this->transactionRepository->find(Uuid::fromString($transactionId));
        $updatedAt = $this->clock->mutableNow();

        foreach (array_intersect_key($questions, $fields) as $key => $question) {
            $answer = $question();

            switch ($key) {
                case 'label':
                    $transaction->labeled($answer, $updatedAt);
                    break;
                case 'amount':
                    $transaction->ofAmount($answer, $updatedAt);
                    break;
                case 'type':
                    $transaction->ofType(Type::fromString($answer), $updatedAt);
                    break;
                case 'date':
                    $transaction->onDate(new Date($answer), $updatedAt);
                    break;
            }
        }

        $this->climate->br();

        if ($this->climate->confirm('Would you like to confirm?')->confirmed()) {
            try {
                $this->transactionRepository->update($transaction);
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
        $this->climate->green('Transaction edited!');
    }

    public function label(): string
    {
        return 'Edit a transaction';
    }
}
