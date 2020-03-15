<?php
/**
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace whatwedo\SearchBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CoreBundle\Command\BaseCommand;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Manager\IndexManager;
use whatwedo\SearchBundle\Repository\IndexRepository;

/**
 * Class PopulateCommand.
 */
class SearchCommand extends BaseCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * @var FormatterManager
     */
    protected $formatterManager;

    /**
     * PopulateCommand constructor.
     */
    public function __construct(ManagerRegistry $doctrine, IndexManager $indexManager, FormatterManager $formatterManager)
    {
        parent::__construct(null);

        $this->doctrine = $doctrine;
        $this->indexManager = $indexManager;
        $this->formatterManager = $formatterManager;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setName('whatwedo:search:search')
            ->setDescription('Search the search index')
            ->setHelp('This command search the search index')
            ->addArgument('entity', InputArgument::REQUIRED, 'The Entity to be searched')
            ->addArgument('query', InputArgument::REQUIRED, 'The Query string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Initialize command
        parent::execute($input, $output);

        /** @var IndexRepository $indexRepo */
        $indexRepo = $this->doctrine->getRepository(Index::class);

        $targetEntity = $input->getArgument('entity');

        $ids = $indexRepo->search($input->getArgument('query'), $targetEntity);

        $table = new Table($output);
        $table
            ->setHeaders(['Entity', 'Id']);

        foreach ($ids as $id) {
            $table->addRow([$targetEntity, $id]);
        }

        $table->render();

        return 0;
    }
}
