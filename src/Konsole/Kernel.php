<?php

namespace Konsole;

class Kernel
{
    /**
     * The application implementation.
     *
     * @var \Konsole\Application
     */
    protected $app;

    /**
     * The Konsole application instance.
     *
     * @var \Konsole\Konsole
     */
    protected $konsole;

    /**
     * The Konsole commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'Konsole\Commands\GenerateCommand',
    ];

    /**
     * Create a new console kernel instance.
     *
     * @param \Konsole\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Run the console application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function handle($input, $output = null)
    {
        return $this->konsole()->run($input, $output);
    }

    /**
     * Run a Konsole console command by name.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return int
     */
    public function call($command, array $parameters = [])
    {
        return $this->konsole()->call($command, $parameters);
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        return $this->konsole()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->konsole()->output();
    }

    /**
     * Get all registered commands.
     *
     * @return array
     */
    public function commands()
    {
        return $this->commands;
    }

    /**
     * Get the Konsole application instance.
     *
     * @return \Konsole\Konsole
     */
    protected function konsole()
    {
        if (is_null($this->konsole)) {
            return $this->konsole = (new Konsole($this->app, $this->app->version()))
                                ->resolveCommands($this->commands);
        }

        return $this->konsole;
    }
}
