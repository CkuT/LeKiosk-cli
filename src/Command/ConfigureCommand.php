<?php

namespace Colfej\LeKioskCLI\Command;

use Colfej\LeKioskCLI\Configuration;
use Colfej\LeKioskCLI\Api\Stores;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends Command {

    protected $input;
    protected $output;

    protected $formatter;

    protected function configure() {

        $this->setName('configure');
        $this->setDescription('Configure LeKiosk-cli');

        $this->addArgument('username', InputArgument::OPTIONAL, 'Your LeKiosk username.');
        $this->addArgument('password', InputArgument::OPTIONAL, 'Your LeKiosk password.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->input = $input;
        $this->output = $output;

        $this->formatter = $this->getHelper('formatter');

        $username = $this->getUsername();
        $password = $this->getPassword();

        $this->output->writeln('Write configuration ...', OutputInterface::VERBOSITY_VERBOSE);

        Configuration::write($username, $password);

        $this->output->writeln('Test configuration ...', OutputInterface::VERBOSITY_VERBOSE);

        $return = Stores::getList();

        $this->output->writeln('<fg=green>LeKiosk-cli is configured.</fg=green>');

    }

    protected function getUsername() {

        $helper = $this->getHelper('question');

        if (!$this->input->getArgument('username')) {

            $this->output->writeln('Ask for username ...', OutputInterface::VERBOSITY_VERY_VERBOSE);

            $question = new Question('Your username : ');

            $username = $helper->ask($this->input, $this->output, $question);

        } else {

            $username = $this->input->getArgument('username');

        }

        $this->output->writeln('Username => '.$username, OutputInterface::VERBOSITY_DEBUG);

        return $username;
        
    }

    protected function getPassword() {

        $helper = $this->getHelper('question');

        if (!$this->input->getArgument('password')) {

            $this->output->writeln('Ask for password ...', OutputInterface::VERBOSITY_VERY_VERBOSE);

            $question = new Question('Your password : ');

            $question->setHidden(true);
            $question->setHiddenFallback(false);

            $password = $helper->ask($this->input, $this->output, $question);

        } else {

            $password = $this->input->getArgument('password');

        }

        $this->output->writeln('Password => '.str_repeat('*', strlen($password)), OutputInterface::VERBOSITY_DEBUG);

        return $password;
        
    }

}