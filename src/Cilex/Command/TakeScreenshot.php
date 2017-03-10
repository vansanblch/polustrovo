<?php

namespace Cli\Command;

use Cilex\Provider\Console\Command;
use Cli\ScreenshotService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TakeScreenshot extends Command
{
    protected function configure()
    {
        $this->setName('take:screenshot');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ScreenshotService $screenshotService */
        $screenshotService = $this->getContainer()->offsetGet('screenshot_service');

        $result = $screenshotService->take();

        $output->writeln($result);
    }
}