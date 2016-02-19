# Konsole

[![Latest Stable Version](https://poser.pugx.org/krisanalfa/konsole/version)](https://packagist.org/packages/krisanalfa/konsole)
[![Total Downloads](https://poser.pugx.org/krisanalfa/konsole/downloads)](https://packagist.org/packages/krisanalfa/konsole)
[![License](https://poser.pugx.org/krisanalfa/konsole/license)](https://packagist.org/packages/krisanalfa/konsole)

- [Introduction](#introduction)
- [Installing](#installing)
- [Writing Commands](#writing-commands)
    - [Command Structure](#command-structure)
- [Command I/O](#command-io)
    - [Defining Input Expectations](#defining-input-expectations)
    - [Retrieving Input](#retrieving-input)
    - [Prompting For Input](#prompting-for-input)
    - [Writing Output](#writing-output)
- [Registering Commands](#registering-commands)
- [Calling Commands from Other Commands](#calling-commands-from-other-commands)

<a name="introduction"></a>
## Introduction

Konsole is a minimum console application built on Laravel Console components. To view a list of all available Konsole commands, you may use the `list` command:

```sh
php konsole list
```

Every command also includes a "help" screen which displays and describes the command's available arguments and options. To view a help screen, simply precede the name of the command with `help`:

```sh
php konsole help generate
```

<a name="installing"></a>
## Installing

Installing Konsole is simple via `composer` command:

```sh
composer create-project krisanalfa/konsole my-console-application
cd my-console-application
php konsole --version
```

<a name="writing-commands"></a>
## Writing Commands

In addition to the commands provided with Konsole, you may also build your own custom commands for working with your application. You may store your custom commands in the `src/Konsole/Commands` directory; however, you are free to choose your own storage location as long as your commands can be autoloaded based on your `composer.json` settings.

To create a new command, you may use the `generate` Konsole command, which will generate a command stub to help you get started:

```sh
php konsole generate SendEmails
```

The command above would generate a class at `src/Konsole/Commands/SendEmails.php`. When creating the command, the `--command` or `-C` option may be used to assign the terminal command name:

```sh
php konsole make:console SendEmails --command=emails:send
```

If you want to force generate the command, you may supply `--force` or `-F` option:

```sh
php konsole make:console SendEmails --command=emails:send --force
```

<a name="command-structure"></a>
### Command Structure

Once your command is generated, you should fill out the `signature` and `description` properties of the class, which will be used when displaying your command on the `list` screen.

The `handle` method will be called when your command is executed. You may place any command logic in this method. Let's take a look at an example command.

Note that we are able to inject any dependencies we need into the command's constructor. The Laravel service container will automatically inject all dependencies type-hinted in the constructor. For greater code reusability, it is good practice to keep your console commands light and let them defer to application services to accomplish their tasks.

```php
namespace Konsole\Commands;

use Konsole\Command;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    }
}
```

<a name="command-io"></a>
## Command I/O

<a name="defining-input-expectations"></a>
### Defining Input Expectations

When writing console commands, it is common to gather input from the user through arguments or options. Laravel makes it very convenient to define the input you expect from the user using the `signature` property on your commands. The `signature` property allows you to define the name, arguments, and options for the command in a single, expressive, route-like syntax.

All user supplied arguments and options are wrapped in curly braces. In the following example, the command defines one **required** argument: `user`:

```php
/**
 * The name and signature of the console command.
 *
 * @var string
 */
protected $signature = 'email:send {user}';
```

You may also make arguments optional and define default values for optional arguments:

```php
// Optional argument...
protected $signature = 'email:send {user?}'

// Optional argument with default value...
protected $signature = 'email:send {user=foo}'
```

Options, like arguments, are also a form of user input. However, they are prefixed by two hyphens (`--`) when they are specified on the command line. We can define options in the signature like so:

```php
/**
 * The name and signature of the console command.
 *
 * @var string
 */
protected $signature = 'email:send {user} {--pretending}';
```

In this example, the `--pretending` switch may be specified when calling the Konsole command. If the `--pretending` switch is passed, the value of the option will be `true`. Otherwise, the value will be `false`:

```sh
php konsole email:send 1 --pretending
```

You may also specify that the option should be assigned a value by the user by suffixing the option name with a `=` sign, indicating that a value should be provided:

```php
/**
 * The name and signature of the console command.
 *
 * @var string
 */
protected $signature = 'email:send {user} {--pretending=}';
```

In this example, the user may pass a value for the option like so:

```sh
php konsole email:send 1 --pretending=default
```

You may also assign default values to options:

```php
protected $signature = 'email:send {user} {--pretending=default}';
```

To assign a shortcut when defining an option, you may specify it before the option name and use a | delimiter to separate the shortcut from the full option name:

```php
protected $signature = 'email:send {user} {--P|pretending}';
```
If you would like to define arguments or options to expect array inputs, you may use the `*` character:

```php
protected $signature = 'email:send {user*}';
```

Or:

```php
protected $signature = 'email:send {user} {--id=*}';
```

#### Input Descriptions

You may assign descriptions to input arguments and options by separating the parameter from the description using a colon:

```php
/**
 * The name and signature of the console command.
 *
 * @var string
 */
protected $signature = 'email:send
                        {user : The ID of the user}
                        {--pretending= : Whether the job should be prentended}';
```

<a name="retrieving-input"></a>
### Retrieving Input

While your command is executing, you will obviously need to access the values for the arguments and options accepted by your command. To do so, you may use the `argument` and `option` methods:

```php
/**
 * Execute the console command.
 *
 * @return mixed
 */
public function handle()
{
    $userId = $this->argument('user');

    //
}
```

If you need to retrieve all of the arguments as an `array`, call `argument` with no parameters:

```php
$arguments = $this->argument();
```

Options may be retrieved just as easily as arguments using the `option` method. Like the `argument` method, you may call `option` without any parameters in order to retrieve all of the options as an `array`:

```php
// Retrieve a specific option...
$isPretending = ($this->option('pretending') !== null);

// Retrieve all options...
$options = $this->option();
```

If the argument or option does not exist, `null` will be returned.

<a name="prompting-for-input"></a>
### Prompting For Input

In addition to displaying output, you may also ask the user to provide input during the execution of your command. The `ask` method will prompt the user with the given question, accept their input, and then return the user's input back to your command:

```php
/**
 * Execute the console command.
 *
 * @return mixed
 */
public function handle()
{
    $name = $this->ask('What is your name?');
}
```

The `secret` method is similar to `ask`, but the user's input will not be visible to them as they type in the console. This method is useful when asking for sensitive information such as a password:

```php
$password = $this->secret('What is the password?');
```

#### Asking For Confirmation

If you need to ask the user for a simple confirmation, you may use the `confirm` method. By default, this method will return `false`. However, if the user enters `y` in response to the prompt, the method will return `true`.

```php
// The default answer is 'no|N'
if ($this->confirm('Do you wish to continue? [y|N]')) {
    // Do something if user answer 'yes|y'
}
```

#### Giving The User A Choice

The `anticipate` method can be used to provide autocompletion for possible choices. The user can still choose any answer, regardless of the auto-completion hints:

```php
$name = $this->anticipate('What is your name?', ['Alfa', 'Fitria']);
```

If you need to give the user a predefined set of choices, you may use the `choice` method. The user chooses the index of the answer, but the value of the answer will be returned to you. You may set the default value to be returned if nothing is chosen:

```php
$name = $this->choice('What is your name?', ['Alfa', 'Fitria'], $default);
```

<a name="writing-output"></a>
### Writing Output

To send output to the console, use the `line`, `info`, `comment`, `question`, `warn` and `error` methods. Each of these methods will use the appropriate ANSI colors for their purpose.

To display an information message to the user, use the `info` method. Typically, this will display in the console as green text:

```php
/**
 * Execute the console command.
 *
 * @return mixed
 */
public function handle()
{
    $this->info('Display this on the screen');
}
```

To display an warning message, use the `warn` method. Warning message text is typically displayed in orange:

```php
$this->warn('Something went wrong!');
```

To display an error message, use the `error` method. Error message text is typically displayed in red:

```php
$this->error('Something went wrong!');
```

If you want to display plain console output, use the `line` method. The `line` method does not receive any unique coloration:

```php
$this->line('Display this on the screen');
````

If you want to suggest user to do something, you can use the `suggest` method:

```php
$this->suggest('Better you pick Sven, because you have Magnus on your side.');
```

#### Table Layouts

The `table` method makes it easy to correctly format multiple rows / columns of data. Just pass in the headers and rows to the method. The width and height will be dynamically calculated based on the given data:

```php
$headers = ['Name', 'Email'];

$this->table($headers, $collection->toArray());
```

#### Progress Bars

For long running tasks, it could be helpful to show a progress indicator. Using the output object, we can start, advance and stop the Progress Bar. You have to define the number of steps when you start the progress, then advance the Progress Bar after each step:

```php
$bar = $this->output->createProgressBar(count($users));

foreach ($users as $user) {
    $this->performTask($user);

    $bar->advance();
}

$bar->finish();
```

For more advanced options, check out the [Symfony Progress Bar component documentation](http://symfony.com/doc/2.7/components/console/helpers/progressbar.html).

<a name="registering-commands"></a>
## Registering Commands

Once your command is finished, you need to register it with Konsole so it will be available for use. This is done within the `src/Konsole/Kernel.php` file.

Within this file, you will find a list of commands in the `commands` property. To register your command, simply add the class name to the list. When Konsole boots, all the commands listed in this property will be registered with Konsole:

```php
/**
 * The Konsole commands provided by your application.
 *
 * @var array
 */
protected $commands = [
    'Konsole\Commands\GenerateCommand',
    'Konsole\Commands\SendEmail',
];
```
<a name="calling-commands-from-other-commands"></a>
## Calling Commands From Other Commands

Sometimes you may wish to call other commands from an existing Konsole command. You may do so using the `call` method. This `call` method accepts the command name and an array of command parameters:

```php
/**
 * Execute the console command.
 *
 * @return mixed
 */
public function handle()
{
    $this->call('email:send', [
        'user' => 1, '--pretending' => 'default'
    ]);

    //
}
```

If you would like to call another console command and suppress all of its output, you may use the `callSilent` method. The `callSilent` method has the same signature as the `call` method:

```php
$this->callSilent('email:send', [
    'user' => 1, '--pretending' => 'default'
]);
```
