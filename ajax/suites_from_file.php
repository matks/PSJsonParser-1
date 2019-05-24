<?php

require_once('../config.php');
if (!isset($_GET['execution_id']) || $_GET['execution_id'] == '' || !isset($_GET['file']) || $_GET['file'] == '' || !isset($_GET['campaign']) || $_GET['campaign'] == '') {
    http_response_code(403);
    echo json_encode(['message' => 'Arguments missing']);
    die();
}

$execution_id = trim($_GET['execution_id']);
$campaign = trim($_GET['campaign']);
$file = trim($_GET['file']).'.js';

$cache = new Cache('dynamic_'.$execution_id.'_'.$campaign.'_'.$file);

//get suites and tests for this campaign/file
$suite = new Suite($db);
$suites = $suite->getAllSuitesByFile($execution_id, $campaign, $file);

$test = new Test($db);
$tests = $test->getAllTestsByFile($execution_id, $campaign, $file);

if (sizeof($suites) > 0 && sizeof($tests) > 0) {
    //add tests in each suite
    foreach($suites as $suite) {
        $suite->tests = [];
        foreach($tests as $test) {
            if ($test->suite_id == $suite->id) {
                $suite->tests[] = $test;
                unset($test);
            }
        }
    }

    //recreate the suite tree
    $suites_tree = Tools::buildTree($suites, $suites[0]->parent_id);

    //create the display
    $suites_content = '';
    loop_through($suites_tree, $suites_content);

    echo json_encode($suites_content);
    die();

} else {
    http_response_code(403);
    echo json_encode(['message' => 'Arguments missing']);
    $cache->store();
    die();
}

function loop_through($cur_suites, &$suites_content) {
    foreach($cur_suites as $suite) {

        $suites_content .= '<section class="suite '.($suite->hasFailures ? 'hasFailed' : '').' '.($suite->hasPasses ? 'hasPassed' : '').'">';
        $suites_content .= '<header class="suite_header">';
        $suites_content .= '<h3 class="suite_title">' . $suite->title . '</h3>';
        if (sizeof($suite->tests) > 0) {
            $suites_content .= '<div class="campaign">' . $suite->campaign . '/<span class="filename">' . $suite->file . '</span></div>';
        }
        if (sizeof($suite->tests) > 0) {
            $suites_content .= '<div class="informations">';
            $suites_content .= '<div class="block_info"><i class="material-icons">timer</i> <div class="info duration">'.Tools::format_duration($suite->duration).'</div></div>';
            $suites_content .= '<div class="block_info"><i class="material-icons">assignment</i> <div class="info number_tests"> '.sizeof($suite->tests).'</div></div>';
            //get number of passed
            if ($suite->totalPasses > 0) {
                $suites_content .= '<div class="block_info tests_passed"><i class="material-icons">check</i> <div class="info ">'.$suite->totalPasses.'</div></div>';
            }
            if ($suite->totalFailures > 0) {
                $suites_content .= '<div class="block_info tests_failed"><i class="material-icons">close</i> <div class="info ">'.$suite->totalFailures.'</div></div>';
            }
            if ($suite->totalSkipped> 0) {
                $suites_content .= '<div class="block_info tests_skipped"><i class="material-icons">skip_next</i> <div class="info ">'.$suite->totalSkipped.'</div></div>';
            }
            $suites_content .= '<div class="metric_container">';
            $suites_content .= '<div class="metric">';
            $suites_content .= '<div class="background"><div class="metric_number">'.(round($suite->totalPasses/sizeof($suite->tests), 3) * 100).'%</div><div class="advancement" style="width:'.(round($suite->totalPasses/sizeof($suite->tests), 3) * 100).'%"></div></div>';
            $suites_content .= '</div>';
            $suites_content .= '</div>';

            $suites_content .= '</div>';
        }
        $suites_content .= '</header>';
        if (sizeof($suite->tests) > 0) {
            $suites_content .= '<div class="test_container">';
            foreach ($suite->tests as $test) {
                $icon = '';
                if ($test->state == 'passed') {
                    $icon = '<i class="icon material-icons">check_circle</i>';
                }
                if ($test->state == 'failed') {
                    $icon = '<i class="icon material-icons">remove_circle</i>';
                }
                if ($test->state == 'skipped') {
                    $icon = '<i class="icon material-icons">error</i>';
                }
                $suites_content .= '<section class="test_component '.$test->state.'">';
                $suites_content .= '<div class="block_test">';
                $suites_content .= '<div id="' . $test->uuid . '" class="test"><div class="test_' . $test->state . '"> ' .$icon.' <span class="test_title" id="' . $test->uuid . '">'.$test->title . '</span></div>';
                $suites_content .= '<div class="test_duration"><i class="material-icons">timer</i> '.Tools::format_duration($test->duration).'</div>';
                if ($test->state == 'failed') {
                    $suites_content .= '<div class="test_info error_message">' . $test->error_message . '</div>';
                    $suites_content .= '<div class="test_info stack_trace" id="stack_'.$test->uuid.'"><code>'.str_replace('    at', "<br />&nbsp;&nbsp;&nbsp;&nbsp;at", $test->stack_trace).'</code></div>';
                }

                $suites_content .= '</div>'; //uuid
                $suites_content .= '</div>'; //block_test
                $suites_content .= '</section>'; //test_component
            }
            $suites_content .= '</div>';
        }
        if ($suite->hasSuites) {
            loop_through($suite->suites, $suites_content);
        }
        $suites_content .= '</section>';
    }
}