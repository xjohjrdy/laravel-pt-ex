<?php

namespace App\Console\Commands;

use App\Entitys\App\PretendShopOrdersMaid;
use App\Services\Shop\Order;
use Illuminate\Console\Command;

class ClearUseless extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clearUseless';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command clearUseless';

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
        $this->info("start");
        $obj_maid = new PretendShopOrdersMaid();
        $this->info($obj_maid->clearUseless());
        $this->info("end");
    }
}
