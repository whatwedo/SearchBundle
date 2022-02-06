<?php

declare(strict_types=1);
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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CoreBundle\Command\BaseCommand;
use whatwedo\SearchBundle\Populator\PopulateOutputInterface;
use whatwedo\SearchBundle\Populator\PopulatorInterface;

class PopulateCommand extends BaseCommand implements PopulateOutputInterface
{
    private ?ProgressBar $progress = null;

    public function __construct(
        protected PopulatorInterface $populator
    ) {
        parent::__construct(null);
    }

    public function progressStart(int $max): void
    {
        $this->progress = new ProgressBar($this->output, $max);
        $this->progress->start();
    }

    public function progressFinish(): void
    {
        $this->progress->finish();
    }

    public function setProgress(int $i): void
    {
        $this->progress->setProgress($i);
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setName('whatwedo:search:populate')
            ->setDescription('Populate the search index')
            ->setHelp('This command populate the search index according to the entity annotations')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Only populate index for this entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Initialize command
        parent::execute($input, $output);

        $this->populator->populate($this, null);

        // Tear down
        $this->tearDown();

        return 0;
    }
}
