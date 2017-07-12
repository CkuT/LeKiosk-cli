<?php

namespace Colfej\LeKioskCLI\Command\Download;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadLinkCommand extends Command {

    protected function configure() {

        $this->setName('download:link');
        $this->setDescription('Download issue from link');

        $this->addArgument('link', InputArgument::REQUIRED, 'Issue link');

        $this->addOption('clean', 'c', InputOption::VALUE_NONE, 'Delete original data.');

        $this->addOption('pdf', 'p', InputOption::VALUE_NONE, 'Convert to PDF.');
        $this->addOption('zip', 'z', InputOption::VALUE_NONE, 'Convert to ZIP.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->writeln('To do ...');

        print_r($input->getArguments());

    }

}