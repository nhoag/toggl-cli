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
                'workspace_id',
                'w',
                InputOption::VALUE_OPTIONAL,
                'Specify workspace ID'
            )
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Filter projects by string'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wid = $input->getOption('workspace_id');
        $filter = $input->getOption('filter');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        if ($wid) {
            $workspaces = $toggl_client->getWorkspaces(array($wid));
        } else {
            $workspaces = $toggl_client->getWorkspaces(array());
        }

        if (!empty($workspaces)) {
            $projects_indicator = false;
            $output_indicator = false;
            foreach ($workspaces as $workspace) {
                $projects = $toggl_client->getProjects(array('id' => $workspace['id']));
                if (!empty($projects)) {
                    $projects_indicator = true;
                    foreach ($projects as $project) {
                        if ($filter) {
                            if (preg_match("/$filter/i", $project['name'])) {
                                $output_indicator = true;
                                $string = $this->highlight($project['name'], $filter);
                                $output->writeln('<info>' . $project['id'] . '</info>' . ' - ' . $string);
                            }
                        } elseif ($project) {
                            $output_indicator = true;
                            $output->writeln('<info>' . $project['id'] . '</info>' . ' - ' . $project['name']);
                        }
                    }
                }
            }
            if (!$projects_indicator) {
                $output->writeln('<comment>No projects found</comment>');
            } elseif (!$output_indicator) {
                $output->writeln("<comment>No projects found with name '{$filter}'</comment>");
            }

        } else {
            $output->writeln('<comment>No workspaces found</comment>');
        }
    }
}
