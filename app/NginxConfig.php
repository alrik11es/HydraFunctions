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
        $default_config = <<<DOC
server {

    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root "/var/metal-functions";

    index index.php;

    charset utf-8;

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/metal-functions.log error;
    sendfile off;

    client_max_body_size 100m;

    location ~ \.php$ {
        include /etc/nginx/snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 1200;
        fastcgi_send_timeout 1200;
        fastcgi_read_timeout 1200;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;

        include /etc/nginx/fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
DOC;
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
