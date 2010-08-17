<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/thouveni/devel/rest_server');

// Load datas
$datas = file_exists('data.txt') ? unserialize(file_get_contents('data.txt')) : null;
if (!is_array($datas))
    $datas = array('projects'=> array(array('name'=> 'exemple', 'issues' => array(array('description' => 'this is a exemple')))));


require_once 'functions.php';


// Define Resources
require_once 'REST/Resource.php';
$projects = REST_Resource::index('xml');
$projects
    ->addAction('GET', 'list_of_projects_in_xml')
    ->addAction('POST', 'add_new_project', array('name'));

$project = REST_Resource::leaf('xml');
$project
    ->addAction('GET', 'get_project_in_xml')
    ->addAction('DELETE', 'delete_project');

$issues = REST_Resource::index('xml');
$issues
    ->addAction('GET', 'list_of_issues_in_xml')
    ->addAction('POST', 'add_new_issue', array('description'));

$issue_xml = REST_Resource::leaf('xml');
$issue_xml
    ->addAction('GET', 'get_issue_in_xml')
    ->addAction('DELETE', 'delete_issue');

$issue_html = REST_Resource::leaf('html');
$issue_html
    ->addAction('GET', 'get_issue_in_html')
    ->addAction('DELETE', 'delete_issue');

// Launch the server
require_once 'REST/Server.php';
$server = new REST_Server();
$server->root()
    ->append($projects)
    ->append($project)
    ->createLevel()
        ->append($issues)
        ->append($issue_xml)
        ->append($issue_html);
$server->handle();

// Save datas
file_put_contents('data.txt', serialize($datas));
