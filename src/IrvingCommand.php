<?php

namespace Irving;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;
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
		$controller_path = app_path('Http/Controllers/IrvingController.php');
		if (!$filesystem->exists($controller_path)) {
			collect($filesystem->allFiles(__DIR__.'/stubs/controllers'))
				->each(function (SplFileInfo $file) use ($filesystem) {
					$filesystem->copy(
						$file->getPathname(),
						app_path('Http/Controllers/'. Str::replaceLast('.stub', '.php', $file->getFilename()))
					);
				});

			// Update namespace
			file_put_contents( $controller_path, $this->compileControllerStub() );
		}

        // Move Irving route.
		if (!$this->checkIrvingRoute()) {
			file_put_contents(
				base_path('routes/api.php'),
				file_get_contents(__DIR__.'/stubs/routes.stub'),
				FILE_APPEND
			);
		}
    }

	/**
	 * Check if the Irving route is preset.
	 *
	 * @return bool
	 */
	private function checkIrvingRoute(): bool
	{
		return \in_array(
			'irving/v1/components',
			array_unique(
				\array_map(
					function( $route ) {
						return $route->uri();
					},
					Route::getRoutes() ?? []
				),
				true
			)
		);
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
