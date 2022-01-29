<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Helper;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Zenstruck\Foundry\ChainManagerRegistry;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use function trigger_deprecation;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestState
{
    /**
     * @var callable|null
     */
    private static $instantiator;

    /**
     * @var \Faker\Generator|null
     */
    private static $faker;

    /**
     * @var bool|null
     */
    private static $defaultProxyAutoRefresh;

    /**
     * @var callable[]
     */
    private static $globalStates = [];

    public static function setInstantiator(callable $instantiator): void
    {
        self::$instantiator = $instantiator;
    }

    public static function enableDefaultProxyAutoRefresh(): void
    {
        self::$defaultProxyAutoRefresh = true;
    }

    /**
     * @deprecated Use TestState::enableDefaultProxyAutoRefresh()
     */
    public static function alwaysAutoRefreshProxies(): void
    {
        trigger_deprecation('zenstruck\foundry', '1.9', 'TestState::alwaysAutoRefreshProxies() is deprecated, use TestState::enableDefaultProxyAutoRefresh().');

        self::enableDefaultProxyAutoRefresh();
    }

    public static function bootFoundry(?Configuration $configuration = null): void
    {
        $configuration = $configuration ?? new Configuration();

        if (self::$instantiator) {
            $configuration->setInstantiator(self::$instantiator);
        }

        if (self::$faker) {
            $configuration->setFaker(self::$faker);
        }

        if (self::$defaultProxyAutoRefresh === true) {
            $configuration->enableDefaultProxyAutoRefresh();
        } elseif (self::$defaultProxyAutoRefresh === false) {
            $configuration->disableDefaultProxyAutoRefresh();
        }

        Factory::boot($configuration);
    }

    /**
     * @internal
     */
    public static function bootFromContainer(ContainerInterface $container): void
    {
        if ($container->has(Configuration::class)) {
            self::bootFoundry($container->get(Configuration::class));

            return;
        }

        $configuration = new Configuration();

        try {
            $configuration->setManagerRegistry(self::initializeChainManagerRegistry($container));
        } catch (NotFoundExceptionInterface $e) {
            throw new \LogicException('Could not boot Foundry, is the DoctrineBundle installed/configured?', 0, $e);
        }

        self::bootFoundry($configuration);
    }

    /**
     * @internal
     */
    public static function initializeChainManagerRegistry(ContainerInterface $container): ChainManagerRegistry
    {
        /** @var ManagerRegistry $managerRegistries */
        $managerRegistries = [];

        if ($container->has('doctrine')) {
            $managerRegistries[] = $container->get('doctrine');
        }

        if (\count($managerRegistries) === 0) {
            throw new \LogicException('Neither doctrine/orm nor doctrine/mongodb-odm are present.');
        }

        return new ChainManagerRegistry($managerRegistries);
    }

    /**
     * @internal
     */
    public static function flushGlobalState(): void
    {
        foreach (self::$globalStates as $callback) {
            $callback();
        }
    }
}
