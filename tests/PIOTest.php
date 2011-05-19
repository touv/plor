<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PIO.php';

class PIOTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
    }
    function tearDown()
    {
    }
    function test_self()
    {
        for ($i = new PIO(__FILE__), $s = new PSO; $b = $i->fetch(); $s->concat($b));
        $this->assertTrue($s->contains('test_self'));
    }
 
}

