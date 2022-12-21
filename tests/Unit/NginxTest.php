<?php
namespace Tests\Unit;

use App\NginxConfig;
use Tests\TestCase;

class NginxTest extends TestCase
{
    public function nginxProvider()
    {
        return [
            [
                file_get_contents(dirname(__FILE__).'/nginx_testcases/basic.conf'),
                file_get_contents(dirname(__FILE__).'/nginx_testcases/result.conf'),
            ],
        ];
    }

    /**
     * @dataProvider nginxProvider
     */
    public function testAddFunctionToNginxConfig($start, $result)
    {
        $nginx = new NginxConfig($start);
        $final = $nginx->addFunction('/hello-world', '/helloworld.php')->build();
        $this->assertEquals($result, $final);
    }

    public function nginxRemoveProvider()
    {
        return [
            [
                file_get_contents(dirname(__FILE__).'/nginx_testcases/case_remove_start.conf'),
                file_get_contents(dirname(__FILE__).'/nginx_testcases/case_remove_end.conf'),
            ],
        ];
    }

    /**
     * @dataProvider nginxRemoveProvider
     */
    public function testRemoveFunctionToNginxConfig($start, $result)
    {
        $nginx = new NginxConfig($start);
        $final = $nginx->removeFunction('/hello-world')->build();
        $this->assertEquals($result, $final);
    }
}
