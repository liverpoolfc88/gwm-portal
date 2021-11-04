<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\II001FileController;

class sendFilesToGwmServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending II001, II002 files to GWM';

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
     * @return int
     */
    public function handle()
    {
        
        // DB::table('test')->delete();
        // echo "Operation done";
        //  return 0;

        $II001FileController = new II001FileController();
        $data = $II001FileController->filecreate('II001');
        $data1 = $II001FileController->filecreate('II002');
        $sts = $II001FileController->filesend('II001');
        $sts1 = $II001FileController->filesend('II002');



         
    }
}
