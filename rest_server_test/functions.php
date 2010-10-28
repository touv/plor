<?php
 
function list_of_projects_in_xml($p, $h)
{
    global $datas;
    echo '<projects>';
    foreach($datas['projects'] as $project_id => $projet)  {
        echo '<projet>';
        echo '<id>',$project_id,'</id>';
        echo '<name>',$projet['name'],'</name>';
        echo '</projet>';
    }
    echo '</projects>';
    $h->send(200);
}

function add_new_project($p, $h)
{
    global $datas;
    if (is_null($p->name)) 
        return $h->send(400, true);

    if (!isset($datas['projects']))
        return $h->send(500, true);

    $project_id = count($datas['projects']);
    $datas['projects'][] = array(
        'name' => preg_replace('/[^\w]*/', '', $p->name),
        'issues' => array(),
    );
    $h
        ->add('Location', '/'.$project_id.'.xml')
        ->send(201, true);
}
function get_project_in_xml($p, $h)
{
    global $datas;
    $project_id = $p->section()->value();   
    if (!isset($datas['projects'][$project_id]))
        return $h->send(404, true);
    echo '<projet>';
    echo '<id>',$project_id,'</id>';
    echo '<name>',$datas['projects'][$project_id]['name'],'</name>';
    echo '</projet>';
    $h->send(200);
}
function delete_project($p, $h)
{
    global $datas;
    $project_id = $p->section()->value();
    unset($datas['projects'][$project_id]);
    $h->send(204, true);
}
function list_of_issues_in_xml($p, $h)
{
    global $datas;
    $project_id = $p->section(0)->value();
    if (!isset($datas['projects'][$project_id]))
        return $h->send(404, true);

    echo '<issues of="'.$project_id.'">';
    foreach($datas['projects'][$project_id]['issues'] as $issue_id => $issue)  {
        echo '<issue>';
        echo '<id>',$issue_id,'</id>';
        echo '<description>',$issue['description'],'</description>';
        echo '</issue>';
    }
    echo '</issues>';
    $h->send(200);
}
function add_new_issue($p, $h)
{
    global $datas;
    $project_id = $p->section(0)->value();
    if (!isset($datas['projects'][$project_id]))
        return $h->send(404, true);

    if (is_null($p->description)) 
        return $h->send(400, true);

    $issue_id = count($datas['projects'][$project_id]);
    $datas['projects'][$project_id]['issues'][] = array(
        'description' => $p->description,
    );
    $h
        ->add('Location', '/'.$project_id.'/'.$issue_id.'.xml')
        ->send(201, true);
}
function get_issue_in_xml($p, $h)
{
    global $datas;
    $project_id = $p->section(0)->value();
    $issue_id = $p->section(1)->value();
    if (!isset($datas['projects'][$project_id]))
        return $h->send(404, true);
    if (!isset($datas['projects'][$project_id]['issues'][$issue_id]))
        return $h->send(404, true);

    echo '<issue>';
    echo '<id>',$issue_id,'</id>';
    echo '<description>',$datas['projects'][$project_id]['issues'][$issue_id]['description'],'</description>';
    echo '</issue>';
    $h->send(200);
}
function delete_issue($p, $h)
{
    global $datas;
    $project_id = $p->section(0)->value();
    $issue_id = $p->section(1)->value();
    unset($datas['projects'][$project_id]['issues'][$issue_id]);
    $h->send(204, true);
}
function get_issue_in_html($p, $h)
{
    global $datas;
    $project_id = $p->section(0)->value();
    $issue_id = $p->section(1)->value();
    if (!isset($datas['projects'][$project_id]))
        return $h->send(404, true);
    if (!isset($datas['projects'][$project_id]['issues'][$issue_id]))
        return $h->send(404, true);

    echo '<h1>issue #',$issue_id,'</h1>';
    echo '<p>',$datas['projects'][$project_id]['issues'][$issue_id]['description'],'</p>';
    echo '<hr />';
    $h->send(200);
}
