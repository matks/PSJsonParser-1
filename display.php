<?php

require_once('config.php');

if (!isset($_GET['id']) || $_GET['id'] == '') {
    exit();
}

$id = trim($_GET['id']);
//does the cache exists for this one ?
$cache = new Cache(md5('display_'.$id));

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

$suites_container = array_shift(array_values(Tools::buildTree($suites)));


//get all campaigns and files for the summary
$suite = new Suite($db);
$campaignsAndFiles = $suite->getAllCampaignsAndFilesByExecutionId($id);

$test = new Test($db);
$invalid_session_id = $test->getSubset($id, 'invalid session id');


$layout = Layout::get();
$layout->setTitle('Test report');
$view = new Template('display');

$view->set(['title' => 'Test report']);
$view->set(['links' => '<a class="link" href="'.BASEURL.'"><i class="material-icons">home</i> Home</a><a class="link" href="'.BASEURL.'graph.php"><i class="material-icons">timeline</i> Graph</a>']);

//recap
$recap = '<div class="recap_block suites" title="Execution time">
                <i class="material-icons">timer</i> <span>'.$execution->getTotalDuration().'</span>
            </div>
            <div class="recap_block suites" title="Number of suites">
                <i class="material-icons">library_books</i> <span>'.(sizeof($suites)-1).'</span>
            </div>
            <div class="recap_block tests" title="Number of tests">
                <i class="material-icons">assignment</i> <span>'.sizeof($tests).'</span>
            </div>
            <div class="recap_block passed_tests" title="Number of passed tests">
                <i class="material-icons">check_circle_outline</i> <span>'.$execution->getPassed().'</span>
            </div>';

  if ($execution->getFailed() > 0) {
    $recap .= '<div class="recap_block failed_tests" title="Number of failed tests">
        <i class="material-icons">highlight_off</i> <span>'.$execution->getFailed().'</span>
    </div>';
}

if ($execution->getSkipped() > 0) {
    $recap .= '<div class="recap_block skipped_tests" title="Number of skipped tests">
        <i class="material-icons">radio_button_checked</i> <span>'.$execution->getSkipped().'</span>
    </div>';
}
$view->set(['recap' => $recap]);

$view->set(['start_date' => date('d/m/Y H:i', strtotime($execution->getStartDate()))]);
$view->set(['end_date' => date('d/m/Y H:i', strtotime($execution->getEndDate()))]);

//navigation
$nav = '';
if (sizeof($campaignsAndFiles) > 0) {
    $cur_campaign = $campaignsAndFiles[0]->campaign;
    $nav .= '<div id="campaign_list">';
    $nav .= '<a href="#'.$cur_campaign.'"><div class="campaign">'.$cur_campaign.'</div></a>';
    $nav .= '<div class="file_list">';
    foreach($campaignsAndFiles as $item) {
        if ($cur_campaign != $item->campaign) {
            $cur_campaign = $item->campaign;
            $nav .= '</div>'; //closing the file list
            $nav .= '<a href="#'.$cur_campaign.'"><div class="campaign">'.$cur_campaign.'</div></a>';
            $nav .= '<div class="file_list">';
        }
        $class = 'passed';
        if ($item->hasFailed > 0) {
            $class = 'failed';
        }
        $nav .= '<a href="#'.$item->file.'"><div class="file '.$class.'"> '.$item->file.'</div></a>';
        //listing files in it
    }
    $nav .= '</div>'; //closing the file list
    $nav .= '</div>'; //closing the campaign list
}
$view->set(['navigation' => $nav]);

$view->set(['invalid_session_id_count' => count($invalid_session_id)]);

//content
$content = '';
$current_campaign_name = '';
$current_file_name = '';

