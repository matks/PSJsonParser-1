<?php
require_once('config.php');
$parser = new Parser($db);

$directory = 'files/';
$list = array_diff(scandir($directory), array('..', '.'));

foreach($list as $file) {
    echo "-- Inserting $file...<br />";
    $pattern = '/reports_[0-9]{4}-[0-9]{2}-[0-9]{2}-(.*?)\.json/';
    preg_match($pattern, $file, $matches);
    if (!isset($matches[1]) || $matches[1] == '') {
        echo "-- [ERR] VERSION NOT FOUND IN FILENAME $file <br /><br />";
        continue;
    }
    echo "---- Version detected : ".$matches[1]."<br />";
    $parser->init($matches[1], $directory.$file);
    sleep(2);
    echo "-- $file inserted !<br /><br />";
}

echo "Done !";