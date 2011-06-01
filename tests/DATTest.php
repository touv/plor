<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'DAT.php';

class DATTest extends PHPUnit_Framework_TestCase
{
    protected $data;
    function setUp()
    {
    }
    function tearDown()
    {
    }
    function test_0()
    {
        $h = '';
        $i = DAT::factory()
            ->add('test', 12345)
            ->add('test', 67890);

//        printf("%s %s %6s %-20s %s %s\n", 'index', 'depth', 'type', 'name', 'uri', 'value');
        while($row = $i->fetch()) {
            $h .= $row->value;
//            printf("%5d %5d %6s %-20s %s %s\n", $r->index, $r->depth, $r->type, $r->name, $r->uri, $r->value);
        }
        $this->assertEquals($h, '1234567890');
        $this->assertTrue($i->splice()->isEqual('1234567890'));

    }
    function test_2()
    {
        $d = array(array(1,2,3));
        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= $row->value;
        }
        $this->assertEquals($h, '123');
        $this->assertTrue($i->splice()->isEqual('123'));

    }
    function test_array2()
    {
        $d = array('a', 'b', 'c');
        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= $row->value;
        }
        $this->assertEquals($h, 'abc');
        $this->assertTrue($i->splice()->isEqual('abc'));
    }
    function test_array3()
    {
        $d = array('x'=>array(1,2,3));
        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= $row->value;
        }
        $this->assertEquals($h, '123');
        $this->assertTrue($i->splice()->isEqual('123'));
    }
    function test_array4()
    {
        $d = array(array(1,2,3), array(4,5,6));
        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= $row->value;
        }
        $this->assertEquals($h, '123456');
        $this->assertTrue($i->splice()->isEqual('123456'));
    }
    function test_array5()
    {
        $d = array(
            "x" => array('1', '2', '3'),
            "y" => array(4, 5, 6),
        );

        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= $row->value;
        }
        $this->assertEquals($h, '123456');
        $this->assertTrue($i->splice()->isEqual('123456'));
    }
    function test_array6()
    {
        $d = array(
            "y" => array(0, 1, 2),
            "x" => array('3', '4', '5'),
        );

        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= $row->value;
        }
        $this->assertEquals($h, '012345');
        $this->assertTrue($i->splice()->isEqual('012345'));
    }
    function test_array7()
    {
        $d = array(
            null, 0, null, ''
        );

        $h = '';
        $i = new DAT($d);
        while($row = $i->fetch()) {
            $h .= '['.$row->value.']';
        }
        $this->assertEquals($h, '[][0][][]');
    }




}

