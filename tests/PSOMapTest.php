<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PSOMap.php';

class PSOMapTest extends PHPUnit_Framework_TestCase
{
    protected $data;
    function setUp()
    {
    }
    function tearDown()
    {
    }
    function test_set()
    {
        $h = '';
        $i = PSOMap::factory()
            ->set('a', new PSO('1'))
            ->set('b', new PSO('2'))
            ->set('c', new PSO('3'))
            ->set('d', new PSO('4'));

        while($row = $i->fetch()) {
            $h .= $row;
        }
        $this->assertEquals($h, '1234');
        $this->assertTrue($i->splice()->isEqual('1234'));
    }

    function test_set2()
    {
        $h = '';
        $i = PSOMap::factory();
        $i->a = new PSO('1');
        $i->b = new PSO('2');
        $i->c = new PSO('3');
        $i->d = new PSO('4');

        while($row = $i->fetch()) {
            $h .= $row;
        }
        $this->assertEquals($h, '1234');
        $this->assertTrue($i->splice()->isEqual('1234'));
    }

    function test_get()
    {
        $i = PSOMap::factory()
            ->set('a', new PSO('1'))
            ->set('b', new PSO('2'))
            ->set('c', new PSO('3'))
            ->set('d', new PSO('4'));

        $this->assertTrue($i->get('a')->isEqual('1'));
        $this->assertTrue($i->get('b')->isEqual('2'));
        $this->assertTrue($i->get('c')->isEqual('3'));
        $this->assertTrue($i->get('d')->isEqual('4'));
    }

    function test_get2()
    {
        $i = PSOMap::factory();
        $i->a = new PSO('1');
        $i->b = new PSO('2');
        $i->c = new PSO('3');
        $i->d = new PSO('4');

        $this->assertTrue($i->a->isEqual('1'));
        $this->assertTrue($i->b->isEqual('2'));
        $this->assertTrue($i->c->isEqual('3'));
        $this->assertTrue($i->d->isEqual('4'));
    }


    function test_del()
    {
        $h = '';
        $i = PSOMap::factory()
            ->set('a', new PSO('1'))
            ->set('b', new PSO('2'))
            ->set('c', new PSO('3'))
            ->set('d', new PSO('4'));

        $i->del('b');
        $i->del('d');

        while($row = $i->fetch()) {
            $h .= $row;
        }
        $this->assertEquals($h, '13');
        $this->assertTrue($i->splice()->isEqual('13'));
    }


}

