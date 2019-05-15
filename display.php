<?php

require_once('Execution.php');
require_once('Suite.php');
require_once('Test.php');
require_once('Parser.php');

require_once('database.php');
//db properties
define('DB_TYPE','mysql');
define('DB_HOST','localhost');
define('DB_USER','simon');
define('DB_PASS','phpmyadmin');
define('DB_NAME','prestashop_results');

try{
    $db = Database::get();
} catch (Exception $e) {
    exit("Error when connecting to database : ".$e->getMessage()."\n");
}

if (!isset($_GET['id']) || $_GET['id'] == '') {
    exit();
}

/*
 *
 * Can't this be in a class ? ¯\_(ツ)_/¯
 *
 */




$id = trim($_GET['id']);
try {
    $execution = new Execution($db);
    $execution->populate($id);
} catch(Exception $e) {
    exit("Can't find the execution");
}

$suite = new Suite($db);
$suites = $suite->getAllByExecutionId($id);

$test = new Test($db);
$tests = $test->getAllByExecutionId($id);


//add tests in each suite
foreach($suites as $suite) {
    $suite->tests = [];
    foreach($tests as $test) {
        if ($test->suite_id == $suite->id) {
            $suite->tests[] = $test;
            //array_push($suite->tests, $test);
            unset($test);
        }
    }
}










function buildTree(array &$suites, $parentId = null) {

    $branch = array();

    foreach ($suites as &$suite) {

        if ($suite->parent_id == $parentId) {
            //add tests


            $children = buildTree($suites, $suite->id);
            if ($children) {
                $suite->suites = $children;
            }
            $branch[$suite->id] = $suite;
            unset($suite);
        }
    }
    return $branch;
}