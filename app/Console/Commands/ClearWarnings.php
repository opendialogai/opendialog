<?php

namespace App\Console\Commands;

use App\Warning;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearWarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warnings:clear {--d|days=3} {--y|yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the Warnings older than the number of specified days (defaults to three).';

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

        $confirmPreparationText = sprintf("Are you sure you want to prepare warnings older than %d days for deletion?", $days);
        if (!$this->option("yes") && !$this->confirm($confirmPreparationText)) {
            return;
        }

        $thresholdDate = Carbon::now()->subDays($days)->setTime(0, 0, 0, 0);
        $this->info(sprintf("Looking up warnings that are older than %s.", $thresholdDate->format("Y-m-d")));

        $warningsForDeletion = Warning::where('created_at', '<', $thresholdDate->format("Y-m-d"));

        if ($warningsForDeletion->count() < 1) {
            $this->info("There are no warnings to delete.");
            return;
        }

        if ($this->option("yes")) {
            $shouldDelete = true;
        } else {
            $shouldDelete = $this->confirm(
                sprintf("Are you sure you want to delete %d warnings?", $warningsForDeletion->count())
            );
        }

        if ($shouldDelete) {
            $warningsForDeletion->delete();
            $this->info("Deletion complete.");
        } else {
            $this->info("Deletion skipped.");
        }
    }
}
