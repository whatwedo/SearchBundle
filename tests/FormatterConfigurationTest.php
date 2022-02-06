<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use whatwedo\SearchBundle\DependencyInjection\Configuration;

class FormatterConfigurationTest extends KernelTestCase
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

        $this->assertIsArray($processedConfiguration);
    }

}
