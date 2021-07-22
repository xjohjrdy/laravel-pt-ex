<?php

namespace App\Console\Commands;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleRingAdd;
use App\Services\Shop\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Uu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uu';

    protected $testObj;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command uu';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $testObj)
    {
        parent::__construct();
        $this->testObj = $testObj;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        

    }
}
