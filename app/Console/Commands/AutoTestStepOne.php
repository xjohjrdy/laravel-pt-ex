<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoTestStepOne extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoTestStepOne';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command test for jump one';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        var_dump(1);
        sleep(10);
        var_dump(3);
    }
}
