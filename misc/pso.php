<?php
require_once '../plor/PSO.php';
require_once 'timer.php';

timer();


echo '--- Méthodes natives ---',PHP_EOL;
$s = 'azertyuiopqsdfghjklmwxcvbn';
for ($i = 1 ; $i <= 10000; $i++)
{
    $s = md5($s);
}
echo 'PHP> ',timer(),PHP_EOL;

$s = new PSO('azertyuiopqsdfghjklmwxcvbn');
for ($i = 1 ; $i <= 10000; $i++)
{
    $s->md5();
}
echo 'PSO> ',timer(),PHP_EOL;

echo '--- Méthodes proxifiées ---',PHP_EOL;
$s = 'azertyuiopqsdfghjklmwxcvbn';
for ($i = 1 ; $i <= 10000; $i++)
{
    $s = ord($s);
}
echo 'PHP> ',timer(),PHP_EOL;
$s = new PSO('azertyuiopqsdfghjklmwxcvbn');
for ($i = 1 ; $i <= 10000; $i++)
{
    $s->ord();
}
echo 'PSO> ',timer(),PHP_EOL;
