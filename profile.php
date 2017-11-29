<?php

require 'example.config.php';
require 'src/DomainTypos.php';

$startTime = microtime(true);
$index = DomainTypos::domainIndex($config['domains'], $config['tlds']);

for ($i = 0; $i < 100000; $i++) {
    DomainTypos::isTypo('foo@bar.ru', 1, $index, $config['tlds']);
}

$endTime = microtime(true);
$elapsed = $endTime - $startTime;
echo "Elapsed: $elapsed\n";
