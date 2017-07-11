<?php

namespace Colfej\LeKioskCLI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command {

    protected function configure() {
        $this->setName('hello');
        $this->setDescription('Hello world command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Hello World');
    }

}