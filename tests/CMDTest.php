<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'CMD.php';

class CMDTest extends PHPUnit_Framework_TestCase
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
            ->linkStream(1, '/tmp/t.txt')            
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
            ->linkStream(1, $out->toURL())
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
    function test_f()
    {
        $out = new PSO;
        CMD::factory('/bin/ls --all -b --color=? --format=? -t --reverse ?')
            ->bindValue(1, 'never')
            ->bindValue(2, 'single-column')
            ->bindValue(3, '/usr')
            ->linkStream(1, $out->toURL())
            ->fire();
        $s = $out->toString();
        $this->assertTrue(strpos($s, 'bin') !== false);
    }
    function test_g()
    {
        $out = new PSO;
        CMD::factory('/bin/ls --all -b --color=:color --format=:format -t --reverse :path')
            ->bindValue(':color', 'never')
            ->bindValue(':format', 'single-column')
            ->bindValue(':path', '/usr')
            ->linkStream(1, $out->toURL())
            ->fire();
        $s = $out->toString();
        $this->assertTrue(strpos($s, 'bin') !== false);
    }
    function test_h()
    {
        $out = new PSO;
        CMD::factory('
            /bin/ls --all 
                    -b 
                    --color=:color 
                    --format=:format 
                    -t 
                    --reverse 
                    :path
            ')
            ->bindValue(':color', 'never')
            ->bindValue(':format', 'single-column')
            ->bindValue(':path', '/usr')
            ->linkStream(1, $out->toURL())
            ->fire();
        $s = $out->toString();
        $this->assertTrue(strpos($s, 'bin') !== false);
    }

    function test_e()
    {
        $c = CMD::factory('/usr/bin/curl', array('long_option_operator' => ' '))
            ->option('no-buffer')
            ->option('request', 'GET')
            ->param('http://www.google.fr')
            ->linkStream(2, '/dev/null')
            ->fire();

        $this->assertTrue($c->fetchAll()->splice()->contains('</script>'));
    }

}
