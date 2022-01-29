<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\CoreBundle\whatwedoCoreBundle;
use whatwedo\SearchBundle\Manager\FilterManager;
use whatwedo\SearchBundle\Manager\SearchManager;
use whatwedo\SearchBundle\Tests\Fixtures\Repository\CompanyRepository;
use whatwedo\SearchBundle\Tests\Fixtures\Repository\ContactRepository;
use whatwedo\SearchBundle\whatwedoSearchBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new DoctrineBundle(),
            new TwigBundle(),
            new FrameworkBundle(),
            new ZenstruckFoundryBundle(),
            new whatwedoCoreBundle(),
            new whatwedoSearchBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $registerClasses = [
            ContactRepository::class,
            CompanyRepository::class,
            ContactRepository::class,
            SearchManager::class,
            FilterManager::class,
            //     FormatterManager::class,
        ];

        foreach ($registerClasses as $registerClass) {
            $containerBuilder->register($registerClass)
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->setPublic(true);
        }

        $containerBuilder->register(FormatterManager::class)
            ->addArgument(tagged_iterator('whatwedo_core.formatter'));

        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'S3CRET',
            'test' => true,
            'default_locale' => 'en',
            'translator' => [
                'default_path' => '%kernel.project_dir%/translations',
            ],
        ]);

        $containerBuilder->loadFromExtension(
            'doctrine',
            [
                'dbal' => [
                    'url' => '%env(resolve:DATABASE_URL)%',
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'auto_mapping' => true,
                    'mappings' => [
                        'Test' => [
                            'is_bundle' => false,
                            'type' => 'annotation',
                            'dir' => '%kernel.project_dir%/tests/Fixtures/Entity',
                            'prefix' => 'whatwedo\SearchBundle\Tests\Fixtures\Entity',
                            'alias' => 'Test',
                        ],
                    ],
                ],
            ]
        );

        $containerBuilder->loadFromExtension(
            'zenstruck_foundry',
            [
                'auto_refresh_proxies' => true,
            ]
        );
    }
}
