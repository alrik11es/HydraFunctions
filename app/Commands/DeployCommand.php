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

        $ssh = $mf->ssh();
        $rsync = $mf->rsync();
        $process = $mf->process();

        $localhost = new Localhost();
        Context::push(new Context($localhost));

        foreach (Deployer::get()->hosts as $hostname => $host) {
            $host->config()->load();
            Context::push(new Context($host));
            // Detect the version of PHP and NGINX if not ask the user to define one to use for Nginx.

            $version = $mf->getHostLatestPhpVersion($host);

            // Comprobar si estan creadas las carpetas del sistema Metal Functions
            $function_hash = substr(md5($host->get('function_url')), 0, 7);
            $metal_route = '/var/metal-functions/'.$function_hash;
            $user = $host->get('remote_user');
            $function_start_script = $host->get('function_start_script');
            $ssh->run($host, 'mkdir -p '.$metal_route.' && chown '.$user.':'.$user.' '.$metal_route);

            // Subir el directorio actual comprimido y descomprimirlo en el remoto
            $process->run($localhost, 'tar -czf file.tar.gz ./');

            $rsync->call($host, 'file.tar.gz', $user.'@'.$host->getHostname().':'.$metal_route);

            $process->run($localhost, 'rm file.tar.gz');

            $ssh->run($host, 'cd '.$metal_route.' && tar -xvzf file.tar.gz');
            $ssh->run($host, 'cd '.$metal_route.' && rm file.tar.gz');
            $ssh->run($host, 'chmod 755 '.$metal_route.' -R');

            // Modificar virtualhost de NGINX

            $file_exist = $ssh->run($host, '[ ! -f /etc/nginx/sites_available/metal-functions ] && echo "1" || echo "0"');
            if(trim($file_exist)) {
                $nginx_conf = $ssh->run($host, 'cat /etc/nginx/sites-available/metal-functions');
            } else {
                $nginx_conf = null;
            }

            $nginx = new NginxConfig($nginx_conf);

            $nginx_conf = $nginx
                ->removeFunction($host->get('function_url'))
                ->addFunction($host->get('function_url'), '/'.$function_hash.'/'.$function_start_script)
                ->build();

            // Reiniciar NGINX gracefully
            xdebug_break();
            $this->info('... Function hash '.$function_hash.' : http://'.$hostname.$host->get('function_url'));
        }


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
