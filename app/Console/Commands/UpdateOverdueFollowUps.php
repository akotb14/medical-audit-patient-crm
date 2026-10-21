<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('followups:update-overdue')]
#[Description('Mark overdue follow-ups as missed')]
class UpdateOverdueFollowUps extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //update all follow ups of status is scheduled and date of due_date < now() to status is missed
        $count = FollowUp::query()->scheduled()->where('due_date', '<', now())->update(['status' => 'missed']);
        $this->info("Updated {$count} overdue follow-ups.");

        return self::SUCCESS;
    }
}
