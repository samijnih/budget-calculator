<?php

declare(strict_types=1);

namespace BudgetCalculator\Cli\Command\Climate\Transaction;

use BudgetCalculator\Cli\Adapter\Cli;
use BudgetCalculator\Cli\Command\Command;
use BudgetCalculator\Cli\Helper\FormatterHelper;
use BudgetCalculator\Cli\Output\Question\CheckboxesInput;
use BudgetCalculator\Cli\Output\Question\RadioInput;
use BudgetCalculator\Cli\Output\Question\TextInput;
use BudgetCalculator\Cli\Security\AuthenticationRequired;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Cli\Transformer\CliTransformer;
use BudgetCalculator\EntityRepository\TransactionRepository;
use BudgetCalculator\Facade\TransactionFacade;
use BudgetCalculator\Helper\MoneyHelper;
use BudgetCalculator\Model\Date;
use BudgetCalculator\Model\DateFormatter;
use BudgetCalculator\Model\Transaction\Type;
use BudgetCalculator\Service\Clock;
use DateTime;
use Money\Currency;
use Money\Money;
use Money\MoneyFormatter;
use Money\MoneyParser;
use Ramsey\Uuid\Uuid;
use Throwable;

class EditTransactionsCommand implements Command, AuthenticationRequired
{
    use FormatterHelper, DateFormatter, MoneyHelper;

    private Cli $cli;
    private MoneyFormatter $moneyFormatter;
    private MoneyParser $moneyParser;
    private TransactionFacade $transactionFacade;
    private TransactionRepository $transactionRepository;
    private UserProvider $userProvider;
    private Clock $clock;

    public function __construct(
        Cli $cli,
        MoneyFormatter $moneyFormatter,
        MoneyParser $moneyParser,
        TransactionFacade $transactionFacade,
        TransactionRepository $transactionRepository,
        UserProvider $userProvider,
        Clock $clock
    ) {
        $this->cli = $cli;
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
        $this->cli->lineBreak();

        $transactions = $this->transactionFacade->listForUser($this->userProvider->getUser()->id());

        if (empty($transactions)) {
            $this->cli->outputInfo('You do not have any transaction. Please add some.');

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

        $transactionId = $this->cli->prompt(new RadioInput('transaction_id', 'Select a transaction:', $options));

        $this->cli->lineBreak();

        $fields = $this->cli->prompt(new CheckboxesInput('fields', 'What do you want to edit?', [
            'label',
            'amount',
            'type',
            'date',
        ]));
        $fields = array_combine($fields, array_fill(0, count($fields), null));

        $questions = [
            'label' => function (): string {
                return $this->cli->prompt(new TextInput('label', 'What is the label?'));
            },
            'amount' => function (): Money {
                $currency = $this->cli->prompt(new RadioInput('currency', 'What is the currency?', [
                    'EUR' => '€',
                    'USD' => '$',
                ]));

                $this->cli->lineBreak();

                $amount = $this->replaceInString(
                    ',',
                    '.',
                    $this->cli->prompt(new TextInput('amount', 'What is the amount?'))
                );

                return $this->moneyParser->parse($amount, new Currency($currency));
            },
            'type' => function (): string {
                return $this->cli->prompt(new RadioInput('type', 'What kind of transaction is it?', [
                    Type::debit()->toString() => 'debit',
                    Type::credit()->toString() => 'credit',
                ]));
            },
            'date' => function (): string {
                $date = $this->cli->prompt(new TextInput(
                    'date',
                    'What is the date of your transaction? Format: MM-DD-YYYY',
                    [],
                    $this->format(new DateTime(), 'm-d-Y'),
                ));

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

        $this->cli->lineBreak();

        if ($this->cli->confirm('Would you like to confirm?')) {
            try {
                $this->transactionRepository->update($transaction);
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
        $this->cli->output('Transaction edited!', 'green');
    }

    public function label(): string
    {
        return 'Edit a transaction';
    }
}
