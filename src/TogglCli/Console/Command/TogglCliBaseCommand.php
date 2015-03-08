<?php

namespace TogglCli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

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
}