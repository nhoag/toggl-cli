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
                'filter',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Filter workspaces by string'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filter = $input->getOption('filter');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        $workspaces = $toggl_client->getWorkspaces(array());

        if (!empty($workspaces)) {
            $workspace_indicator = false;
            foreach($workspaces as $workspace) {
                if ($filter) {
                    if (preg_match("/$filter/i", $workspace['name'])) {
                        $workspace_indicator = true;
                        $string = $this->highlight($workspace['name'], $filter);
                        $output->writeln('<info>' . $workspace['id'] . '</info>' . ' - ' . $string);
                    }
                } elseif ($workspace) {
                    $workspace_indicator = true;
                    $output->writeln('<info>' . $workspace['id'] . '</info>' . ' - ' . $workspace['name']);
                }
            }
            if (!$workspace_indicator) {
                $output->writeln("<comment>No workspaces found with name '{$filter}'</comment>");
            }
        } else {
            $output->writeln('<comment>No workspaces found</comment>');
        }
    }
}