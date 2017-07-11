<?php

namespace Colfej\LeKioskCLI\Command;

use Humbug\SelfUpdate\Updater;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

    const LAST_PHAR_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/lekiosk-cli.phar';
    const LAST_VERSION_URL = 'https://jcolfej.github.io/LeKiosk-cli/latest/version';

    protected function configure() {
        $this->setName('update');
        $this->setDescription('Self-update to the latest version');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $updater = new Updater(null, false);

        $updater->getStrategy()->setPharUrl(self::LAST_PHAR_URL);
        $updater->getStrategy()->setVersionUrl(self::LAST_VERSION_URL);

        try {
            $result = $updater->update();
            echo $result ? "Updated!\n" : "No update needed!\n";
        } catch (\Exception $e) {
            echo "Well, something happened! Either an oopsie or something involving hackers.\n";
            exit(1);
        }
        
    }

}