<?php

declare(strict_types=1);

use BudgetCalculator\Facade\DatabaseFacade;
use BudgetCalculator\Service\Clock;
use Doctrine\DBAL\Connection;
use Money\Currencies;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\MoneyFormatter;
use Money\MoneyParser;
use Money\Parser\DecimalMoneyParser;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\UuidFactory;
use function DI\autowire;
use function DI\factory;

return [
    Connection::class => factory(static fn () => DatabaseFacade::getConnectionFromUrl($_ENV['DB_URL'])),
    UuidFactory::class => autowire()->lazy(),
    Clock::class => autowire()->lazy(),
    Currencies::class => factory(static fn (): Currencies => new ISOCurrencies()),
    MoneyParser::class => factory(static fn (ContainerInterface $container): MoneyParser => new DecimalMoneyParser($container->get(Currencies::class))),
    MoneyFormatter::class => factory(static function () {
        $currencies = new ISOCurrencies();
        $usdFormatter = new IntlMoneyFormatter(new NumberFormatter('en_US', NumberFormatter::CURRENCY), $currencies);
        $euroFormatter = new IntlMoneyFormatter(new NumberFormatter('fr_FR', NumberFormatter::CURRENCY), $currencies);

        return new AggregateMoneyFormatter([
            'USD' => $usdFormatter,
            'EUR' => $euroFormatter,
        ]);
    }),
];
