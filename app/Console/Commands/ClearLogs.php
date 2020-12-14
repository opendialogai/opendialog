<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear {--d|days=3} {--y|yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the OpenDialog logs older than the number of specified days (defaults to three).';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = $this->option("days");

        if (!is_numeric($days) || $days < 0) {
            $this->error(sprintf("%s is not a valid integer.", $days));
            return;
        }

        $confirmPreparationText = sprintf("Are you sure you want to prepare logs older than %d days for deletion?", $days);
        if (!$this->option('yes') && !$this->confirm($confirmPreparationText)) {
            return;
        }

        $thresholdDate = Carbon::now()->subDays($days)->setTime(0, 0, 0, 0);
        $this->info(sprintf("Looking up logs that are older than %s.", $thresholdDate->format("Y-m-d")));

        $files = Storage::disk("logs")->allFiles();
        $logFiles = collect($files)->mapWithKeys(function ($file) {
            $matches = [];
            $isMatch = preg_match("/^laravel\-(.*)\.log$/i", $file, $matches);

            if (count($matches) > 1) {
                $date = $matches[1];
            }

            $key = $isMatch ? $date : "";
            return [$key => $file];
        })->forget("");

        $filesForDeletion = $logFiles->filter(function ($value, $key) use ($thresholdDate) {
            try {
                $date = Carbon::parse($key);
            } catch (Exception $e) {
                return true;
            }

            return $date->isBefore($thresholdDate);
        });

        if (count($filesForDeletion) < 1) {
            $this->info("There are no log files to delete.");
            return;
        }

        if ($this->option('yes')) {
            $shouldDelete = true;
        } else {
            $shouldDelete = $this->confirm(sprintf("Are you sure you want to delete %d log files?", count($filesForDeletion)));
        }

        if ($shouldDelete) {
            Storage::disk("logs")->delete($filesForDeletion->values()->toArray());
            $this->info("Deletion complete.");
        } else {
            $this->info("Deletion skipped.");
        }
    }
}
