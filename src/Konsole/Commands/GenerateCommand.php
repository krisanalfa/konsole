<?php

namespace Konsole\Commands;

use Konsole\Command;

class GenerateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'generate
                            {name : Command Name}
                            {--C|command= : The name and signature of the console command}
                            {--D|description= : The description of the console command}
                            {--F|force : Force write when command target already exists}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new Konsole command';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $name = $this->argument('name');
        $basePath = dirname(__DIR__);

        $this->makeSure(($path = "{$basePath}/Commands/{$name}.php"), ($this->option('force') === true));

        $this->putFile($path, $this->compileStubFile($name, $this->option('command'), $this->option('description'), $basePath));

        $this->info("==> Command has been generated successfully in {$path}.");

        $this->suggest("To make {$name} command runnable, register it with the 'registerCommand' method in {$basePath}/bootstrap/app.php.");
    }

    /**
     * Make sure if user want to replace existing class.
     *
     * @param string $path
     * @param bool   $force
     */
    protected function makeSure($path, $force)
    {
        if (($force === false)
            and (file_exists($path))
            and ($this->confirm('File already exists, do you want to replace? [y|N]') === false)
        ) {
            $this->warn('==> Cannot generate command because destination file already exists.');

            die(1);
        }
    }

    /**
     * Compile stub file.
     *
     * @param string $name     Class name.
     * @param string $command
     * @param string $basePath
     *
     * @return string
     */
    protected function compileStubFile($name, $command, $description, $basePath)
    {
        return str_replace(
            ['{{name}}', '{{command}}', '{{description}}'],
            [$name, $command, $description],
            file_get_contents("{$basePath}/stubs/CommandStub.stub")
        );
    }

    /**
     * Put compiled stub to certain path.
     *
     * @param string $path
     * @param string $content
     *
     * @return int|void
     */
    protected function putFile($path, $content)
    {
        if ((is_writable(dirname($path)) === false)) {
            $this->error("Destination {$path} is not writable.");

            die(1);
        }

        return file_put_contents($path, $content);
    }
}
