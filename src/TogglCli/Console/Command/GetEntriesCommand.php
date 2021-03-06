<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AJT\Toggl\TogglClient;

class GetEntriesCommand extends TogglCliBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('get:entries')
            ->setDescription('Get Toggl Entries')
            ->addOption(
                'billable',
                'b',
                InputOption::VALUE_NONE,
                'Return entries marked billable'
            )
            ->addOption(
                'end',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Specify end date-time'
            )
            ->addOption(
                'expand',
                'x',
                InputOption::VALUE_NONE,
                'Disable data truncation'
            )
            ->addOption(
                'non_billable',
                'i',
                InputOption::VALUE_NONE,
                'Return entries not marked billable'
            )
            ->addOption(
                'start',
                's',
                InputOption::VALUE_OPTIONAL,
                'Specify start date-time'
            )
            ->addOption(
                'project_id',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Specify project ID'
            )
            ->addOption(
                'workspace_id',
                'w',
                InputOption::VALUE_OPTIONAL,
                'Specify workspace ID'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $wid = $input->getOption('workspace_id');
        $toggl_client = TogglClient::factory(array('api_key' => $this->config['api_token']));
        if ($wid) {
            $workspaces = $toggl_client->getWorkspaces(array($wid));
        } else {
            $workspaces = $toggl_client->getWorkspaces(array());
        }

        if (!empty($workspaces)) {
            foreach ($workspaces as $workspace) {
                $projects = $toggl_client->getProjects(array('id' => $workspace['id']));
                if (!empty($projects)) {
                    $ref = $this->refBuilder($projects, $options);
                    $entries = $this->entriesBuilder($toggl_client, $options);
                    if (!empty($entries)) {
                        $headers = array('Date', 'Entry ID', 'Project', 'Time', '$', 'Tags');
                        $rows = $this->rowsBuilder($entries, $ref, $options);
                        $table = $this->tableBuilder($output, $headers, $rows);
                        $output->writeln('<info>' . $workspace['name'] . '</info>');
                        $table->render();
                    } else {
                        $output->writeln('<comment>No entries found</comment>');
                    }
                } else {
                    $output->writeln('<comment>No projects found</comment>');
                }
            }
        } else {
            $output->writeln('<comment>No workspaces found</comment>');
        }
    }

    protected function entriesBuilder($toggl_client, $options)
    {
        if ($options['start'] || $options['end']) {
            $entries = $toggl_client->getTimeEntries(array('start' => $options['start'], 'end' => $options['end']));
        } else {
            $entries = $toggl_client->getTimeEntries();
        }
        return $this->entriesFilter($entries, $options);
    }

    protected function entriesFilter($entries, $options)
    {
        foreach ($entries as $key => $entry) {
            $entries = $this->entriesFilterProject($entry, $key, $options, $entries);
            $entries = $this->entriesFilterBillable($entry, $key, $options, $entries);
        }
        return $entries;
    }

    protected function entriesFilterProject($entry, $key, $options, $entries)
    {
        if ($options['project_id']) {
            if ($entry['pid'] != $options['project_id']) {
                unset($entries[$key]);
            }
        }
        return $entries;
    }

    protected function entriesFilterBillable($entry, $key, $options, $entries)
    {
        if ($options['billable'] && !$options['non_billable']) {
            if (!$entry['billable']) {
                unset($entries[$key]);
            }
        } elseif ($options['non_billable'] && !$options['billable']) {
            if ($entry['billable']) {
                unset($entries[$key]);
            }
        }
        return $entries;
    }

    protected function refBuilder($projects, $options)
    {
        foreach ($projects as $project) {
            if ($options['expand']) {
                $ref[$project['id']] = $project['name'];
            } else {
                $ref[$project['id']] = $this->truncateString($project['name'], 20, 'offset-left');
            }
        }
        return $ref;
    }

    protected function rowsBuilder($entries, $ref, $options)
    {
        $rows = array();
        foreach ($entries as $entry) {
            $project = $ref[$entry['pid']];
            array_push($rows, $this->rowBuilder($entry, $project, $options));
        }
        return $rows;
    }

    protected function rowBuilder($entry, $project, $options)
    {
        if ($options['expand']) {
            $tags = implode(", ", $entry['tags']);
        } else {
            $tags = $this->truncateString(implode(", ", $entry['tags']), 16, 'right');
        }
        $date = date('Y-m-d', strtotime($entry['start']));
        $duration = gmdate("G:i", $entry['duration']);
        $bill = $entry['billable'] == 1 ? 'Y' : 'N';
        return array($date, $entry['id'], $project, $duration, $bill, $tags);
    }
}