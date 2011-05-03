<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PHPUnit/Framework.php';
require_once 'CMD.php';


class PSOStreamTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
    }
    function tearDown()
    {
    }
    function test_a()
    {
        $s = CMD::factory('/bin/ls')
            ->option('all')
            ->option('b')
            ->option('color', 'never')
            ->option('format', 'single-column')
            ->option('t')
            ->option('reverse')
            ->param('/usr')
            ->fire()
            ->fetchAll()
            ->toString();

        $this->assertTrue(strpos($s, 'bin') !== false);
    }
    function test_b()
    {
        CMD::factory('/bin/ls')
            ->option('all')
            ->param('/usr')
            ->bind(1, '/tmp/t.txt')            
            ->fire();
        $s = file_get_contents('/tmp/t.txt');
        $this->assertTrue(strpos($s, 'bin') !== false);
    }
    function test_c()
    {
        $out = new PSO;
        CMD::factory('/bin/ls')
            ->option('all')
            ->option('b')
            ->option('color', 'never')
            ->option('format', 'single-column')
            ->option('t')
            ->option('reverse')
            ->param('/usr')
            ->bind(1, $out->toURL())
            ->fire();
        $s = $out->toString();
        $this->assertTrue(strpos($s, 'bin') !== false);
    }
    function test_d()
    {
        $c = CMD::factory('/bin/sleep')
            ->param('1')
            ->fire();
        $this->assertTrue($c->isAlive());
        usleep(1200000);
        $this->assertFalse($c->isAlive());
    }
    function test_e()
    {
        $c = CMD::factory('/usr/bin/curl', array('long_option_operator' => ' '))
            ->option('no-buffer')
            ->option('request', 'GET')
            ->param('http://www.google.fr')
            ->bind(2, '/dev/null')
            ->fire();

        for ($s = ''; $b = $c->fetch(); $s .= $b);
        $this->assertTrue(strpos($s, '</script>') !== false);
    }
}
