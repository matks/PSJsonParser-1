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

$passed = $failed = $skipped = 0;
foreach ($tests as $t) {
    if ($t->state == 'passed') {
        $passed ++;
    } elseif ($t->state == 'failed') {
        $failed ++;
    } elseif ($t->state == 'skipped') {
        $skipped ++;
    }
}

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

$suites_container = array_shift(array_values(buildTree($suites)));
//$suites_container = array_shift(array_values($suites_container));

function format_duration($duration) {
    if ($duration != 0) {
        $secs = round($duration/1000, 3);

        return $secs.'s';
    }
}

echo '<pre>';
//print_r($suites_container->suites);
echo '</pre>';

?>
<html>
<head>
    <title>Test JSON</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="navbar">
    <div class="navbar_container">
        <div class="title">
            <h2>Rapport <?php echo $execution->getRef(); ?></h2>
        </div>
        <div class="summary">
            <div class="summary_block suites" title="Execution time">
                <i class="material-icons">timer</i> <span><?php echo $execution->getTotalDuration(); ?></span>
            </div>
            <div class="summary_block suites" title="Number of suites">
                <i class="material-icons">library_books</i> <span><?php echo sizeof($suites)-1; ?></span>
            </div>
            <div class="summary_block tests" title="Number of tests">
                <i class="material-icons">assignment</i> <span><?php echo sizeof($tests); ?></span>
            </div>
            <div class="summary_block passed_tests" title="Number of passed tests">
                <i class="material-icons">check_circle_outline</i> <span><?php echo $passed; ?></span>
            </div>
            <?php if ($failed > 0) {
                echo '<div class="summary_block failed_tests" title="Number of failed tests">
                    <i class="material-icons">highlight_off</i> <span>'.$failed.'</span>
                </div>';
                }
            ?>
            <?php if ($skipped > 0) {
                echo '<div class="summary_block skipped_tests" title="Number of skipped tests">
                    <i class="material-icons">radio_button_checked</i> <span>'.$skipped.'</span>
                </div>';
            }
            ?>
        </div>
    </div>
