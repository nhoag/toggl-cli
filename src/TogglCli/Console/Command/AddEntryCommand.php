<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AJT\Toggl\TogglClient;

class AddEntryCommand extends TogglCliBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('add:entry')
            ->setDescription('Add Toggl Entry')
            ->addArgument(
                'project_id',
                InputArgument::REQUIRED,
                'Name of entry'
            )
            ->addOption(
                'billable',
                'b',
                InputOption::VALUE_NONE,
                'Mark time entry as billable'
            )
            ->addOption(
                'description',
                'd',
                InputOption::VALUE_REQUIRED,
                'A brief description of this time entry'
            )
            ->addOption(
                'duration',
                'l',
                InputOption::VALUE_REQUIRED,
                'Duration of time entry'
            )
            ->addOption(
                'start',
                's',
                InputOption::VALUE_REQUIRED,
                'Start time of entry'
            )
            ->addOption(
                'tag',
                't',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Time entry tags'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project_id = $input->getArgument('project_id');
        $billable = $input->getArgument('billable');
        $description = $input->getOption('description');
        $duration = $input->getOption('duration');
        $start = $input->getOption('start');
        $tags = $input->getOption('tag');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        $toggl_client->createTimeEntry(array(
            'time_entry' => array(
                'pid' => intval($project_id),
                'billable' => $billable,
                'description' => $description,
                'created_with' => 'toggl-cli',
                'duration' => intval($duration),
                'start' => $start,
                'tags' => $tags
            )
        ));
    }
}
