<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use AJT\Toggl\TogglClient;

class GetTagsCommand extends TogglCliBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('get:tags')
            ->setDescription('Get Toggl Tags')
            ->addOption(
                'workspace_id',
                NULL,
                InputOption::VALUE_OPTIONAL,
                'Specify workspace'
            )
            ->addOption(
                'name',
                NULL,
                InputOption::VALUE_OPTIONAL,
                'Filter by tag name fragment'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wid = $input->getOption('workspace_id');
        $name = $input->getOption('name');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        if ($wid) {
            $workspaces = $toggl_client->getWorkspaces(array($wid));
        } else {
            $workspaces = $toggl_client->getWorkspaces(array());
        }

        foreach($workspaces as $workspace){
            $tags = $toggl_client->getWorkspaceTags(array('id' => $workspace['id']));
            usort($tags, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            foreach($tags as $tag){
                if ($name) {
                    if (preg_match("/$name/i", $tag['name'])) {
                        $string = $this->highlight($tag['name'], $name);
                        $output->writeln($string);
                    }
                } else {
                    $output->writeln($tag['name']);
                }
            }
        }
    }
}