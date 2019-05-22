<?php

require_once('config.php');

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


//get all campaigns and files for the summary
$suite = new Suite($db);
$campaignsAndFiles = $suite->getAllCampaignsAndFilesByExecutionId($id);

$test = new Test($db);
$invalid_session_id = $test->getSubset($id, 'invalid session id');

function format_duration($duration) {
    if ($duration != 0) {
        $secs = round($duration/1000, 2);

        $return = '';

        $minutes = floor(($secs / 60) % 60);
        if ($minutes > 0) {
            $return .= $minutes.'m';
        }
        $return .= $secs.'s';
        return $return;
    }
}

?>
<html>
<head>
    <title>Report display</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>
<body>
<div class="navbar">
    <div class="navbar_container">
        <div class="links">
            <a class="link" href="<?php echo BASEURL; ?>" target="_blank">
                <i class="material-icons">home</i> Home
            </a>
            <a class="link" href="<?php echo BASEURL; ?>graph.php" target="_blank">
                <i class="material-icons">timeline</i> Graph
            </a>
        </div>
        <div class="title">
            <h2>Report <?php echo $execution->getRef(); ?></h2>
        </div>
        <div class="recap">
            <div class="recap_block suites" title="Execution time">
                <i class="material-icons">timer</i> <span><?php echo $execution->getTotalDuration(); ?></span>
            </div>
            <div class="recap_block suites" title="Number of suites">
                <i class="material-icons">library_books</i> <span><?php echo sizeof($suites)-1; ?></span>
            </div>
            <div class="recap_block tests" title="Number of tests">
                <i class="material-icons">assignment</i> <span><?php echo sizeof($tests); ?></span>
            </div>
            <div class="recap_block passed_tests" title="Number of passed tests">
                <i class="material-icons">check_circle_outline</i> <span><?php echo $execution->getPassed(); ?></span>
            </div>
            <?php if ($execution->getFailed() > 0) {
                echo '<div class="recap_block failed_tests" title="Number of failed tests">
                    <i class="material-icons">highlight_off</i> <span>'.$execution->getFailed().'</span>
                </div>';
                }
            ?>
            <?php if ($execution->getSkipped() > 0) {
                echo '<div class="recap_block skipped_tests" title="Number of skipped tests">
                    <i class="material-icons">radio_button_checked</i> <span>'.$execution->getSkipped().'</span>
                </div>';
            }
            ?>
        </div>
    </div>
</div>
<div class="container">

    <div class="details">
        <div class="options">
            <div class="blocks_container">
                <div class="block">
                    Start Date : <?php echo date('d/m/Y H:i', strtotime($execution->getStartDate())); ?>
                </div>
                <div class="block">
                    End Date : <?php echo date('d/m/Y H:i', strtotime($execution->getEndDate())); ?>
                </div>
            </div>
        </div>
        <div id="left_summary">
            <div class="summary_block">
                <h4>Options</h4>
                <div class="buttons">
                    <div class="button">
                        <button id="toggle_failed">Hide Passed Tests</button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="summary_block">
                <h4>Navigation</h4>
                <div class="summary">
                    <?php
                    if (sizeof($campaignsAndFiles) > 0) {
                        $cur_campaign = $campaignsAndFiles[0]->campaign;
                        echo '<div id="campaign_list">';
                        echo '<a href="#'.$cur_campaign.'"><div class="campaign">'.$cur_campaign.'</div></a>';
                        echo '<div class="file_list">';
                        foreach($campaignsAndFiles as $item) {
                            if ($cur_campaign != $item->campaign) {
                                $cur_campaign = $item->campaign;
                                echo '</div>'; //closing the file list
                                echo '<a href="#'.$cur_campaign.'"><div class="campaign">'.$cur_campaign.'</div></a>';
                                echo '<div class="file_list">';
                            }
                            $class = 'passed';
                            if ($item->hasFailed > 0) {
                                $class = 'failed';
                            }
                            echo '<a href="#'.$item->file.'"><div class="file '.$class.'"> '.$item->file.'</div></a>';
                            //listing files in it
                        }
                        echo '</div>'; //closing the file list
                        echo '</div>'; //closing the campaign list
                    }
                    ?>
                </div>
            </div>
            <hr>
            <div class="summary_block">
                <div class="additional_infos">
                    <h4>Additional Info</h4>
                    <div class="info">
                        <span><i class="material-icons">bug_report</i> Invalid Session ID count: </span> <?php echo count($invalid_session_id); ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="content">
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
                        echo '<a name="'.$suite->campaign.'"></a>';
                        echo '<div class="campaign_title" id="'.$suite->campaign.'">';
                        echo '<h2><i class="material-icons">library_books</i> '.$suite->campaign.'</h2>';
                        echo '</div>';
                        echo '<article class="container_campaign" id="campaign_'.$suite->campaign.'">';
                    }
                    if ($current_file_name != $suite->file) {
                        if ($current_file_name != '') {
                            echo '</section>';
                        }
                        $current_file_name = $suite->file;
                        echo '<a name="'.$suite->file.'"></a>';
                        echo '<div class="file_title" id="'.$suite->file.'">';
                        echo '<h3><i class="material-icons">assignment</i> '.$suite->file.'</h3>';
                        echo '</div>';
                        echo '<section class="container_file" id="file_'.$suite->file.'">';
                        echo '<hr />';
                    }
                    echo '<section class="suite '.($suite->hasFailures ? 'hasFailed' : '').' '.($suite->hasPasses ? 'hasPassed' : '').'" style="display: block;">';
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
                        echo '<div class="metric_container">';
                        echo '<div class="metric">';
                        echo '<div class="background"><div class="metric_number">'.(round($passed/sizeof($suite->tests), 3) * 100).'%</div><div class="advancement" style="width:'.(round($passed/sizeof($suite->tests), 3) * 100).'%"></div></div>';
                        echo '</div>';
                        echo '</div>';

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
                                    <div class="test_' . $test->state . '"> ' .$icon.' <span class="test_title" id="' . $test->uuid . '">'.$test->title . '</span></div>';
                            echo '<div class="test_duration"><i class="material-icons">timer</i> '.format_duration($test->duration).'</div>';
                            if ($test->state == 'failed') {
                                echo '<div class="test_info error_message">' . $test->error_message . '</div>';
                                echo '<div class="test_info stack_trace" id="stack_'.$test->uuid.'">
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
</div>
<script>
    window.onload = function() {
        let test_blocks;
        test_blocks = document.querySelectorAll(".test_title");
        for (const test_block of test_blocks) {
            test_block.addEventListener('click', function(event, item, t) {
                let id = this.id;
                let stt = document.getElementById('stack_'+id).style;
                if (stt.display != "block") {
                    stt.display = "block";
                } else {
                    stt.display = "none";
                }
            })
        }

        let toggle_failed_button = document.getElementById('toggle_failed');
        toggle_failed_button.addEventListener('click', function() {
            let passed_blocks = document.querySelectorAll("section.suite.hasPassed:not(.hasFailed)");
            passed_blocks.forEach(function(block) {
                if (block.style.display !== "block") {
                    block.style.display="block";
                    toggle_failed_button.innerHTML = 'Hide Passed Tests';
                } else {
                    block.style.display="none";
                    toggle_failed_button.innerHTML = 'Show Passed Tests';
                }
            });
        });
    }
</script>
</body>
</html>

