<?php

namespace CleaniqueCoders\AppPulse\Commands;

use Illuminate\Console\Command;

class AppPulseCommand extends Command
{
    public $signature = 'app-pulse';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
