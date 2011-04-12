<?php
function translaters_index ($url, $sec) {
    if ($url == '') {
        $url .= 'index.xml';
        $sec->exchange('index');
    } elseif (preg_match('/^index/', $url)) {
        $sec->exchange('index');
    }
    return $url;
}

function translaters_id ($url, $sec) {
    if (preg_match('/(^[0-9]+)/', $url, $m)) {
        $sec->exchange($m[1]);
    }
    return $url;
}





