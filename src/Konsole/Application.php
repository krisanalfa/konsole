<?php

namespace Konsole;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class Application extends Container
{
    /**
     * The Konsole commands provided by your application.
     *
     * @var array
     */
    private static $commands = [
        'Konsole\Commands\GenerateCommand',
    ];

    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * The service binding methods that have been executed.
     *
     * @var array
     */
    protected $ranServiceBinders = [];

    /**
     * The available container bindings and their respective load methods.
     *
     * @var array
     */
    public $availableBindings = [
        //
    ];

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array  $parameters
     *
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($this->normalize($abstract));

        if (array_key_exists($abstract, $this->availableBindings) and
            !array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)) {
            $this->{$method = $this->availableBindings[$abstract]}();

            $this->ranServiceBinders[$method] = true;
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Get all registered commands.
     *
     * @return array
     */
    public function commands()
    {
        return static::$commands;
    }

    /**
     * Register a service provider with the application.
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @param array                                      $options
     * @param bool                                       $force
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false)
    {
        if (!$provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = true;

        $provider->register();
        $provider->boot();
    }

    /**
     * Register a command.
     *
     * @param  string $command
     */
    public function registerCommand($command)
    {
        if (array_key_exists($command, static::$commands) === false) {
            static::$commands[] = $command;
        }
    }

    /**
     * Register commands.
     *
     * @param array $commands
     */
    public function registerCommands(array $commands)
    {
        $commands = array_merge(static::$commands, $commands);

        static::$commands = array_unique($commands);
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return 'Konsole (0.0.5) (Illuminate Components 5.2.*)';
    }
}
