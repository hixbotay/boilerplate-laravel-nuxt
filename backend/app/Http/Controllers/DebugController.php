<?php

namespace App\Http\Controllers;

use App\Jobs\DeployJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DebugController extends Controller
{
    //
    public function deploy(Request $request){
        $this->dispatch(new DeployJob());
        exit('Deploy is scheduled'); 
    }

    public function info(){
        phpinfo();
        exit();
    }

    public function getLog(Request $request){
        if($request->key != 'duong'){
            exit();
        }
        $log_file = str_replace('|','/',$request->log);
        $path = storage_path("logs/");
        $log_file = $path . $log_file;
        echo $log_file;
        echo '<div style="white-space:pre-wrap;width:100%;overflow-wrap: break-word;">';
        print_r(file_get_contents($log_file));
        echo '</div>';
        exit();
    }
}
