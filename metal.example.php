<?php
namespace Deployer;

set('function_url', '/hello-world');
set('function_start_script', 'helloworld.php');

host('123.23.24.54')
    ->set('remote_user', 'ubuntu')
    ->set('become', 'root')
    ->set('identity_file', '~/.ssh/key');
