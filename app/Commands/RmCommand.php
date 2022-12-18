<?php
namespace App\Commands;

use App\MetalFunctions;
use App\Nginx\NginxParser;
use Deployer\Exception\Exception;
use Deployer\Task\Context;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class RmCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'rm';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove function from host';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mf = new MetalFunctions($this->getApplication(), $this->getOutput());

        foreach ($mf->getHosts() as $host) {
            $host->config()->load();
            Context::push(new Context($host));

            $metal_route = '/var/metal-functions/'.md5($host->get('function_url'));
            // Detectar la version de PHP y de NGINX si no pedir al usuario definición de la q hay que usar para Nginx.
            $result = $mf->ssh()->run($host, 'rm -rf '.$metal_route);

            // delete route from NGINX and
            // restart NGINX gracefully

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
