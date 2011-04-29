<?php
require_once '../plor/CMD.php';


echo CMD::factory('/bin/ls')
    ->option('all')
    ->option('b')
    ->option('color', 'never')
    ->option('format', 'single-column')
    ->option('t')
    ->option('reverse')
    ->param('/')
    ->fire()->toString();


echo CMD::factory('/bin/ls', CMD::NOHUP)
    ->option('all')
    ->option('b')
    ->option('color', 'never')
    ->option('format', 'single-column')
    ->option('t')
    ->option('reverse')
    ->param('/')
    ->fire()->toString();
