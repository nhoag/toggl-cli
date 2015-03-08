<?php

namespace TogglCli\Console;

use Symfony\Component\Console\Application as TogglCliApplication;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

class Application extends TogglCliApplication
{
    const NAME = 'Toggl CLI';
    const VERSION = '@git-version@';
    protected $config;
    protected $input;
    protected $output;

    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ConsoleEvents::COMMAND,
            array($this, 'onConsoleEventsCommand')
        );
        $this->setDispatcher($dispatcher);
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
     * @return \TogglCli\Console\TogglCliApplication
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
     * @return \TogglCli\Console\TogglCliApplication
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Callback method for console.command events.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @return \TogglCli\Console\TogglCliApplication
     */
    public function onConsoleEventsCommand(ConsoleCommandEvent $event)
    {
        $this->setInput($event->getInput());

        $style = new OutputFormatterStyle('cyan');
        $output = $event->getOutput();
        $output->getFormatter()->setStyle('debug', $style);
        $this->setOutput($output);
        $config = $this->loadConfig();

        $command = $event->getCommand();
        if (method_exists($command, 'setConfig')) {
            $command->setConfig($config);
        }
    }

    /**
     * Load configuration files from known or probable paths.
     *
     * @param string $fileBaseName
     * @return array
     * @throws \RuntimeException
     */
    public function loadConfig($fileBaseName = 'toggl-config')
    {
        $configLoaded = false;
        $this->config = array();

        $files = $this->getPossibleConfigPaths($fileBaseName);
        foreach ($files as $file) {
            $config = $this->loadConfigFromFile($file);
            if (!empty($config) && is_array($config)) {
                $this->config = array_merge_recursive($this->config, $config);
                $configLoaded = true;
            }
        }

        if (!$configLoaded) {
            $errorMessage = sprintf(
              "A loadable configuration was not found. Places checked:\n    %s\n",
              implode("\n    ", $files)
            );
            throw new \RuntimeException($errorMessage);
        }

        $this->verbose("Loaded configuration.");
        $this->debug($this->getScrubbedConfig());

        return $this->config;
    }

    /**
     * Returns a list of paths where a configuration file may be found.
     *
     * /etc, $HOME, and $PWD
     *
     * @param $fileBaseName
     * @return array
     */
    public function getPossibleConfigPaths($fileBaseName)
    {
        $files = array();
        $files[] = "/etc/{$fileBaseName}";
        if (isset($_ENV['HOME'])) {
            $files[] = "{$_ENV['HOME']}/.{$fileBaseName}";
        } elseif ($_SERVER['HOME']) {
            $files[] = "{$_SERVER['HOME']}/.{$fileBaseName}";
        }
        $files[] = ".{$fileBaseName}";

        return $files;
    }

    /**
     * Loads configuration settings from a specific file.
     *
     * @param string $file
     * @return array|null
     */
    public function loadConfigFromFile($file)
    {
        $config = null;

        $this->debug("Checking if config file '{$file}' exists");

        if (file_exists($file)) {
            $this->verbose("Applying configuration from {$file}");
            $config = Yaml::parse(file_get_contents($file));

            if (!is_array($config)) {
                $this->debug(
                  "\tThe config file '{$file}' exists but is empty."
                );
            }
        }

        return $config;
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

    /**
     * Obscures known sensitive data from config.
     *
     * Replacements:
     * - Toggl api_token - replaces first 28 chars with "*"
     * - Salesforce security_token - replaces first 21 chars with "*"
     * - Salesforce password - replaces first 8 chars with "*"
     *
     * All lines are prefixed with "config> "
     *
     * @return string
     */
    public function getScrubbedConfig()
    {
        return preg_replace(
          array(
            '#(api_token: )([\S]{28})#',
            '#(security_token: )([\S]{21})#',
            '#(password: )([\S]{8})#',
            '#^(.*)#m'
          ),
          array(
            '\1****************************',
            '\1*********************',
            '\1********',
            'config> \1'
          ),
          Yaml::dump($this->config)
        );
    }
}
