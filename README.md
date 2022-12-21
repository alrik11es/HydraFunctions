# Metal Functions
A.K.A. MF, a CLI for FaaS like deployment in PHP metal servers for non experts.

## Use and intentions
This project aims to deploy multiple different functions in a server with Nginx and PHP. It will create the Nginx virtual hosts just for you. You will only have to know how to use this CLI. A MF could be a directory or a php single file.

This project is also compatible with [Deployer](https://deployer.org/) config files, we share some of the libraries so most of the Host configuration is the same.

## MF System requirements
* Any kind of Debian based system should do
* PHP >=8.0 with Nginx
* 512MB RAM

## CLI System requirements
* PHP >=8.0

## Build from source

Download and decompress this repo on any directory.

```
$ composer install
$ ./mf app:build
$ sudo mv builds/mf /usr/local/bin/mf
```

## Install

* In the remote host you need Nginx and PHP 7.4 installed on any host.
* In your system for the CLI, you require SSH access to a server with Nginx and PHP >=7.3.

First you will need to install MF.
```
$ wget https://github.com/alrik11es/releases/... (Not yet)
$ sudo mv mf /usr/local/bin/mf
```

Download the latest release from releases page.

### ON SERVER (FullStack if needed)
```
$ git clone https://github.com/alrik11es/MetalFunctions.git
$ cd MetalFunctions
$ ./installer.sh
```

### Using Metal Functions
Well, supossing that you already have a PHP >=7.3 with Nginx server then you can get MF.

First you will need to install MF.
```
$ wget https://github.com/alrik11es/releases/mf.phar
$ sudo mv mf.phar /usr/local/bin/mf
```


Create a directory. `$ mkdir hello-world` and create a file called `$ touch metal.php`. This file is going to be your settings file. You can add here the URL of the function and your start script. View the example as follows.

```php
<?php
namespace Deployer;

set('function_url', '/hello-world');
set('function_start_script', 'helloworld.php');

host('111.55.222.222')
    ->set('remote_user', 'ubuntu')
    ->set('become', 'root')
    ->set('identity_file', '~/.ssh/mykey');
```

Once you're done you can start using MF commands in the shell.

```
$ echo "<?php echo 'Hello world';" > helloworld.php
$ mf deploy
 ... Function name 89x7tya : http://ip/89x7tya
```

DONE.

Using my own [Basic library standard](https://www.msfsoftware.com/art%C3%ADculos/basic-library-standard)

## TODO

-[X] Host check for PHP and Nginx
-[X] Host check to enable metal functions in disabling default NGINX file
-[X] Deploy function
-[X] Delete function
-[ ] List functions
-[ ] Rollback a function
