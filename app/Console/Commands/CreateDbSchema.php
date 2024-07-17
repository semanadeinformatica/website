<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDbSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-db-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $conn = DB::connectUsing("pgsql", [
            ...config('database.connections.pgsql'),
            'search_path' => "public",
        ]);

        $search_path = config('database.connections.pgsql.search_path');
        $conn->statement("CREATE SCHEMA IF NOT EXISTS $search_path");
    }
}
