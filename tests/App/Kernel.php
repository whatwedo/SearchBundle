<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/../../var/cache/' . $this->environment;
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__) . '/App';
    }
}
