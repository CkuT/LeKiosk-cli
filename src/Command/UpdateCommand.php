<?php

namespace Colfej\LeKioskCLI\Command;

use Humbug\SelfUpdate\Updater;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

    const LAST_PHAR_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/lekiosk-cli.phar';
    const LAST_VERSION_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/version';

    protected function configure() {

        $this->setName('update');
        $this->setDescription('Update to most recent stable, pre-release or development build.');

        $this->addOption(
                'dev',
                'd',
                InputOption::VALUE_NONE,
                'Update to most recent development build of Humbug.'
            );
        $this->addOption(
                'non-dev',
                'N',
                InputOption::VALUE_NONE,
                'Update to most recent non-development (alpha/beta/stable) build.'
            );
        $this->addOption(
                'pre',
                'p',
                InputOption::VALUE_NONE,
                'Update to most recent pre-release version (alpha/beta/rc).'
            );
        $this->addOption(
                'stable',
                's',
                InputOption::VALUE_NONE,
                'Update to most recent stable version.'
            );
        $this->addOption(
                'rollback',
                'r',
                InputOption::VALUE_NONE,
                'Rollback to previous version if available on filesystem.'
            );
        $this->addOption(
                'check',
                'c',
                InputOption::VALUE_NONE,
                'Checks what updates are available across all possible stability tracks.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $updater = new Updater(null, false);

        $updater->getStrategy()->setPharUrl(self::LAST_PHAR_URL);
        $updater->getStrategy()->setVersionUrl(self::LAST_VERSION_URL);

        try {
            $result = $updater->update();
            echo $result ? "Updated!\n" : "No update needed!\n";
        } catch (\Exception $e) {
            print_r($e);
            exit(1);
        }
        
    }

}