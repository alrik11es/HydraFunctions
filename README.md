# MetalFunctions
A.K.A. MF, a rapid FaaS deployment system for non experts.
[Basic library standard](https://www.msfsoftware.com/art%C3%ADculos/basic-library-standard)

## Intentions
This is created to cover a specific usage case. Where you have servers available under your control. But somehow you need to deploy applications on those servers that only you are going to use.

## System requirements
* Any kind of Debian based system with PHP 8.0+
* 512MB RAM

## Install

Download the latest release from releases page.

```
$ wget latest
$ sudo mv mf.phar /usr/local/bin/phf
```

On the server
```
$ mf install new node
installing...
hash:2189347yr9f7as
key:298f89982173197
```

In the client
$ mf

## TODO Requirements
- [ ] MF COULD be installed in a raspberry pi.
- [ ] MF MUST be able to be operated by people that don't understand server architecture or more than basic server commands.
- [ ] This system MUST be able to deploy into a new server with composer.
- [ ] Operating system WIDE.
- [ ] MF

