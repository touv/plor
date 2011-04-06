<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/..' );

// Load datas
$datas = file_exists('data.txt') ? unserialize(file_get_contents('data.txt')) : null;
if (!is_array($datas))
    $datas = array('projects'=> array(array('name'=> 'exemple', 'issues' => array(array('description' => 'this is a exemple')))));


require_once 'functions.php';

// Define Splitters
require_once 'REST/Url.php';
REST_Url::registerSplitter('index',
    function ($url, $sec) {
       if ($url == '') {
          $url .= 'index.xml';
          $sec->set('index');
       } elseif (preg_match('/^index/', $url)) {
          $sec->set('index');
       }
       return $url;
    }
);
REST_Url::registerSplitter('id',
    function ($url, $sec) {
        if (preg_match('/(^[0-9]+)/', $url, $m)) {
            $sec->set($m[1]);
       }
       return $url;
    }
);



// Define Resources
require_once 'REST/Url.php';
$projects = REST_Url::factory('/{index}.xml')
    ->addConstant('test', 'all callbacks can access to this content through the REST_Parameter class') 
    ->bindMethod('GET', 'list_of_projects_in_xml')
    ->bindMethod('POST', 'add_new_project', array('name'))
    ->bindMethod('OPTIONS', 'options');

$project = REST_Url::factory('/{id}.xml')
    ->bindParameter('*', 'enrich_params')
    ->bindMethod('GET', 'get_project_in_xml')
    ->bindMethod('DELETE', 'delete_project');

$issues = REST_Url::factory('/{id}/{index}.xml')
    ->bindMethod('GET', 'list_of_issues_in_xml')
    ->bindMethod('POST', 'add_new_issue', array(array('description', 'd', 'desc')));

$issue = REST_Url::factory('/{id}/{id}.(xml|html)')
    ->bindMethod('GET', 'get_issue')
    ->bindMethod('DELETE', 'delete_issue');

// Launch the server
require_once 'REST/Server.php';
$options = array(
    'base' => '/rest_server_test',
    );
REST_Server::factory($options)
    ->register($projects)
    ->register($project)
    ->register($issues)
    ->register($issue)
    ->listen();

// Save datas
file_put_contents('data.txt', serialize($datas));
