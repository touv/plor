<?php
 
function list_of_projects_in_xml($p, $h)
{
    global $datas;

    $h->add('content-type', 'text/xml');
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
    $datas['projects'][$project_id] = array(
        'name' => preg_replace('/[^\w]*/', '', $p->name),
        'issues' => array(),
    );
    $h
        ->add('Location', $p->__server->fullpath().$project_id.'.xml')
        ->send(201, true);
}
function get_project_in_xml($p, $h)
{
    global $datas;
    $h->add('content-type', 'text/xml');
    $project_id = $p->__sections[0]->getInteger();
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
    $project_id = $p->__sections[0]->getInteger();
    unset($datas['projects'][$project_id]);
    $h->send(204, true);
}
function list_of_issues_in_xml($p, $h)
{
    global $datas;
    $h->add('content-type', 'text/xml');
    $project_id = $p->__sections[0]->getInteger();
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
    $project_id = $p->__sections[0]->getInteger();
    if (!isset($datas['projects'][$project_id]))
        return $h->send(404, true);

    if (is_null($p->description)) 
        return $h->send(400, true);

    $issue_id = count($datas['projects'][$project_id]['issues']);
    $datas['projects'][$project_id]['issues'][$issue_id] = array(
        'description' => $p->description,
    );
    $h
        ->add('Location', $p->__server->fullpath().$issue_id.'.xml')
        ->send(201, true);
}
function get_issue_in_xml($p, $h)
{
    global $datas;
   }
function delete_issue($p, $h)
{
    global $datas;
    $project_id = $p->__sections[0]->getInteger();
    $issue_id = $p->__sections[1]->getInteger();
    unset($datas['projects'][$project_id]['issues'][$issue_id]);
    $h->send(204, true);
}
function get_issue($p, $h)
{
    global $datas;
    if ($p->__sections[2]->isEqual('xml')) {
         $h->add('content-type', 'text/xml');
         $project_id = $p->__sections[0]->getInteger();
         $issue_id = $p->__sections[1]->getInteger();
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
    elseif ($p->__sections[2]->isEqual('html')) {
        $h->add('content-type', 'text/html');
        $project_id = $p->__sections[0]->getInteger();
        $issue_id = $p->__sections[1]->getInteger();
        if (!isset($datas['projects'][$project_id]))
            return $h->send(404, true);
        if (!isset($datas['projects'][$project_id]['issues'][$issue_id]))
            return $h->send(404, true);

        echo '<h1>issue #',$issue_id,'</h1>';
        echo '<p>',$datas['projects'][$project_id]['issues'][$issue_id]['description'],'</p>';
        echo '<hr />';
        $h->send(200);
    }
}

function options($p, $h)
{
    $h->add('Allow', implode(',', $p->__methods));
    $h->add('Content-Length', '0');
    $h->add('Content-Type', 'text/plain');
    $h->send(200);
}


function enrich_params($p, $h)
{
    $p->newparam = 'Adding a new param';
}


