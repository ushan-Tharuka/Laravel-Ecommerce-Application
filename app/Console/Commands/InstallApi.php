<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up API routes, controllers, and authentication';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Setting up API routes and controllers...');

        // Example of generating API resources or performing tasks
        $this->call('make:controller', ['name' => 'Api/ProductController']);
        $this->call('make:model', ['name' => 'Api/Product']);
        $this->call('make:resource', ['name' => 'Api/ProductResource']);

        // Optionally, set up routes for your API
        file_put_contents(
            base_path('routes/api.php'),
            PHP_EOL . "Route::resource('products', App\Http\Controllers\Api\ProductController::class);" . PHP_EOL,
            FILE_APPEND
        );

        $this->info('API setup complete!');
    }
}
