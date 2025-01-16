<?php

declare(strict_types=1);

namespace MultiTenancyBundle\Command\Migration;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as DoctrineMigrateCommand;
use MultiTenancyBundle\Repository\TenantRepository;
use MultiTenancyBundle\Service\TenantDatabaseName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateCommand extends AbstractDoctrineCommand
{
    /**
     * @var TenantRepository
     */
    private $tenantRepository;

    /**
     * @var TenantDatabaseName
     */
    private $tenantDatabaseName;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setTenantRepository(TenantRepository $tenantRepository)
    {
        $this->tenantRepository = $tenantRepository;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setTenantDatabaseName(TenantDatabaseName $tenantDatabaseName)
    {
        $this->tenantDatabaseName = $tenantDatabaseName;
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setName('tenancy:migrate')
            ->setDescription('Wrapper to launch doctrine:migrations:migrate command as it would require a "configuration" option')
            ->addArgument('version', InputArgument::OPTIONAL, 'The version number (YYYYMMDDHHMMSS) or alias (first, prev, next, latest) to migrate to.', 'latest')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute the migration as a dry run.')
            ->addOption('query-time', null, InputOption::VALUE_NONE, 'Time all the queries individually.')
            ->addOption('allow-no-migration', null, InputOption::VALUE_NONE, 'Don\'t throw an exception if no migration is available (CI).')
            ->addOption('default', null, InputOption::VALUE_NONE, 'Run the migration in default db, not in any tenant db.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $defaultDbMigration =  $input->getOption('default');

        if ($defaultDbMigration) {

            $this->migrate($input, $output);
            return Command::SUCCESS;
        }
        
        $tenant = $input->getOption('tenant');
        
        if ($tenant) {
            $tenantDb = $this->tenantDatabaseName->getName($tenant);
            $this->migrate($input, $output, $tenantDb);
        } else {
            $this->executeAllTenants($input, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * Execute all tenants migration
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function executeAllTenants(InputInterface $input, OutputInterface $output): void
    {
        // Get array with all tenants name
        $tenants = $this->tenantRepository->findAll();

        foreach ($tenants as $tenant) {
            $this->migrate($input, $output, $tenant->getUuid());
        }
    }

    /**
     * Execute the migration
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $tenantDb
     * @return void
     */
    private function migrate(InputInterface $input, OutputInterface $output, ?string $tenantDb = null): void
    {
        $df = $this->getDependencyFactory($input);
        $migrateCommand = new DoctrineMigrateCommand($df);

        $newInput = new ArrayInput([
            'version'               => $input->getArgument('version'),
            '--dry-run'             => $input->getOption('dry-run'),
            '--query-time'          => $input->getOption('query-time'),
            '--allow-no-migration'  => $input->getOption('allow-no-migration'),
        ]);
        $newInput->setInteractive(false);

        if ($tenantDb) {
            $output->writeln("<info>Executing tenant: {$tenantDb}</info>");
            $this->setTenantConnection($df, $tenantDb);
        } else {
            $output->writeln("<info>Executing default DB</info>");
        }

        // Execute the migration
        $migrateCommand->run($newInput, $output);
    }
}
