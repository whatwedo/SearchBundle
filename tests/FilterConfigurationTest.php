<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use whatwedo\SearchBundle\DependencyInjection\Configuration;
use whatwedo\SearchBundle\Filter\FilterInterface;
use whatwedo\SearchBundle\Manager\FilterManager;

class FilterConfigurationTest extends KernelTestCase
{
    public function testConfig()
    {
        $config = Yaml::parse(
            file_get_contents(__DIR__ . '/resources/config/basic.yaml')
        );

        $processor = new Processor();
        $databaseConfiguration = new Configuration();
        $processedConfiguration = $processor->processConfiguration(
            $databaseConfiguration,
            $config
        );

        self::assertIsArray($processedConfiguration);
    }

    public function testFilterManagerConfig()
    {
        /** @var FilterManager $filterManager */
        $filterManager = self::getContainer()->get(FilterManager::class);

        $config = Yaml::parse(
            file_get_contents(__DIR__ . '/resources/config/basic.yaml')
        );

        $processor = new Processor();
        $databaseConfiguration = new Configuration();
        $processedConfiguration = $processor->processConfiguration(
            $databaseConfiguration,
            $config
        );

        foreach ($processedConfiguration['chains'] as $chain => $filters) {
            foreach ($filters['filters'] as $filterConfig) {
                /** @var FilterInterface $filter */
                $filter = new $filterConfig['class']();
                $filter->setOptions($filterConfig['options']);
                $filterManager->addFilter($filter, $chain);
            }
        }

        self::assertTrue(true);
    }
}
