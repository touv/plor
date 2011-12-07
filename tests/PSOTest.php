<?php
// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker :

ini_set('include_path', dirname(__FILE__).'/../plor'.PATH_SEPARATOR.ini_get('include_path'));

require_once 'PSO.php';

class PSOTest extends PHPUnit_Framework_TestCase
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

    public function test_duplicate()
    {
        $ss = $this->s->exchange('azerty')->duplicate()->exchange('qwzerty');
        $this->assertTrue($this->s->isEqual('azerty'));
        $this->assertTrue($ss->isEqual('qwzerty'));
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

    public function test_pad()
    {
        $this->s->exchange('XYZ')->pad('9', 'a');
        $this->assertTrue($this->s->isEqual('aaaXYZaaa'));
        $this->s->exchange('.Â.')->pad('9', 'a');
        $this->assertTrue($this->s->isEqual('aaa.Â.aaa'));
        $this->s->exchange('XYZ')->pad('9', 'Â');
        $this->assertTrue($this->s->isEqual('ÂÂÂXYZÂÂÂ'));
        $this->s->exchange('.Â.')->pad('9', 'Â');
        $this->assertTrue($this->s->isEqual('ÂÂÂ.Â.ÂÂÂ'));
    }

    public function test_lpad()
    {
        $this->s->exchange('XYZ')->lpad('9', 'a');
        $this->assertTrue($this->s->isEqual('aaaaaaXYZ'));
        $this->s->exchange('XYZ')->lpad('9', 'â');
        $this->assertTrue($this->s->isEqual('ââââââXYZ'));
        $this->s->exchange('éäè')->lpad('9', 'â');
        $this->assertTrue($this->s->isEqual('ââââââéäè'));
    }

    public function test_rpad()
    {
        $this->s->exchange('XYZ')->rpad('9', 'a');
        $this->assertTrue($this->s->isEqual('XYZaaaaaa'));
        $this->s->exchange('XYZ')->rpad('9', 'â');
        $this->assertTrue($this->s->isEqual('XYZââââââ'));
        $this->s->exchange('éäè')->rpad('9', 'â');
        $this->assertTrue($this->s->isEqual('éäèââââââ'));
    }

    public function test_urlencode()
    {
    }

    public function test_urldecode()
    {
    }

    public function test_fetch()
    {
        $this->s->exchange('Hello,How,Are,You,Today')->setEnding(',');
        $entries = array();
        while($entry = $this->s->fetch()) {
            $entries[] = $entry;
        }
        $this->assertEquals(count($entries), 5);
        $this->assertTrue($entries[0]->isEqual('Hello'));
        $this->assertTrue($entries[1]->isEqual('How'));
        $this->assertTrue($entries[2]->isEqual('Are'));
        $this->assertTrue($entries[3]->isEqual('You'));
        $this->assertTrue($entries[4]->isEqual('Today'));
    }
    public function test_fetchAll()
    {
        $this->s->exchange('Hello,How,Are,You,Today');
        $entries = $this->s->setEnding(',')->fetchAll();
        $this->assertEquals(count($entries), 5);
        $this->assertTrue($entries->fetch()->isEqual('Hello'));
        $this->assertTrue($entries->fetch()->isEqual('How'));
        $this->assertTrue($entries->fetch()->isEqual('Are'));
        $this->assertTrue($entries->fetch()->isEqual('You'));
        $this->assertTrue($entries->fetch()->isEqual('Today'));
    }
    public function test_map()
    {
        $entries = array();
        $this->s->exchange('Hello,How,Are,You,Today')->setEnding(',')->map(function($e) use (&$entries) {
            $entries[] = $e;
        });

        $this->assertEquals(count($entries), 5);
        $this->assertTrue($entries[0]->isEqual('Hello'));
        $this->assertTrue($entries[1]->isEqual('How'));
        $this->assertTrue($entries[2]->isEqual('Are'));
        $this->assertTrue($entries[3]->isEqual('You'));
        $this->assertTrue($entries[4]->isEqual('Today'));
    }

    public function test_bindValue()
    {
        $s = $this->s->exchange('Is ? a ? template \?')
            ->bindValue(1, 'it', PSO::PARAM_STR)
            ->bindValue(2, 'string', PSO::PARAM_STR)
            ->fire();
        $this->assertEquals('Is it a string template ?', $s->toString());

        $s = $this->s->exchange('Is :one a :two template ?')
            ->bindValue(':one', 'it', PSO::PARAM_STR)
            ->bindValue(':two', 'string', PSO::PARAM_STR)
            ->fire();
        $this->assertEquals('Is it a string template ?', $s->toString());

    }

    public function test_with()
    {
        $s = $this->s->exchange('Is ? a ? template \?')
            ->with(1, PSO::PARAM_STR)
            ->with(2, PSO::PARAM_STR)
            ->set(1, 'it')
            ->set(2, 'string')
            ->fire();
        $this->assertEquals('Is it a string template ?', $s->toString());

        $s = $this->s->exchange('Is :one a :two template ?')
            ->with(':one', PSO::PARAM_STR)
            ->with(':two', PSO::PARAM_STR)
            ->set(':one', 'it')
            ->set(':two', 'string')
            ->fire();
        $this->assertEquals('Is it a string template ?', $s->toString());
    }

    public function test_bind()
    {
        $p1 = '123';
        $p2 = 'abcd';
        $p3 = '123';
        $p4 = 'abcd';
        $s = $this->s->exchange('Is ? a ? template \?')
            ->bind(1, $p1, PSO::PARAM_INT)
            ->bind(2, $p2, PSO::PARAM_STR, 3)
            ->fire();
        $this->assertEquals('Is 123 a abc template ?', $s->toString());
        $this->assertEquals($p1, 123);
        $this->assertEquals($p2, 'abc');
        $s = $this->s->exchange('Is :one a :two template ?')
            ->bind(':one', $p3, PSO::PARAM_INT)
            ->bind(':two', $p4, PSO::PARAM_STR, 3)
            ->fire();
        $this->assertEquals('Is 123 a abc template ?', $s->toString());
        $this->assertEquals($p3, 123);
        $this->assertEquals($p4, 'abc');
    }

    public function test_asors()
    {
        $s = $this->s->exchange('x123');
        $s->asors('^[0-9]+$');
        $this->assertTrue($s->isEmpty());

        $s = $this->s->exchange('123');
        $s->asors('^[0-9]+$');
        $this->assertFalse($s->isEmpty());

        $s = $this->s->exchange('x123');
        $s->asors('^[0-9]+$', 10);

        $this->assertEquals('10', $s->toString());
    }
    public function test_tojson()
    {
        $this->assertNotNull(json_decode($this->s->exchange('éäè')->toJson()));
        $this->assertNotNull(json_decode($this->s->exchange('\'')->toJson()));
        $this->assertNotNull(json_decode($this->s->exchange('\n\r')->toJson()));
        $this->assertNotNull(json_decode($this->s->exchange('\\')->toJson()));
    }

    public function test_builder()
    {
        $src = '{"ok":true,"id":"_design/render","rev":"1-2041852709"}';
        $out = PSO::builder(json_decode($src, true));

       $this->assertEquals('1', $out->ok->toString());
       $this->assertEquals('_design/render', $out->id->toString());
       $this->assertEquals('1-2041852709', $out->rev->toString());
    }

    public function test_fromXML()
    {
        $src = '<PERSON>
            <NAME>John</NAME>
            <PHONE type="home">555-555-555</PHONE>
            </PERSON>';
        $out = PSO::fromXML($src);
        $this->assertEquals('John', $out->PERSON->NAME->_text->toString());
        $this->assertEquals('home', $out->PERSON->PHONE->type->toString());
        $this->assertEquals('555-555-555', $out->PERSON->PHONE->_text->toString());
        $src = '<authors>
            <author>
            <name>toto</name>
            <email>toto@example.com</email>
            </author>
            <author>
            <name>tata</name>
            <email>tata@example.com</email>
            </author>
            </authors>';
        $out = PSO::fromXML($src);
        $this->assertEquals('toto', $out->authors->author[0]->name->_text->toString());
        $this->assertEquals('toto@example.com', $out->authors->author[0]->email->_text->toString());
        $this->assertEquals('tata', $out->authors->author[1]->name->_text->toString());
        $this->assertEquals('tata@example.com', $out->authors->author[1]->email->_text->toString());

        $src = '<test>
            <info check="ok" />
            <note>X</note>
            </test>';
        $out = PSO::fromXML($src);
        $this->assertEquals('ok', $out->test->info->check->toString());
        $this->assertEquals('X', $out->test->note->_text->toString());


        $src = '<?xml version="1.0" encoding="UTF-8" ?>
<feed xmlns="http://www.w3.org/2005/Atom"
    xmlns:openSearch="http://a9.com/-/spec/opensearchrss/1.0/"
    xmlns:gd="http://schemas.google.com/g/2005"
    xmlns:gCal="http://schemas.google.com/gCal/2005">
  <id>...</id>
  <updated>2006-11-12T21:25:30.000Z</updated>
  <title type="text">Google Developer Events</title>
  <subtitle type="text">The calendar contains information about upcoming
    developer conferences at which Google will be speaking, along with
    other developer-related events.</subtitle>
  <link rel="http://schemas.google.com/g/2005#feed"
    type="application/atom+xml" href="..." />
  <link rel="self" type="application/atom+xml" href="..." />
  <author>
    <name>Google Developer Calendar</name>
    <email>developer-calendar@google.com</email>
  </author>
  <generator version="1.0"
    uri="http://www.google.com/calendar">Google Calendar</generator>
  <openSearch:startIndex>1</openSearch:startIndex>
  <openSearch:itemsPerPage>25</openSearch:itemsPerPage>
  <gCal:timezone value="America/Los_Angeles" />
    
  <entry>
    <id>...</id>
    <published>2006-11-12T21:25:30.000Z</published>
    <updated>2006-11-12T21:25:30.000Z</updated>
    <category scheme="..." term="..." />
    <title type="text">WebmasterWorld PubCon 2006:
      Google Developer Tools in General</title>
    <content type="text">Google is sponsoring at 
      <a href="http://www.pubcon.com/">WebmasterWorld PubCon 2006</a>. Come and
      visit us at the booth or join us for an evening demo reception where we
      will be talking "5 ways to enhance your website with Google Code". 
      After all, it is Vegas, baby! See you soon.</content>
    <link rel="alternate" type="text/html" href="..." title="alternate" />
    <link rel="self" type="application/atom+xml" href="..." />
    <author>
      <name>Google Developer Calendar</name>
      <email>developer-calendar@google.com</email>
    </author>
    <gCal:sendEventNotifications value="true" />
    <gd:comments>
       <gd:feedLink href="..." />
    </gd:comments>
    <gd:transparency value="..." />
    <gd:eventStatus value="..." />
    <gd:where valueString="3150 Paradise Road, Las Vegas, NV 89109" />
    <gd:when startTime="2006-11-15" endTime="2006-11-17">
      <gd:reminder minutes="10" />
    </gd:when>
  </entry>
  <entry>
  <id>...</id>
  </entry>
  <!-- etc. -->
</feed>
';
        $out = PSO::fromXML($src);
        echo $out->toJson();
    }
}
