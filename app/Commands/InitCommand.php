<?php

namespace App\Commands;

use App\Nginx\NginxParser;
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

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Initialize metal functions node server on the host';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $config = new NginxParser('server');

        $location = new NginxParser('location','/');
        $location->setRoot('/route')
            ->setIndex(array('index.html', 'index.htm'));

        $config->setPort(80)
            ->setServerName(array('localhost','local','serveralias'))
            ->setAccessLog('/var/log/nginx/log/host.access.log')
            ->setLocation($location);

        $nginx = $config->build();
        xdebug_break();

        exit;


        $this->info('Simplicity is the ultimate sophistication.');

        $deployer = new Deployer($this->getApplication());
        $deployer->output = $this->output;

        /** @var Importer $importer */
        $importer = $deployer['importer'];
        $deployFile = 'metal.php';
        // Import recipe file
        if (is_readable($deployFile ?? '')) {
            $importer->import($deployFile);
        }
        $printer = new Printer($this->output);
        $logger = new Logger(new NullHandler());
        $ssh = new Client($this->output, $printer, $logger);
        $rsync = new Rsync($printer, $this->output);
        $process = Deployer::get()->processRunner;

        $localhost = new Localhost();
        Context::push(new Context($localhost));

        foreach ($deployer->hosts as $host) {
            $host->config()->load();
            Context::push(new Context($host));

            // Detectar la version de PHP y de NGINX si no pedir al usuario definición de la q hay que usar para Nginx.
            $result = $ssh->run($host, 'ls /etc/php/');
            $versions = explode("\n", $result);
            $versions = array_filter($versions);
            if (empty($versions)) {
               throw new Exception('Can\'t found PHP in /etc/php. You will need to specify the socket if it\'s installed anywhere else.');
            }

            $has_fpm = [];
            foreach($versions as $version) {
                $result = $ssh->run($host, 'ls /etc/php/'.$version.'');
                if(preg_match('/fpm/', $result)) {
                    $has_fpm[] = $version;
                }
            }

            if (empty($has_fpm)) {
                throw new Exception('php-fpm is not installed. Cannot work without that.');
            }

            $last_version = last($has_fpm);

            // Comprobar si estan creadas las carpetas del sistema Metal Functions
            $metal_route = '/var/metal-functions/';
            $user = $host->get('remote_user');
            $ssh->run($host, 'mkdir -p '.$metal_route.' && chown '.$user.':'.$user.' '.$metal_route);

            // Crear virtualhost de NGINX (que hay que ver como hacerlo) y enlace simbólico

            // Reiniciar NGINX gracefully

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
