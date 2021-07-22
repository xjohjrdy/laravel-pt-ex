<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class testComm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testComm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command testComm';

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
        $this->info("hello");
        $time = date('y-m-d h:i:sa',time());
        $this->info($time);
        file_put_contents("/www/web/putao_api/public_html/log.txt",var_export($time,true).PHP_EOL,FILE_APPEND);
    }
}
