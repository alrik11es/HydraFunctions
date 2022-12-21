<?php
namespace App;

use App\Nginx\Lexer;
use App\Nginx\Parser;

class NginxConfig
{
    public $parser;
    public $config;

    // TODO: Instead of loading in the constructor allow the user to load their own specific base config file
    // that could be used too as virtual host independent file, worth checking if it's possible

    public function __construct($string = null)
    {
        $default_config = MetalFunctions::loadTemplate('Templates/config.php');
        if($string) {
            $this->parse($string);
        } else {
            $this->parse($default_config);
        }
    }

    public function parse($string)
    {
        $lexer = new Lexer($string);
        $this->parser = new Parser($lexer);
        $this->config = $this->parser->parse();
    }

    public function build()
    {
        return $this->parser->build($this->config);
    }

    public function setPHPVersion($version)
    {
        // look for every fastcgi_pass and change the version
    }

    public function addFunction($function_url, $function_start_script)
    {
        $location = new \stdClass();
        $location->expr = 'location '.$function_url;
        $location->children[] = 'try_files $uri $uri/ '.$function_start_script.'?$query_string;';
        $this->config[0]->children[] = $location;
        return $this;
    }

    public function removeFunction($function_url)
    {
        $arr = $this->config;
        $this->removeRecursive($arr, $function_url);
        return $this;
    }

    public function removeRecursive(&$arr, $value)
    {
        foreach ($arr as $num => $line) {
            if (is_object($line)) {
                if (preg_match('%\\'.preg_quote($value).'%', $line->expr)) {
                    unset($arr[$num]);
                }
                $this->removeRecursive($line->children, $value);
            }
        }
    }

}
