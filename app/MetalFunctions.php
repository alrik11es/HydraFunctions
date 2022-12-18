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

    public function getHosts()
    {
        return Deployer::get()->hosts;
    }

    public function getConfig($name)
    {
        return Deployer::get()->config->get($name);
    }
}
