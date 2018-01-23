<?php

namespace Colfej\LeKioskCLI\Command\Download;

use Colfej\LeKioskCLI\Command\Download\DownloadManualCommand;
use Colfej\LeKioskCLI\Api\Library;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadLibraryCommand extends Command {

    protected function configure() {

        $this->setName('download:library');
        $this->setDescription('Download library');

        $this->addOption('archived', 'a', InputOption::VALUE_NONE, 'Also download archived issues.');
        $this->addOption('clean', 'd', InputOption::VALUE_NONE, 'Delete original data.');

        $this->addOption('pdf', 'p', InputOption::VALUE_NONE, 'Convert to PDF.');
        $this->addOption('zip', 'z', InputOption::VALUE_NONE, 'Convert to ZIP.');

        $this->addOption('cover', 'c', InputOption::VALUE_NONE, 'Get cover image.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->writeln('Load library ...');

        $publications = Library::getAllPublications();

        foreach ($publications as $publication) {

            $output->writeln('Publication : '.$publication['title']);
            
            $issues = Library::getAllIssuesForPublication($publication['publicationId']);

            foreach ($issues as $issue) {

                $output->writeln('Issue : '.$issue['title'].' #'.$issue['issueNumber']);

                DownloadManualCommand::download($input, $output, $publication['publicationId'], $issue['issueId']);

            }

        }

    }

}
