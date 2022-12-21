<?php
namespace App\Commands;

use App\MetalFunctions;
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

            $function_hash = substr(md5($host->get('function_url')), 0, 7);
            $metal_route = '/var/metal-functions/'.$function_hash;

            $result = $mf->ssh()->run($host, 'rm -rf '.$metal_route);

            $mf->removeFunction($host);
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
