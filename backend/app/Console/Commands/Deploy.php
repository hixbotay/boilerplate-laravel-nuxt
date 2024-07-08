<?php

namespace App\Console\Commands;

use App\Helpers\LogHelper;
use Illuminate\Console\Command;
use Exception;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class Deploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:code {branch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy code';

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
        $this->log("Start deploy");
        $git_url = 'git@github.com:zero2603/sellonboard.git'; 
        $git_folder = public_path('gitclone');
        $src_folder = dirname(dirname($git_folder));
        echo "project source: $src_folder \n";
        // $src_folder = public_path('test'); 
        $git_branch = $this->argument('branch') ? $this->argument('branch') : 'phase_1';
        try{ 
            //delete git src
            if(is_dir($git_folder)){
                $this->log("Remove existing git src");
                $process = Process::fromShellCommandline("rm -rf {$git_folder}");
                $process->mustRun();
                echo $process->getOutput();
            }
            //set ssh key if call from url
            //     echo 'setup ssh key'.PHP_EOL;
            //     $ssh_path = public_path('id_ed25519');
            //     $process = Process::fromShellCommandline("git config core.sshCommand 'ssh -i {$ssh_path}'");
            //     $process->mustRun();
            //     echo $process->getOutput();
            $ssh_path = $src_folder.'/id_ed25519';
            
            // //git clone
            $this->log("Start clone git");
            $process = Process::fromShellCommandline("git -c core.sshCommand='ssh -i {$ssh_path}' clone {$git_url} {$git_folder} --branch {$git_branch}");
            $process->setWorkingDirectory($src_folder);
            $process->mustRun();
            
            // $copy_cmd = "git clone {$git_url} {$git_folder} --branch {$git_branch}";
            // $process = Process::fromShellCommandline($copy_cmd);
            // $process->setWorkingDirectory($src_folder);
            // $process->mustRun();

            $output = $process->getOutput();
            echo $output;
            $this->log("git clone result: ".$output);
            // copy git to real src
            $process = Process::fromShellCommandline("\\cp -r {$git_folder}/* {$src_folder}");
            $process->mustRun();
            echo "\nCopy source success ".$process->getOutput();  
            $this->log("Copy source git");
            //remove git src to free disk
            $process = Process::fromShellCommandline("rm -rf {$git_folder}");
            $process->mustRun();
            echo "\nRemove trash success ".$process->getOutput();
            $this->log("Remove trash");
            //composer install
            $process = Process::fromShellCommandline("composer install");
            $process->setWorkingDirectory($src_folder);
            $process->mustRun();
            echo "\nComposer install success ".$process->getOutput();
            //call migrate
            Artisan::call("migrate");
            echo "\nMigrate database ".Artisan::output();
			
            if(env('APP_ENV') == 'stg'){
                echo "Remove ".$src_folder.'/public/ecwid'.' '.File::deleteDirectories($src_folder.'/public/ecwid');
                echo "\n Move folder ".$src_folder.'/public/ecwid_stg'.' => '.$src_folder.'/public/ecwid '.File::copyDirectory($src_folder.'/public/ecwid_stg',$src_folder.'/public/ecwid');
            }

            //linux permissino
            $process = Process::fromShellCommandline("chown -R nginx:centos {$src_folder}");
            $process->mustRun();
			echo "\n Permission nginx:centos {$src_folder} ".$process->getOutput();  
            $process = Process::fromShellCommandline("chmod -R 774 {$src_folder}");
            $process->mustRun();
			echo "\n Permission 774 {$src_folder} ".$process->getOutput();  
            echo "\nDone\n";
            
        }catch (Exception $exception){
            echo $exception->getMessage();
            $this->log($exception->getMessage());
        }
        
        return 0;
    }

    private function log($txt){
        LogHelper::write_log('deploy.txt',$txt);
    }
}
