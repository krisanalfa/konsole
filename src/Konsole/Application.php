<?php

namespace Konsole;

use Monolog\Logger;
use Illuminate\Container\Container;
use Monolog\Formatter\LineFormatter;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\RotatingFileHandler;

class Application extends Container
{
    /**
     * The Konsole commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

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
        'app' => 'registerApplicationBindings',
        'Konsole\Application' => 'registerApplicationBindings',

        'config' => 'registerConfigBindings',
        'Illuminate\Config\Repository' => 'registerConfigBindings',

        'log' => 'registerLogBindings',
        'Psr\Log\LoggerInterface' => 'registerLogBindings',
    ];

    /**
     * Create a new Lumen application instance.
     *
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;

        $this->configure('app');

        $this->commands = $this->make('config')->get('app.commands');
    }

    /**
     * Load a configuration file into the application.
     *
     * @param string $name
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->configPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app';
    }

    /**
     * Get the base path for the application.
     *
     * @param string|null $path
     *
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd().'/../');
        }

        return $this->basePath($path);
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param string|null $name
     *
     * @return string
     */
    public function configPath($name = null)
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config').'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
    }

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
        $abstract = $this->getAlias($abstract);

        if (array_key_exists($abstract, $this->availableBindings) &&
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
        return $this->commands;
    }

    /**
     * Register a command.
     *
     * @param mixed $command
     */
    public function registerCommand($command)
    {
        $this->commands = array_merge($this->commands, (array) $command);
    }

    /**
     * Get the application name.
     *
     * @return string
     */
    public function name()
    {
        return $this->make('config')->get('app.name');
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return $this->make('config')->get('app.version');
    }

    /**
     * Register container bindings for the application.
     */
    protected function registerConfigBindings()
    {
        $this->singleton('Illuminate\Contracts\Config\Repository', 'Illuminate\Config\Repository');
        $this->alias('Illuminate\Contracts\Config\Repository', 'config');
    }

    /**
     * Register application bindings.
     */
    protected function registerApplicationBindings()
    {
        $this->singleton('Konsole\Application', function () {
            return $this;
        });
        $this->alias('Konsole\Application', 'app');
    }

    /**
     * Register container bindings for the application.
     */
    protected function registerLogBindings()
    {
        $this->singleton('Psr\Log\LoggerInterface', function () {
            return new Logger($this->name(), [$this->getMonologHandler()]);
        });
        $this->alias('Psr\Log\LoggerInterface', 'log');
    }

    /**
     * Get the Monolog handler for the application.
     *
     * @return \Monolog\Handler\RotatingFileHandler
     */
    protected function getMonologHandler()
    {
        return (new RotatingFileHandler($this->basePath('var/log/'.mb_strtolower($this->name()).'.log'), 30))
                            ->setFormatter(new LineFormatter(null, null, true, true));
    }
}