</div>
<div class="container">
    <div class="details">
        <?php
        $current_campaign_name = '';
        $current_file_name = '';

        function loop_through($cur_suites) {
            global $current_campaign_name;
            global $current_file_name;
            foreach($cur_suites as $suite) {
                if ($current_campaign_name != $suite->campaign) {
                    if ($current_campaign_name != '') {
                        echo '</article>';
                    }
                    $current_campaign_name = $suite->campaign;
                    echo '<div class="campaign_title" id="'.$suite->campaign.'">';
                    echo '<h2><i class="material-icons">library_books</i> '.$suite->campaign.' <i class="material-icons" id="icon_campaign_'.$suite->campaign.'">expand_less</i></h2>';
                    echo '</div>';
                    echo '<article class="container_campaign" id="campaign_'.$suite->campaign.'" style="display:none">';
                }
                if ($current_file_name != $suite->file) {
                    if ($current_file_name != '') {
                        echo '</section>';
                    }
                    $current_file_name = $suite->file;
                    echo '<div class="file_title" id="'.$suite->file.'">';
                    echo '<h3><i class="material-icons">assignment</i> '.$suite->file.' <i class="material-icons" id="icon_file_'.$suite->file.'">expand_less</i></h3>';
                    echo '</div>';
                    echo '<section class="container_file" id="file_'.$suite->file.'" style="display:none">';
                    echo '<hr />';
                }
                echo '<section class="suite">';
                echo '<header class="suite_header">';
                    echo '<h3 class="suite_title">' . $suite->title . '</h3>';
                    if (sizeof($suite->tests) > 0) {
                        echo '<div class="campaign">' . $suite->campaign . '/<span class="filename">' . $suite->file . '</span></div>';
                    }
                if (sizeof($suite->tests) > 0) {
                    echo '<div class="informations">';
                        echo '<div class="block_info"><i class="material-icons">timer</i> <div class="info duration">'.format_duration($suite->duration).'</div></div>';
                        echo '<div class="block_info"><i class="material-icons">assignment</i> <div class="info number_tests"> '.sizeof($suite->tests).'</div></div>';
                        //get number of passed
                        $passed = $failed = $skipped = 0;
                        foreach ($suite->tests as $t) {
                            if ($t->state == 'passed') {
                                $passed ++;
                            } elseif ($t->state == 'failed') {
                                $failed ++;
                            } elseif ($t->state == 'skipped') {
                                $skipped ++;
                            }
                        }
                        if ($passed > 0) {
                            echo '<div class="block_info tests_passed"><i class="material-icons">check</i> <div class="info ">'.$passed.'</div></div>';
                        }
                        if ($failed > 0) {
                            echo '<div class="block_info tests_failed"><i class="material-icons">close</i> <div class="info ">'.$failed.'</div></div>';
                        }
                        if ($skipped > 0) {
                            echo '<div class="block_info tests_skipped"><i class="material-icons">skip_next</i> <div class="info ">'.$skipped.'</div></div>';
                        }

                    echo '</div>';
                }
                echo '</header>';
                if (sizeof($suite->tests) > 0) {
                    echo '<div class="test_container">';
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
                        echo '<section class="test_component '.$test->state.'">';
                            echo '<div class="block_test">';
                                echo '<div id="' . $test->uuid . '" class="test">
                                    <div class="test_' . $test->state . '"> ' .$icon.' <span class="test-title" id="' . $test->uuid . '">'.$test->title . '</span></div>';
                                    echo '<div class="test_duration"><i class="material-icons">timer</i> '.format_duration($test->duration).'</div>';
                                if ($test->state == 'failed') {
                                    echo '<div class="test_info error_message">' . $test->error_message . '</div>';
                                    echo '<div class="test_info stack_trace" id="stack_'.$test->uuid.'">
                                            <div class="error_filename">Origin of this test : <span>'.$suite->campaign.'/'.$suite->file.'</span></div>
                                            <code>' . str_replace('    at', "<br />&nbsp;&nbsp;&nbsp;&nbsp;at", $test->stack_trace) . '</code>
                                            </div>';
                                }

                                echo '</div>'; //uuid
                            echo '</div>'; //block_test
                        echo '</section>'; //test_component
                    }
                    echo '</div>';
                }
                if (sizeof($suite->suites) > 0) {
                    loop_through($suite->suites);
                }
                echo '</section>';
            }
        }
        loop_through($suites_container->suites);
        echo '</section>';
        echo '</article>';
        ?>
    </div>
</div>
<script>
    window.onload = function() {
        const test_blocks = document.querySelectorAll(".test-title");
        for (const test_block of test_blocks) {
            test_block.addEventListener('click', function() {
                let id = this.id;
                let stt = document.getElementById('stack_'+id).style;
                if (stt.display != "block") {
                    stt.display = "block";
                } else {
                    stt.display = "none";
                }
            })
        }

        const campaign_titles = document.querySelectorAll(".campaign_title");
        for (const campaign_title of campaign_titles) {
            campaign_title.addEventListener('click', function() {
                let id = this.id;
                let stc = document.getElementById('campaign_'+id).style;
                let icc = document.getElementById('icon_campaign_'+id);
                if (stc.display != "block") {
                    stc.display = "block";
                    icc.innerHTML = 'expand_less';
                } else {
                    stc.display = "none";
                    icc.innerHTML = 'expand_more';
                }
            })
        }

        const file_titles = document.querySelectorAll(".file_title");
        for (const file_title of file_titles) {
            file_title.addEventListener('click', function() {
                let id = this.id;
                let stf = document.getElementById('file_'+id).style;
                let icf = document.getElementById('icon_file_'+id);
                if (stf.display != "block") {
                    stf.display = "block";
                    icf.innerHTML = 'expand_less';
                } else {
                    stf.display = "none";
                    icf.innerHTML = 'expand_more';
                }
            })
        }
    }

</script>
</body>
</html>

