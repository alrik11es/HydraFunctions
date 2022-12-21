<?php
namespace App;

use Deployer\Component\ProcessRunner\Printer;
use Deployer\Component\Ssh\Client;
use Deployer\Deployer;
use Deployer\Exception\Exception;
use Deployer\Importer\Importer;
use Deployer\Logger\Handler\NullHandler;
use Deployer\Logger\Logger;
use Deployer\Task\Context;
use Deployer\Utility\Rsync;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Style\OutputStyle;

class MetalFunctions
{
    public $printer;
    public $logger;

    public function __construct(Application $application, OutputStyle $output)
    {
        $deployer = new Deployer($application);
        Deployer::get()->output = $output;

        /** @var Importer $importer */
        $importer = $deployer['importer'];
        $deployFile = 'metal.php';
        // Import recipe file
        if (is_readable($deployFile ?? '')) {
            $importer->import($deployFile);
        }

        $this->printer = new Printer($output);
        $this->logger = new Logger(new NullHandler());
    }

    /**
     * @return mixed
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function getHosts()
    {
        return Deployer::get()->hosts;
    }

    public function getConfig($name)
    {
        return Deployer::get()->config->get($name);
    }

    public function ssh()
    {
        return new Client(Deployer::get()->output, $this->getPrinter(), $this->getLogger());
    }

    public function rsync()
    {
        return new Rsync($this->getPrinter(), Deployer::get()->output);
    }

    public function process()
    {
        return Deployer::get()->processRunner;
    }

    public function getHostLatestPhpVersion($host)
    {
        $result = $this->ssh()->run($host, 'ls /etc/php/');
        $versions = explode("\n", $result);
        $versions = array_filter($versions);
        if (empty($versions)) {
            throw new Exception('Can\'t found PHP in /etc/php. You will need to specify the socket if it\'s installed anywhere else.');
        }

        $has_fpm = [];
        foreach($versions as $version) {
            $result = $this->ssh()->run($host, 'ls /etc/php/'.$version.'');
            if(preg_match('/fpm/', $result)) {
                $has_fpm[] = $version;
            }
        }

        if (empty($has_fpm)) {
            throw new Exception('php-fpm is not installed. Cannot work without that.');
        }

        return last($has_fpm);
    }

    /**
     * @param Client $this->ssh
     * @param $host
     * @param $function_hash
     * @param $function_start_script
     * @param Rsync $rsync
     * @param $user
     * @return void
     * @throws Exception
     * @throws \Deployer\Exception\RunException
     * @throws \Deployer\Exception\TimeoutException
     */
    public function addFunction($host, $function_hash): void
    {
        $function_start_script = $host->get('function_start_script');
        $nginx_conf = $this->getNginxConfig($host);

        $nginx = new NginxConfig($nginx_conf);

        $nginx_conf = $nginx
            ->removeFunction($host->get('function_url'))
            ->addFunction($host->get('function_url'), '/' . $function_hash . '/' . $function_start_script)
            ->build();

        $this->saveNginxConfigAndRestart($host, $nginx_conf);
    }

    public function getNginxConfig($host): string
    {
        $function_start_script = $host->get('function_start_script');
        try {
            $nginx_conf = $this->ssh()->run($host, 'cat /etc/nginx/sites-available/metal-functions');
        } catch (\Exception $e) {
            $nginx_conf = null;
        }
        return $nginx_conf;
    }

    public function removeFunction($host, $function_hash): void
    {
        $nginx_conf = $this->getNginxConfig($host);
        $nginx = new NginxConfig($nginx_conf);

        $nginx_conf = $nginx
            ->removeFunction($host->get('function_url'))
            ->build();

        $this->saveNginxConfigAndRestart($host, $nginx_conf);
    }

    public function saveNginxConfigAndRestart($host, $nginx_conf): void
    {
        $user = $host->get('remote_user');
        file_put_contents('metal-functions', $nginx_conf);
        $this->rsync()->call($host, 'metal-functions', $user . '@' . $host->getHostname() . ':' . '/etc/nginx/sites-available');
        unlink('metal-functions');
        $this->ssh()->run($host, 'chown root:root /etc/nginx/sites-available/metal-functions');
        $this->ssh()->run($host, 'chmod 644 /etc/nginx/sites-available/metal-functions');

        // Check if there's another config enabled listening in port 80 without server_name (for all connections)

        // ASK The user if he wants to enable metal functions or not in the case that there's another listener

        $this->ssh()->run($host, 'ln -s /etc/nginx/sites-available/metal-functions /etc/nginx/sites-enabled');
        // Restart NGINX gracefully
        $this->ssh()->run($host, 'service nginx restart');
    }
}
