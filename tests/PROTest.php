<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PRO.php';

class ProTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
    }
    function tearDown()
    {
    }
    function test_array1()
    {
        $d = array(array(1,2,3));
        $h = '';
        $i = new PRO($d);
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
        $i = new PRO($d);
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
        $i = new PRO($d);
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
        $i = new PRO($d);
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
        $i = new PRO($d);
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
        $i = new PRO($d);
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
        $i = new PRO($d);
        while($row = $i->fetch()) {
            $h .= '['.$row->value.']';
        }
        $this->assertEquals($h, '[][0][][]');
    }




}

