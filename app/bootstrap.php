<?php

declare(strict_types=1);

use BudgetCalculator\Cli\CliBudgetCalculator;
use BudgetCalculator\Cli\Command\Transaction\AddTransactionCommand;
use BudgetCalculator\Cli\Command\Transaction\DisplayTransactionsCommand;
use BudgetCalculator\Cli\Command\User\DeleteUserCommand;
use BudgetCalculator\Cli\Command\User\RegisterUserCommand;
use BudgetCalculator\Cli\Security\Guard;
use BudgetCalculator\Cli\Security\UserProvider;
use BudgetCalculator\Facade\DatabaseFacade;
use BudgetCalculator\Facade\TransactionFacade;
use BudgetCalculator\Facade\UserFacade;
use BudgetCalculator\EntityRepository\TransactionRepository;
use BudgetCalculator\EntityRepository\UserRepository;
use BudgetCalculator\ReadModelRepository\TransactionRepository as TransactionReadModelRepository;
use BudgetCalculator\ReadModelRepository\UserRepository as UserReadModelRepository;
use BudgetCalculator\Cli\Security\Authenticator;
use BudgetCalculator\Service\Clock;
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
    CliBudgetCalculator::class => DI\factory(static function (ContainerInterface $container) {
        $app = new CliBudgetCalculator($container->get(CLImate::class), $container->get(Guard::class));

        $app->registerCommand($container->get(RegisterUserCommand::class));
        $app->registerCommand($container->get(DeleteUserCommand::class));
        $app->registerCommand($container->get(AddTransactionCommand::class));
        $app->registerCommand($container->get(DisplayTransactionsCommand::class));

        return $app;
    }),

    # Facades
    UserFacade::class => DI\autowire(),
    TransactionFacade::class => DI\autowire(),
]);

return $containerBuilder->build();
