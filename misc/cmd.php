<?php
ini_set('implicit_flush', true);
require_once '../plor/CMD.php';

echo CMD::factory('/bin/ls')
    ->option('all')
    ->option('b')
    ->option('color', 'never')
    ->option('format', 'single-column')
    ->option('t')
    ->option('reverse')
    ->param('/usr')
    ->fire()->fetchAll()->toString();

CMD::factory('/bin/ls')
    ->option('all')
    ->param('/usr')
    ->bind(1, '/tmp/t.txt')
    ->fire();
echo file_get_contents('/tmp/t.txt');



$out = new PSO;
CMD::factory('/bin/ls')
    ->option('all')
    ->option('b')
    ->option('color', 'never')
    ->option('format', 'single-column')
    ->option('t')
    ->option('reverse')
    ->param('/usr')
    ->bind(1, $out->toURL())
    ->fire();
var_dump($out->toString());


$c = CMD::factory('/bin/sleep')
    ->param('4')
    ->fire();

echo 'On attend';

while($c->isAlive()) {
    usleep(500000);
    echo '.';
}

var_dump($c->fetchAll()->toString());


$c = CMD::factory('/usr/bin/curl', array('long_option_operator' => ' '))
    ->option('no-buffer')
    ->option('request', 'GET', 0)
    ->param('http://www.google.com')
    ->fire();

while($row = $c->fetch()) {
    echo $row;
}

