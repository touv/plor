<?php
set_include_path(
    get_include_path()
    . PATH_SEPARATOR . dirname(__FILE__).DIRECTORY_SEPARATOR.'..'
    . DIRECTORY_SEPARATOR. '..' . DIRECTORY_SEPARATOR . 'pear'
    . PATH_SEPARATOR . dirname(__FILE__).DIRECTORY_SEPARATOR.'..'
    . PATH_SEPARATOR . dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'REST_Client'
);

require_once 'REST/EasyClient.php';

define('HOST', 'localhost');
if (strpos(__FILE__, 'thouveni') === FALSE) {
    define('PORT', 80);
} else {
    define('PORT', 8000);
}
define('BASE', 'rest_server_test');

unlink(sprintf('%s/../%s/data.txt', rtrim(dirname(__FILE__), '/'),BASE));

class Test extends PHPUnit_Framework_TestCase 
{
    protected $host = 'localhost';
    protected $rso;

    function setUp()
    {
        $this->rso = new REST_EasyClient(HOST, PORT);
    }
    function tearDown()
    {
        $this->rso = null;
    }
    function test_projets_options()    # OPTIONS / 
    {
        $rsp = $this->_options(200, $this->url('/'));
        $this->assertContains('GET,POST,OPTIONS', $rsp->headers['allow']);
    }
    function test_projets_get()    # GET / 
    {
        $rsp = $this->_get(200, $this->url('/'));
        $this->loadXML($rsp->content);
    }
    function test_projets_post()   # POST / 
    {
        $rsp = $this->_post(201, $this->url('/'), http_build_query(array('name'=>'zero')));
        $rsp = $this->_get(200, $rsp->headers['location']);
        $this->loadXML($rsp->content);
        $this->assertContains('<name>zero</name>', $rsp->content);
    }
    function test_projets_put()    # PUT / 
    {
        $rsp = $this->_put(405, $this->url('/'), '<fake/>');
    }
    function test_projets_delete() # DELETE / 
    {
        $rsp = $this->_delete(405, $this->url('/'), '<fake/>');
    }
    function test_xml_project_get()    # GET /{pid}.xml
    {
        $rsp = $this->_get(200, $this->url('/0.xml'));
        $this->loadXML($rsp->content);
    }
    function test_xml_project_post()   # POST /{pid}.xml
    {
        $rsp = $this->_post(405, $this->url('/0.xml'), '<fake/>');
    }
    function test_xml_project_put()    # PUT /{pid}.xml
    {
        $rsp = $this->_put(405, $this->url('/0.xml'), '<fake/>');
    }
    function test_xml_project_delete() # DELETE /{pid}.xml
    {
        $url = $this->url('/0.xml');
        $rsp = $this->_delete(204, $url);
        $rsp = $this->_get(404, $url);
    }
    function test_issues_get()    # GET /{pid}/
    {
        $rsp = $this->_post(201, $this->url('/'), http_build_query(array('name'=>'un')));
        $url = preg_replace('/\.xml$/','/', $rsp->headers['location']);
        $rsp = $this->_get(200, $url);
        $this->loadXML($rsp->content);
        $this->assertContains('<issues of=', $rsp->content);
    }
    function test_issues_post()   # POST /{pid}/
    {
        $rsp = $this->_post(201, $this->url('/1/'), http_build_query(array('description' => 'aaa')));
        $rsp = $this->_get(200, $rsp->headers['location']);
        $this->loadXML($rsp->content);
        $this->assertContains('<description>aaa</description>', $rsp->content);
        $rsp = $this->_post(201, $this->url('/1/'), http_build_query(array('description' => 'bbb')));
        $rsp = $this->_get(200, $rsp->headers['location']);
        $this->loadXML($rsp->content);
        $this->assertContains('<description>bbb</description>', $rsp->content);
    }
    function test_issues_put()    # PUT /{pid}/
    {
        $rsp = $this->_put(405, $this->url('/0/'), '<fake/>');
    }
    function test_issues_delete() # DELETE /{pid}/
    {
        $rsp = $this->_delete(405, $this->url('/0/'), '<fake/>');
    }
    function test_xml_issue_get()   # GET /{pid}/{iid}.xml
    {
        $rsp = $this->_get(200, $this->url('/1/0.xml'));
        $this->loadXML($rsp->content);
        $this->assertContains('<description>aaa</description>', $rsp->content);
    }
    function test_xml_issue_post()   # POST /{pid}/{iid}.xml
    {
        $rsp = $this->_post(405, $this->url('/1/0.xml'), '<fake/>');
    }
    function test_xml_issue_put()    # PUT /{pid}/{iid}.xml
    {
        $rsp = $this->_put(405, $this->url('/1/0.xml'), '<fake/>');
    }
    function test_xml_issue_delete() # DELETE /{pid}/{iid}.xml
    {
        $url = $this->url('/1/0.xml');
        $rsp = $this->_delete(204, $url);
        $rsp = $this->_get(404, $url);
    }
    function test_html_issue_post()   # POST /{pid}/{iid}.html
    {
        $rsp = $this->_post(405, $this->url('/1/1.html'), '<fake/>');
    }
    function test_html_issue_get()   # GET /{pid}/{iid}.html
    {
        $rsp = $this->_get(200, $this->url('/1/1.html'));
        $this->assertContains('<p>bbb</p>', $rsp->content);
    }
    function test_html_issue_put()    # PUT /{pid}/{iid}.html
    {
        $rsp = $this->_put(405, $this->url('/1/1.html'), '<fake/>');
    }
    function test_html_issue_delete() # DELETE /{pid}/{iid}.html
    {
        $url = $this->url('/1/1.html');
        $rsp = $this->_delete(204, $url);
        $rsp = $this->_get(404, $url);
    }

     private function _options($code, $path)
    {
        return $this->assertHTTP($this->rso->options($path), $code);
    }
    private function _delete($code, $path)
    {
        return $this->assertHTTP($this->rso->delete($path), $code);
    }
    private function _get($code, $path)
    {
        return $this->assertHTTP($this->rso->get($path), $code);
    }
    private function _post($code, $path, $data)
    {
        return $this->assertHTTP($this->rso->post($path, $data), $code);
    }
    private function _put($code, $path, $data)
    {
        return $this->assertHTTP($this->rso->put($path, $data), $code);
    }
    private function assertHTTP($rsp, $code)
    {
        $this->assertFalse($rsp->isError(), $rsp->error);
        $this->assertNotEquals(401, $rsp->code, 'Authorization Required');
        $this->assertContains('PRS', $rsp->headers['x-powered-by'], $rsp->content);
        if ($code != 200)
            $this->assertNotEquals(200, $rsp->code, $rsp->content);
        $this->assertEquals($code, $rsp->code, $rsp->content);

        return $rsp;
    }
    protected function loadXML($str)   
    {
        $dom = new DomDocument;
        $dom->loadXML($str);
        return $dom;
    }


    private function url($url)
    {
        return '/'.BASE.$url;
    }
}

