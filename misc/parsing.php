<?php

$string = <<<EOD
HTTP/1.0 200 OK
Date: Wed, 11 May 2011 06:18:00 GMT
Expires: -1
Cache-Control: private, max-age=0
Content-Type: text/html; charset=ISO-8859-1
Set-Cookie: PREF=ID=2368bfdf697c34ec:FF=0:TM=1305094680:LM=1305094680:S=xUphhintK_StQ5m3; expires=Fri, 10-May-2013 06:18:00 GMT; path=/; domain=.google.fr
Set-Cookie: NID=46=jiv8MFFltXU7qvedgO0mxJ0WeYYyCTGmJtSiosOUlg0Fl_Q2wzU785mDNU02B2-bSZfKzPWVLeQR8ULKFsCtUTJE4nGOqCBpST0C5HaOuYCMHCz0kGX-APWpc5XfBatY; expires=Thu, 10-Nov-2011 06:18:00 GMT; path=/; domain=.google.fr; HttpOnly
Server: gws
X-XSS-Protection: 1; mode=block
X-Cache: MISS from proxy.exemple.fr
Connection: close

<html>
</html>
EOD;
/*
var_dump($string);

$headers = array();
$status = '';
$content = '';
$str = strtok($string, "\n");
$h = null;
while ($str !== false) {
    if ($h and trim($str) === '') {
        $h = false;
        continue;
    }
    if ($h !== false and false !== strpos($str, ':')) {
        $h = true;
        list($headername, $headervalue) = explode(':', trim($str), 2);
        $headername = strtolower($headername);
        $headervalue = ltrim($headervalue);
        if (isset($headers[$headername]))
            $headers[$headername] .= ',' . $headervalue;
        else
            $headers[$headername] = $headervalue;
    }
    elseif ($h !== false and $status === '') {
        $status = $str;
    }
    if ($h === false) {
        $content .= $str."\n";
    }
    $str = strtok("\n");
}
echo $status;
echo "\n--------\n";
echo $content;
 */


require_once '../plor/PSO.php';

$s = new PSO($string);
$headers = new ArrayObject;
$start = $body = null;
while ($line = $s->fetch()) {
    if (is_null($start)) {
        $start = $line; 
    }    
    elseif(is_null($body) and !$line->trim()->isEmpty()) {
        if (($name = $line->fetch(':')) and ($value = $line->fetch())) {
            $key = $name->lower()->toString();
            if ($headers->offsetExists($key))
                $headers->offsetGet($key)->concat(',')->concat($value);
            else
                $headers[$key] = $value;
        }
    }
    elseif(is_null($body) and $line->trim()->isEmpty()) {
        $body = $line;
    }
    else {
        $body->concat($line)->concat("\n");
    }
}

echo $start;
echo "\n--------\n";
foreach($headers as $k => $v) 
    echo $k,'=',$v,PHP_EOL;
echo "\n--------\n";
echo $body;


