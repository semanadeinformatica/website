<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateScoutIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-scout-indexes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the scout indexes for the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('scout:delete-all-indexes');

        collect(File::allFiles(app_path('Models')))
            ->map(fn ($item) => 'App\\Models\\'.$item->getBasename('.php'))
            ->filter(fn ($item) => in_array(\Laravel\Scout\Searchable::class, class_uses($item)))
            ->each(function ($item) {
                $this->call('scout:index', [
                    'name' => (new $item())->searchableAs(),
                ]);

                $this->call('scout:import', [
                    'model' => $item,
                ]);
            });

        $this->call('scout:sync-index-settings');
    }
}
