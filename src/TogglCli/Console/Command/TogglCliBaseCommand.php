<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class TogglCliBaseCommand extends Command
{
    protected $config;
    protected $input;
    protected $output;

    /**
     * Set the object's config attribute. This method is chainable.
     *
     * @param $config
     *
     * @return \TogglCli\Console\Command\TogglCliBaseCommand
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Returns the object's config.
     *
     * @return array|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the object's input attribute.
     *
     * If input is unset, it initializes it as ArgvInput
     *
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput()
    {
        if (empty($this->input)) {
            $this->input = new ArgvInput();
        }

        return $this->input;
    }

    /**
     * Set the object's input attribute.
     *
     * This method is chainable.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return \TogglCli\Console\Command\TogglCliBaseCommand
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Returns the object's output attribute.
     *
     * If output is unset, it initializes it as ConsoleOutput
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        if (empty($this->output)) {
            $this->output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, true);
        }

        return $this->output;
    }

    /**
     * Set the object's output attribute. This method is chainable.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \TogglCli\Console\Command\TogglCliBaseCommand
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Writes a debugging message if applicable.
     *
     * @param $message
     */
    public function debug($message)
    {
        $output = $this->getOutput();

        if ($output->isDebug()) {
            $output->writeln(sprintf("<debug>%s</debug>", $message));
        }
    }

    /**
     * Writes a verbose message if applicable.
     *
     * @param $message
     */
    public function verbose($message)
    {
        $output = $this->getOutput();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(sprintf("<info>%s</info>", $message));
        }
    }

    public function highlight($string, $hilite, $style = 'fg=white;bg=magenta')
    {
        $regex = "/{$hilite}/i";
        preg_match_all($regex, $string, $matches);
        $unmatch = preg_split($regex, $string, NULL, PREG_SPLIT_OFFSET_CAPTURE);
        $rebuild = '';
        $open = "<{$style}>";
        $close = "</{$style}>";
        foreach ($unmatch as $i) {
            if (strlen($rebuild) == $i[1]) {
                $rebuild .= $i[0];
            } elseif (strlen($rebuild) < $i[1]) {
                $rebuild .= $open;
                while (strlen($rebuild) - strlen($open) < $i[1]) {
                    $rebuild .= array_shift($matches[0]);
                }
                $rebuild .= $close . $i[0];
            } else {
                $rebuild .= $open;
                foreach ($matches as $x) {
                    foreach ($x as $y) {
                        $rebuild .= $y;
                    }
                }
                $rebuild .= $close . $i[0];
            }
        }
        return $rebuild;
    }

    public function tableBuilder($output, $headers, $rows)
    {
        $table = new Table($output);
        $table
          ->setHeaders($headers)
          ->setRows($rows)
        ;
        return $table;
    }

    public function truncateString($name, $max_chars = 30, $direction = 'center')
    {
        $text_length = strlen($name);
        if ($text_length > $max_chars) {
            $replace_length = $text_length - $max_chars;
            if ($direction == 'center') {
                $replace_start = $text_length/2 - $replace_length/2;
            } elseif ($direction == 'right') {
                $replace_start = $text_length - $replace_length;
            } elseif ($direction == 'left') {
                $replace_start = 0;
            } elseif ($direction == 'offset-left') {
                $replace_start = $text_length/2.5 - $replace_length/2;
            }
            return substr_replace($name, '<comment>...</comment>', $replace_start, $replace_length);
        } else {
            return $name;
        }
    }
}