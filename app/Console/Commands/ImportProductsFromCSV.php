<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductImporterService;

class ImportProductsFromCSV extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'products:import {path : Path to the CSV file} {--test : Run in test mode without inserting}';

    /**
     * The console command description.
     */
    protected $description = 'Import products from a supplier CSV file with business rules and optional test mode';

    /**
     * Execute the console command to import csv file using test or production handler
     * @created 2025-10-04
     * @author Kareem Lorenzana
     * @params void
     * @return void
     */
    public function handle()
    {
        $path = $this->argument('path');
        $testMode = $this->option('test');

        $this->info("Starting import from: $path");
        if ($testMode) {
            $this->warn("Running in TEST mode. No data will be inserted.");
        }

        try {
            $importer = new ProductImporterService($path, $testMode);
            $result = $importer->import();

            $this->line("Import completed. :D");
            $this->line("Processed: {$result['processed']}");
            $this->line("Inserted: {$result['inserted']}");
            $this->line("Skipped: {$result['skipped']}");

            if (!empty($result['errors'])) {
                $this->error("Errors encountered:");
                foreach ($result['errors'] as $error) {
                    $this->line("- Row: " . implode(', ', $error['row']));
                    $this->line("  Error: " . $error['error']);
                }
            }

        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
        }
    }
}
