<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Compilers\CompilerEngine;

class WindowsBladeCompilerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override the Blade compiler engine to handle Windows file locking issues
        if (PHP_OS_FAMILY === 'Windows') {
            $this->app->singleton('blade.compiler', function ($app) {
                return new class($app['files'], $app['config']['view.compiled'], $app->basePath()) extends BladeCompiler
                {
                    /**
                     * Compile the view at the given path.
                     *
                     * @param  string|null  $path
                     * @return void
                     */
                    public function compile($path = null)
                    {
                        if ($path) {
                            $this->setPath($path);
                        }

                        if (! is_null($this->cachePath)) {
                            $contents = $this->compileString($this->files->get($this->getPath()));

                            // Use file_put_contents instead of rename to avoid Windows locking issues
                            $compiledPath = $this->getCompiledPath($this->getPath());

                            // Ensure directory exists
                            if (! $this->files->exists(dirname($compiledPath))) {
                                $this->files->makeDirectory(dirname($compiledPath), 0755, true);
                            }

                            // Write directly instead of using atomic write (rename)
                            $this->files->put($compiledPath, $contents);
                        }
                    }
                };
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
