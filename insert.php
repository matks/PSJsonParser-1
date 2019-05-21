<?php
require_once('config.php');
$parser = new Parser($db);

$directory = 'files/';
$list = array_diff(scandir($directory), array('..', '.'));
foreach($list as $file) {
    echo "-- Inserting $file...<br />";
    $parser->init('1.7.6.x', $directory.$file);
    echo "-- $file inserted !<br /><br />";
}

echo "Done !";