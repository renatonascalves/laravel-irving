<?php

namespace Irving;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class IrvingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'irving';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold Irving route and controller';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->exportBackend();

        $this->info('Irving route and controller scaffolding generated successfully.');
    }

    /**
     * Export the backend.
     *
     * @return void
     */
    protected function exportBackend(): void
    {

        // Move controller(s).
        $filesystem = new Filesystem;
        collect($filesystem->allFiles(__DIR__.'/stubs/controllers'))
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->copy(
                    $file->getPathname(),
                    app_path('Http/Controllers/'. Str::replaceLast('.stub', '.php', $file->getFilename()))
                );
            });

        // Update with namespace.
        file_put_contents(
            app_path('Http/Controllers/IrvingController.php'),
            $this->compileControllerStub()
        );

        // Move Irving route.
        // @todo confirm if this route is already there so to avoid duplication.
        file_put_contents(
            base_path('routes/api.php'),
            file_get_contents(__DIR__.'/stubs/routes.stub'),
            FILE_APPEND
        );

        // @todo move config, if necessary.
    }

    /**
     * Compiles the "IrvingController" stub.
     *
     * @return string
     */
    protected function compileControllerStub(): string
    {
        return str_replace(
            '{{namespace}}',
            $this->laravel->getNamespace(),
            file_get_contents(__DIR__ . '/stubs/controllers/IrvingController.stub')
        );
    }
}
