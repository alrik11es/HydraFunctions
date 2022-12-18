<?php

namespace App;


use App\Nginx\Lexer;
use App\Nginx\Parser;

class NginxConfig
{
    public $parser;
    public $config;

    public function __construct($string)
    {
        $lexer = new Lexer($string);
        $this->parser = new Parser($lexer);
        $this->config = $this->parser->parse();
    }

    public function build()
    {
        return $this->parser->build($this->config);
    }

    public function addFunction($function_url, $function_start_script)
    {
        // Add location in virtual host
        $this->config[1][] = 'location '.$function_url;
        $this->config[1][] = ['try_files $uri $uri/ '.$function_start_script.'?$query_string;'];
        return $this;
    }

    public function removeFunction($function_url)
    {
        $this->removeRecursive($this->config, $function_url);
        return $this;
    }

    public function removeRecursive(&$config, $function_url)
    {
        foreach ($config as $num => $line) {
            if (is_array($line)) {
                $this->removeRecursive($line, $function_url);
            } elseif (preg_match('/\\'.preg_quote($function_url).'/', $line)) {
                unset($config[$num]);
                unset($config[$num+1]);
            }
        }
    }

}
