<?php

declare(strict_types=1);

use App\Cli\Budget\AddBudgetCommand;
use App\Cli\Security\Guard;
use App\Cli\Security\UserProvider;
use App\Cli\Solde;
use App\Cli\Transaction\AddTransactionCommand;
use App\Cli\Transaction\DeleteTransactionsCommand;
use App\Cli\Transaction\DisplayTransactionsCommand;
use App\Cli\Transaction\EditTransactionsCommand;
use App\Cli\User\DeleteUserCommand;
use App\Cli\User\RegisterUserCommand;
use App\Facade\DatabaseFacade;
use App\Facade\TransactionFacade;
use App\Facade\UserFacade;
use App\Model\Transaction\TransactionRepository;
use App\Model\User\UserRepository;
use App\ReadModel\Transaction\TransactionRepository as TransactionReadModelRepository;
use App\ReadModel\User\UserRepository as UserReadModelRepository;
use App\Security\Authenticator;
use App\Service\Clock;
use DI\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Dotenv\Dotenv;
use League\CLImate\CLImate;
use Money\Currencies;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\MoneyFormatter;
use Money\MoneyParser;
use Money\Parser\DecimalMoneyParser;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\UuidFactory;
use Symfony\Component\ErrorHandler\Debug;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();
$dotenv->required('PROJECT_DIR')->notEmpty();

$errorHandler = Debug::enable();

$containerBuilder = new ContainerBuilder();
$containerBuilder->enableCompilation($_ENV['VAR_DIR'].'/cache');
$containerBuilder->useAnnotations(false);
$containerBuilder->useAutowiring(true);
$containerBuilder->addDefinitions([
    # Common
    CLImate::class => DI\autowire()->lazy(),
    Connection::class => DI\factory(static fn () => DatabaseFacade::getConnectionFromUrl($_ENV['DB_URL'])),
    UuidFactory::class => DI\autowire()->lazy(),
    Clock::class => DI\autowire()->lazy(),
    Currencies::class => DI\factory(static fn (): Currencies => new ISOCurrencies()),
    MoneyParser::class => DI\factory(static fn (ContainerInterface $container): MoneyParser => new DecimalMoneyParser($container->get(Currencies::class))),
    MoneyFormatter::class => DI\factory(static function () {
        $currencies = new ISOCurrencies();
        $usdFormatter = new IntlMoneyFormatter(new NumberFormatter('en_US', NumberFormatter::CURRENCY), $currencies);
        $euroFormatter = new IntlMoneyFormatter(new NumberFormatter('fr_FR', NumberFormatter::CURRENCY), $currencies);

        return new AggregateMoneyFormatter([
            'USD' => $usdFormatter,
            'EUR' => $euroFormatter,
        ]);
    }),

    # Read Model Repositories
    UserReadModelRepository::class => DI\autowire()->lazy(),
    TransactionReadModelRepository::class => DI\autowire()->lazy(),

    # Write Model Repositories
    UserRepository::class => DI\autowire()->lazy(),
    TransactionRepository::class => DI\autowire()->lazy(),

    # Security
    Guard::class => DI\autowire()->lazy(),
    UserProvider::class => DI\autowire()->lazy(),
    Authenticator::class => DI\factory(static fn (ContainerInterface $container) => $container->get(UserFacade::class)),

    # Command
    RegisterUserCommand::class => DI\autowire()->lazy(),
    DeleteUserCommand::class => DI\autowire()->lazy(),
    AddTransactionCommand::class => DI\autowire()->lazy(),
    DisplayTransactionsCommand::class => DI\autowire()->lazy(),
    EditTransactionsCommand::class => DI\autowire()->lazy(),
    DeleteTransactionsCommand::class => DI\autowire()->lazy(),
    AddBudgetCommand::class => DI\autowire()->lazy(),
    Solde::class => DI\factory(static function (ContainerInterface $container) {
        $app = new Solde($container->get(CLImate::class), $container->get(Guard::class));

        $app->registerCommand($container->get(RegisterUserCommand::class));
        $app->registerCommand($container->get(DeleteUserCommand::class));
        $app->registerCommand($container->get(AddTransactionCommand::class));
        $app->registerCommand($container->get(DisplayTransactionsCommand::class));
        $app->registerCommand($container->get(EditTransactionsCommand::class));
        $app->registerCommand($container->get(DeleteTransactionsCommand::class));
        $app->registerCommand($container->get(AddBudgetCommand::class));

        return $app;
    }),

    # Facades
    UserFacade::class => DI\autowire(),
    TransactionFacade::class => DI\autowire(),
]);

return $containerBuilder->build();
