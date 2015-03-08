<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AJT\Toggl\TogglClient;

class GetProjectsCommand extends TogglCliBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('get:projects')
            ->setDescription('Get Toggl Projects')
            ->addOption(
                'name',
                NULL,
                InputOption::VALUE_OPTIONAL,
                'Filter projects by name fragment'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        $workspaces = $toggl_client->getWorkspaces(array());

        foreach($workspaces as $workspace){
            $projects = $toggl_client->getProjects(array('id' => $workspace['id']));
            foreach($projects as $project){
                if ($name) {
                    if (preg_match("/$name/i", $project['name'])) {
                        $output->writeln($project['id'] . ' - ' . $project['name']);
                    }
                } else {
                    $output->writeln($project['id'] . ' - ' . $project['name']);
                }
            }
        }
    }
}
