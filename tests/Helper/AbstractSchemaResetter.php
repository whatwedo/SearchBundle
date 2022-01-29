<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 */
abstract class AbstractSchemaResetter
{
    abstract public function resetSchema(): void;

    protected function runCommand(Application $application, string $command, array $parameters = []): void
    {
        $exit = $application->run(
            new ArrayInput(\array_merge([
                'command' => $command,
            ], $parameters)),
            $output = new BufferedOutput()
        );

        if ($exit !== 0) {
            throw new \RuntimeException(\sprintf('Error running "%s": %s', $command, $output->fetch()));
        }
    }
}
