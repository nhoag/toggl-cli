<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AJT\Toggl\TogglClient;

class DeleteEntriesCommand extends TogglCliBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('delete:entries')
            ->setDescription('Delete Toggl Entries')
            ->addArgument(
                'entry_ids',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Space-delimited IDs of entries to delete'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entries = $input->getArgument('entry_ids');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));

        if ($entries) {
            foreach ($entries as $entry) {
                $toggl_client->deleteTimeEntry(array('id' => intval($entry)));
            }
        }
    }
}
