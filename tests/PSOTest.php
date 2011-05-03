<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PHPUnit/Framework.php';
require_once 'PSO.php';

class PSOStreamTest extends PHPUnit_Framework_TestCase
{
    protected $s;
    function setUp()
    {
        $this->s = new PSO;
    }
    function tearDown()
    {
        $this->s = null;
    }
    public function test_toString()
    {
        $this->s->exchange('azerty');
        $this->assertEquals($this->s->toString(), 'azerty');
    }

    public function test_toInteger()
    {
        $this->s->exchange('1001');
        $this->assertEquals($this->s->toInteger(), 1001);
    }

    public function test_toBoolean()
    {
        $this->s->exchange('1');
        $this->assertTrue($this->s->toBoolean());
        $this->s->exchange('0');
        $this->assertFalse($this->s->toBoolean());
    }

    public function test_toURL()
    {
    }

    public function test_isEmpty()
    {
        $this->assertTrue($this->s->isEmpty());
    }


    public function test_isEqual()
    {
        $this->s->exchange('azerty');
        $this->assertTrue($this->s->isEqual('azerty'));
    }

    public function test_isMatch()
    {
    }

    public function test_contains()
    {
        $this->s->exchange('azerty');
        $this->assertTrue($this->s->contains('zert'));
    }

    public function test_replace()
    {
    }

    public function test_slice()
    {
    }

    public function test_substr()
    {
    }

    public function test_concat()
    {
    }

    public function test_upper()
    {
        $this->s->exchange('azerty');
        $this->assertEquals((string)$this->s->upper(), 'AZERTY');
    }

    public function test_lower()
    {
        $this->s->exchange('AZERTY');
        $this->assertEquals((string)$this->s->lower(), 'azerty');
    }

    public function test_title()
    {
        $this->s->exchange('AZERTY');
        $this->assertEquals((string)$this->s->title(), 'Azerty');
    }

    public function test_md5()
    {
    }

    public function test_trim()
    {
    }

    public function test_ltrim()
    {
    }

    public function test_rtrim()
    {
    }

    public function test_urlencode()
    {
    }

    public function test_urldecode()
    {
    }
}
