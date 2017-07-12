<?php

namespace Colfej\LeKioskCLI\Command\Download;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadManualCommand extends Command {

    protected function configure() {

        $this->setName('download:manual');
        $this->setDescription('Download issue from publication ID and issue ID');

        $this->addArgument('id_publication', InputArgument::REQUIRED, 'Publication ID');
        $this->addArgument('id_issue', InputArgument::REQUIRED, 'Issue ID');

        $this->addOption('clean', 'c', InputOption::VALUE_NONE, 'Delete original data.');

        $this->addOption('pdf', 'p', InputOption::VALUE_NONE, 'Convert to PDF.');
        $this->addOption('zip', 'z', InputOption::VALUE_NONE, 'Convert to ZIP.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $output->writeln('To do ...');

        print_r($input->getArguments());

    }

}