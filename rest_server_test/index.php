<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/../lib' );

// Load datas
$datas = file_exists('data.txt') ? unserialize(file_get_contents('data.txt')) : null;
if (!is_array($datas))
    $datas = array('projects'=> array(array('name'=> 'exemple', 'issues' => array(array('description' => 'this is a exemple')))));


require_once 'functions.php';
require_once 'translaters.php';


require_once 'PRSUrl.php';


// Define Resources
$projects = PRSUrl::factory('/{index}.xml')
    ->translate('index', 'translaters_index')
    ->addConstant('test', 'all callbacks can access to this content through the PRSParameter class') 
    ->bindMethod('GET', 'list_of_projects_in_xml')
    ->bindMethod('POST', 'add_new_project', array('name'))
    ->bindMethod('OPTIONS', 'options');

$project = PRSUrl::factory('/{id}.xml')
    ->translate('id', 'translaters_id')
    ->bindParameter('*', 'enrich_params')
    ->bindMethod('GET', 'get_project_in_xml')
    ->bindMethod('DELETE', 'delete_project');

$issues = PRSUrl::factory('/{id}/{index}.xml')
    ->translate('id', 'translaters_id')
    ->translate('index', 'translaters_index')
    ->bindMethod('GET', 'list_of_issues_in_xml')
    ->bindMethod('POST', 'add_new_issue', array(array('description', 'd', 'desc')));

$issue = PRSUrl::factory('/{id}/{id}.(xml|html)')
    ->translate('id', 'translaters_id')
    ->bindMethod('GET', 'get_issue')
    ->bindMethod('DELETE', 'delete_issue');

// Launch the server
require_once 'PRS.php';
$options = array(
    'base' => '/rest_server_test',
    );
$app = PRS::factory($options);
$app[] = $projects;
$app[] = $project;
$app[] = $issues;
$app[] = $issue;
$app->listen();

// Save datas
file_put_contents('data.txt', serialize($datas));
