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
                'w',
                InputOption::VALUE_OPTIONAL,
                'Specify workspace ID'
            )
            ->addOption(
                'filter',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Filter tags by string'
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
            $output_indicator = false;
            $tag_indicator = false;
            foreach($workspaces as $workspace){
                $tags = $toggl_client->getWorkspaceTags(array('id' => $workspace['id']));
                if (!empty($tags)) {
                    $tag_indicator = true;
                    usort($tags, function ($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    foreach($tags as $tag){
                        if ($filter) {
                            if (preg_match("/$filter/i", $tag['name'])) {
                                $output_indicator = true;
                                $string = $this->highlight($tag['name'], $filter);
                                $output->writeln($string);
                            }
                        } elseif ($tag) {
                            $output_indicator = true;
                            $output->writeln($tag['name']);
                        }
                    }
                }
            }
            if (!$tag_indicator) {
                $output->writeln('<comment>No tags found</comment>');
            } elseif (!$output_indicator) {
                $output->writeln("<comment>No tags found with name '{$filter}'</comment>");
            }
        } else {
            $output->writeln('<comment>No workspaces found</comment>');
        }
    }
}