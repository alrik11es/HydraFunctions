# MetalFunctions
A.K.A. MF, a rapid FaaS deployment system for non experts.
[Basic library standard](https://www.msfsoftware.com/art%C3%ADculos/basic-library-standard)

## Intentions
This is created to cover a specific usage case. Where you have servers available under your control. But somehow you need to deploy applications on those servers that only you are going to use.

## Node System requirements
* Any kind of Debian based system
* PHP >=8.0 with Nginx
* 512MB RAM

## CLI System requirements
* PHP >=8.0

## Install

* Fullstack install. I've prepared a handy script (Based on Laravel Homestead) that deploys a complete PHP 8.0 + Nginx suite ready to work with MetalFunctions. Useful to prepare your VPS or server.
* Normal install. Needed only on the client, you require SSH access to a server with Nginx and PHP 8.0.

Download the latest release from releases page.

### ON SERVER (FullStack if needed)
```
$ git clone https://github.com/alrik11es/MetalFunctions.git
$ cd MetalFunctions
$ ./installer.sh
```

### CLI
```
$ wget https://github.com/alrik11es/releases/mf.phar
$ sudo mv mf.phar /usr/local/bin/mf
$ mf node nodename:ip:/home/user/.ssh/id_rsa.key # stores in a local file the connection details
$ mf add function
 ... Function name 89x7tya : http://ip/89x7tya
$ mf deploy # deploys the actual directory
```