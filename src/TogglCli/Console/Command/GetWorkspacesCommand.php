<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AJT\Toggl\TogglClient;

class GetWorkspacesCommand extends TogglCliBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('get:workspaces')
            ->setDescription('Get Toggl Workspaces')
            ->addOption(
                'name',
                NULL,
                InputOption::VALUE_OPTIONAL,
                'Filter workspaces by name fragment'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        $workspaces = $toggl_client->getWorkspaces(array());

        foreach($workspaces as $workspace){
            if ($name) {
                if (preg_match("/$name/i", $workspace['name'])) {
                    $output->writeln($workspace['id'] . ' - ' . $workspace['name']);
                }
            } else {
                $output->writeln($workspace['id'] . ' - ' . $workspace['name']);
            }
        }
    }
}