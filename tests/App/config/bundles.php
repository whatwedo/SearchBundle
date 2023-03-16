<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use whatwedo\CoreBundle\whatwedoCoreBundle;
use whatwedo\SearchBundle\whatwedoSearchBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

return [
    FrameworkBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    TwigBundle::class => [
        'all' => true,
    ],
    ZenstruckFoundryBundle::class => [
        'all' => true,
    ],
    whatwedoCoreBundle::class => [
        'all' => true,
    ],
    whatwedoSearchBundle::class => [
        'all' => true,
    ],
];
