<?php

namespace Konsole\Commands;

use Konsole\Command;

class GenerateCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected $signature = 'generate
                            {name : Command Name}
                            {--command= : The command you can call in Konsole Application}';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Generate new Konsole command';

    /**
     * {@inheritDoc}
     */
    public function handle()
    {
        $name = $this->argument('name');
        $command = $this->option('command') ?: '';
        $compiled = $this->compileStubFile($name, $command);
        $path = dirname(__DIR__)."/Commands/{$name}.php";
        $kernelPath = dirname(__DIR__).'/Kernel.php';

        if (file_exists($path)) {
            if (!$this->confirm('File already exists, do you want to replace? [y|N]')) {
                $this->warn('==> Cannot generate command because destination file already exists.');

                return 1;
            }
        }

        $this->putFile($path, $compiled);

        $this->info("==> Command has been generated successfully in {$path}.");
        $this->line('');
        $this->line("==> Caveats");
        $this->comment("To make {$name} runnable add 'Konsole\\Commands\\{$name}' in {$kernelPath}.");

        return 0;
    }

    /**
     * Compile stub file.
     *
     * @param  string $name Class name.
     *
     * @return string
     */
    protected function compileStubFile($name, $command)
    {
        return str_replace(
            ['{{name}}', '{{command}}'],
            [$name, $command],
            file_get_contents(dirname(__DIR__).'/stubs/CommandStub.stub')
        );
    }

    /**
     * Put compiled stub to certain path.
     *
     * @param  string $path
     * @param  string $content
     *
     * @return void
     */
    protected function putFile($path, $content)
    {
        file_put_contents($path, $content);
    }
}
