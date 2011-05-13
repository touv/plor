<?php

$string = <<<EOD
HTTP/1.0 200 OK
Date: Wed, 11 May 2011 06:18:00 GMT
Expires: -1
Cache-Control: private, max-age=0
Content-Type: text/html; charset=ISO-8859-1
Set-Cookie: PREF=ID1=A; expires=Fri, 10-May-2013 06:18:00 GMT; path=/; domain=.google.fr
Set-Cookie: PREF=ID2=B; expires=Thu, 10-Nov-2011 06:18:00 GMT; path=/; domain=.google.fr; HttpOnly
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
$request = new stdClass;
$request->types = null;
$request->headers = null;
$request->body = null;
while ($line = $s->fetch()) {
    if (is_null($request->types)) {
        $request->types = $line;
    }    
    elseif(is_null($request->body) and !$line->trim()->isEmpty()) {
        if (($name = $line->fetch(':')) and ($value = $line->fetch())) {
            $key = $name->lower()->toString();
            if (isset($request->headers->$key))
                $request->headers->{$key}[] = $value;
            else
                $request->headers->$key = array($value);
        }
    }
    elseif(is_null($request->body) and $line->trim()->isEmpty()) {
        $request->body = $line;
    }
    else {
        $request->body->concat($line)->concat("\n");
    }
}

echo $request->types;
echo "\n--------\n";
foreach($request->headers as $k => $v) 
    echo $k,'=',$v,PHP_EOL;
echo "\n--------\n";
echo $request->body;


require_once '../plor/PRO.php';
$i = new PRO($request);

while ($item = $i->fetch()) {
    $item->dump();
}

