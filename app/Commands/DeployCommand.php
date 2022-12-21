<?php

namespace App\Commands;

use App\MetalFunctions;
use App\NginxConfig;
use Deployer\Command\CommandCommon;
use Deployer\Component\ProcessRunner\Printer;
use Deployer\Component\Ssh\Client;
use Deployer\Deployer;
use Deployer\Exception\Exception;
use Deployer\Executor\Worker;
use Deployer\Host\Host;
use Deployer\Host\Localhost;
use Deployer\Importer\Importer;
use Deployer\Logger\Handler\NullHandler;
use Deployer\Logger\Logger;
use Deployer\Task\Context;
use Deployer\Task\Task;
use Deployer\Utility\Rsync;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Deployer\currentHost;
use function Deployer\Support\is_closure;

class DeployCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Deploys your script to a node';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Deploying function');
        $mf = new MetalFunctions($this->getApplication(), $this->getOutput());

        $this->ssh = $mf->ssh();
        $this->rsync = $mf->rsync();
        $this->process = $mf->process();

        $localhost = new Localhost();
        Context::push(new Context($localhost));

        foreach (Deployer::get()->hosts as $hostname => $host) {
            $host->config()->load();
            Context::push(new Context($host));

            // Comprobar si estan creadas las carpetas del sistema Metal Functions
            $function_hash = substr(md5($host->get('function_url')), 0, 7);
            $metal_route = '/var/metal-functions/'.$function_hash;

            // Detect the version of PHP and NGINX if not ask the user to define one to use for Nginx.

            $user = $host->get('remote_user');
            $this->ssh->run($host, 'mkdir -p '.$metal_route.' && chown '.$user.':'.$user.' '.$metal_route);

            // Subir el directorio actual comprimido y descomprimirlo en el remoto
            $this->process->run($localhost, 'tar -czf file.tar.gz ./');

            $this->rsync->call($host, 'file.tar.gz', $user.'@'.$host->getHostname().':'.$metal_route);

            $this->process->run($localhost, 'rm file.tar.gz');

            $this->ssh->run($host, 'cd '.$metal_route.' && tar -xvzf file.tar.gz');
            $this->ssh->run($host, 'cd '.$metal_route.' && rm file.tar.gz');
            $this->ssh->run($host, 'chmod 755 '.$metal_route.' -R');

            $mf->addFunction($host, $function_hash);

            $this->info('... Function hash '.$function_hash.' : http://'.$hostname.$host->get('function_url'));
        }

        return 1;
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
