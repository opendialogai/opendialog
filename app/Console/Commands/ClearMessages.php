<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use OpenDialogAi\ConversationLog\Message;

class ClearMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:clear {--d|days=3} {--y|yes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears the Messages older than the number of specified days (defaults to three).';

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

        $confirmPreparationText = sprintf("Are you sure you want to prepare messages older than %d days for deletion?", $days);
        if (!$this->option("yes") && !$this->confirm($confirmPreparationText)) {
            return;
        }

        $thresholdDate = Carbon::now()->subDays($days)->setTime(0, 0, 0, 0);
        $this->info(sprintf("Looking up messages that are older than %s.", $thresholdDate->format("Y-m-d")));

        $messagesForDeletion = Message::where('microtime', '<', $thresholdDate->format("Y-m-d"));

        if ($messagesForDeletion->count() < 1) {
            $this->info("There are no messages to delete.");
            return;
        }

        if ($this->option("yes")) {
            $shouldDelete = true;
        } else {
            $shouldDelete = $this->confirm(
                sprintf("Are you sure you want to delete %d messages?", $messagesForDeletion->count())
            );
        }

        if ($shouldDelete) {
            $messagesForDeletion->delete();
            $this->info("Deletion complete.");
        } else {
            $this->info("Deletion skipped.");
        }
    }
}