function loop_through($cur_suites) {
    global $current_campaign_name;
    global $current_file_name;
    global $content;
    foreach($cur_suites as $suite) {
        if ($current_campaign_name != $suite->campaign) {
            if ($current_campaign_name != '') {
                $content .= '</article>';
            }
            $current_campaign_name = $suite->campaign;
            $content .= '<a name="'.$suite->campaign.'"></a>';
            $content .= '<div class="campaign_title" id="'.$suite->campaign.'">';
            $content .= '<h2><i class="material-icons">library_books</i> '.$suite->campaign.'</h2>';
            $content .= '</div>';
            $content .= '<article class="container_campaign" id="campaign_'.$suite->campaign.'">';
        }
        if ($current_file_name != $suite->file) {
            if ($current_file_name != '') {
                $content .= '</section>';
            }
            $current_file_name = $suite->file;
            $content .= '<a name="'.$suite->file.'"></a>';
            $content .= '<div class="file_title" id="'.$suite->file.'">';
            $content .= '<h3><i class="material-icons">assignment</i> '.$suite->file.'</h3>';
            $content .= '</div>';
            $content .= '<section class="container_file" id="file_'.$suite->file.'">';
            $content .= '<hr />';
        }
        $content .= '<section class="suite '.($suite->hasFailures ? 'hasFailed' : '').' '.($suite->hasPasses ? 'hasPassed' : '').'" style="display: block;">';
        $content .= '<header class="suite_header">';
        $content .= '<h3 class="suite_title">' . $suite->title . '</h3>';
        if (sizeof($suite->tests) > 0) {
            $content .= '<div class="campaign">' . $suite->campaign . '/<span class="filename">' . $suite->file . '</span></div>';
        }
        if (sizeof($suite->tests) > 0) {
            $content .= '<div class="informations">';
            $content .= sprintf("<div class=\"block_info\"><i class=\"material-icons\">timer</i> <div class=\"info duration\">%s</div></div>", Tools::format_duration($suite->duration));
            $content .= '<div class="block_info"><i class="material-icons">assignment</i> <div class="info number_tests"> '.sizeof($suite->tests).'</div></div>';
            //get number of passed
            if ($suite->totalPasses > 0) {
                $content .= '<div class="block_info tests_passed"><i class="material-icons">check</i> <div class="info ">'.$suite->totalPasses.'</div></div>';
            }
            if ($suite->totalFailures > 0) {
                $content .= '<div class="block_info tests_failed"><i class="material-icons">close</i> <div class="info ">'.$suite->totalFailures.'</div></div>';
            }
            if ($suite->totalSkipped> 0) {
                $content .= '<div class="block_info tests_skipped"><i class="material-icons">skip_next</i> <div class="info ">'.$suite->totalSkipped.'</div></div>';
            }
            $content .= '<div class="metric_container">';
            $content .= '<div class="metric">';
            $content .= '<div class="background"><div class="metric_number">'.(round($suite->totalPasses/sizeof($suite->tests), 3) * 100).'%</div><div class="advancement" style="width:'.(round($suite->totalPasses/sizeof($suite->tests), 3) * 100).'%"></div></div>';
            $content .= '</div>';
            $content .= '</div>';

            $content .= '</div>';
        }
        $content .= '</header>';
        if (sizeof($suite->tests) > 0) {
            $content .= '<div class="test_container">';
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
                $content .= '<section class="test_component '.$test->state.'">';
                $content .= '<div class="block_test">';
                $content .= '<div id="' . $test->uuid . '" class="test"><div class="test_' . $test->state . '"> ' .$icon.' <span class="test_title" id="' . $test->uuid . '">'.$test->title . '</span></div>';
                $content .= sprintf("<div class=\"test_duration\"><i class=\"material-icons\">timer</i> %s</div>", Tools::format_duration($test->duration));
                if ($test->state == 'failed') {
                    $content .= '<div class="test_info error_message">' . $test->error_message . '</div>';
                    $content .= sprintf("<div class=\"test_info stack_trace\" id=\"stack_%s\"><code>%s</code></div>", $test->uuid, str_replace('    at', "<br />&nbsp;&nbsp;&nbsp;&nbsp;at", $test->stack_trace));
                }

                $content .= '</div>'; //uuid
                $content .= '</div>'; //block_test
                $content .= '</section>'; //test_component
            }
            $content .= '</div>';
        }
        if (sizeof($suite->suites) > 0) {
            loop_through($suite->suites);
        }
        $content .= '</section>';
    }
}
loop_through($suites_container->suites);
$content .= '</section>';
$content .= '</article>';

$view->set(['content' => $content]);

$layout->setView($view);
$layout->render();

$cache->store();