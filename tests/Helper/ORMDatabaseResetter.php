<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests\Helper;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class ORMDatabaseResetter extends AbstractSchemaResetter
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(Application $application, ManagerRegistry $registry)
    {
        $this->application = $application;
        $this->registry = $registry;
    }

    public function resetDatabase(): void
    {
        $this->dropAndResetDatabase();
        $this->createSchema();
    }

    public function resetSchema(): void
    {
        $this->dropSchema();
        $this->createSchema();
    }

    private function createSchema(): void
    {
        if (self::isResetUsingMigrations()) {
            $this->runCommand($this->application, 'doctrine:migrations:migrate', [
                '-n' => true,
            ]);

            return;
        }

        foreach ($this->objectManagersToReset() as $manager) {
            $this->runCommand(
                $this->application,
                'doctrine:schema:create',
                [
                    '--em' => $manager,
                ]
            );
        }
    }

    private function dropSchema(): void
    {
        if (self::isResetUsingMigrations()) {
            $this->dropAndResetDatabase();

            return;
        }

        foreach ($this->objectManagersToReset() as $manager) {
            $this->runCommand(
                $this->application,
                'doctrine:schema:drop',
                [
                    '--em' => $manager,
                    '--force' => true,
                ]
            );
        }
    }

    private function dropAndResetDatabase(): void
    {
        foreach ($this->connectionsToReset() as $connection) {
            $dropParams = [
                '--connection' => $connection,
                '--force' => true,
            ];

            if ($this->registry->getConnection($connection)->getDatabasePlatform()->getName() !== 'sqlite') {
                // sqlite does not support "--if-exists" (ref: https://github.com/doctrine/dbal/pull/2402)
                $dropParams['--if-exists'] = true;
            }

            $this->runCommand($this->application, 'doctrine:database:drop', $dropParams);

            $this->runCommand(
                $this->application,
                'doctrine:database:create',
                [
                    '--connection' => $connection,
                ]
            );
        }
    }

    /**
     * @return list<string>
     */
    private function connectionsToReset(): array
    {
        if (isset($_SERVER['FOUNDRY_RESET_CONNECTIONS'])) {
            return \explode(',', $_SERVER['FOUNDRY_RESET_CONNECTIONS']);
        }

        return [$this->registry->getDefaultConnectionName()];
    }

    /**
     * @return list<string>
     */
    private function objectManagersToReset(): array
    {
        if (isset($_SERVER['FOUNDRY_RESET_OBJECT_MANAGERS'])) {
            return \explode(',', $_SERVER['FOUNDRY_RESET_OBJECT_MANAGERS']);
        }

        return [$this->registry->getDefaultManagerName()];
    }

    private static function isResetUsingMigrations(): bool
    {
        return 'migrate' === ($_SERVER['FOUNDRY_RESET_MODE'] ?? 'schema');
    }
}
