<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PHPUnit/Framework.php';
require_once 'PSOStream.php';

class PSOStreamTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
    }
    function tearDown()
    {
    }
    function test_open()
    {
        $r = fopen('pso://t1', 'r');
        $this->assertTrue(is_resource($r));
        $r = fclose($r);
        $this->assertTrue($r);
    }
    function test_write()
    {
        $h = fopen('pso://t2', 'w');
        $this->assertTrue(is_resource($h));
        $r = fwrite($h, '0123456789');
        $this->assertEquals($r, 10);
        $r = fclose($h);
        $this->assertTrue($r);
    }
    function test_read()
    {
        $h = fopen('pso://t3', 'w');

        $this->assertTrue(is_resource($h));
        $r = fwrite($h, 'azerty');

        $this->assertEquals($r, 6);
        $this->assertEquals(fread($h, 2), '');
        $r = fclose($h);
        $this->assertTrue($r);

        $h = fopen('pso://t3', 'r');
        $c = '';
        while (!feof($h)) {
            $c .= fread($h, 2);
        }
        $this->assertEquals($c, 'azerty');
        $this->assertEquals(fwrite($h, 'XXX'), 0);
        $r = fclose($h);
        $this->assertTrue($r);
    }
    function test_readfromPSO()
    {
        $s = new PSO('qsdfgh');
        $h = fopen($s->toURL(), 'r');
        $c = '';
        while (!feof($h)) {
            $c .= fread($h, 2);
        }
        $this->assertEquals($c, 'qsdfgh');
        $r = fclose($h);
        $this->assertTrue($r);
    }
    function test_writefromPSO()
    {
        $s = new PSO();
        $h = fopen($s->toURL(), 'w');
        $r = fwrite($h, 'wxcvbn');
        $this->assertEquals($r, 6);
        $r = fclose($h);
        $this->assertTrue($r);
        $this->assertEquals($s->toString(), 'wxcvbn');
    }


}

