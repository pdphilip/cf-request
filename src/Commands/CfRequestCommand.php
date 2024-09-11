<?php

namespace PDPhilip\CfRequest\Commands;

use Illuminate\Console\Command;

class CfRequestCommand extends Command
{
    public $signature = 'cf-request';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
