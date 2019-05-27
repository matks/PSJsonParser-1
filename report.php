<?php

require_once('config.php');

if (!isset($_GET['id']) || $_GET['id'] == '') {
    exit();
}

$id = trim($_GET['id']);
//does the cache exists for this one ?
$cache = new Cache('report_'.$id);

try {
    $execution = new Execution($db);
    $execution->populate($id);
} catch(Exception $e) {
    exit("Can't find the execution");
}

//get all campaigns and files for the summary
$suite = new Suite($db);
$campaignsAndFiles = $suite->getAllCampaignsAndFilesByExecutionId($id);

//$test = new Test($db);
//$invalid_session_id = $test->getSubset($id, 'invalid session id');
$exec = new Execution($db);
$precise_stats = $exec->getExecutionPreciseStats($id);

$c_precise_stats = '
<ul class="precise_stats">
    <li><span>Assertion Error : </span>'.$precise_stats->value_expected.'</li>
    <li><span>File not found : </span>'.$precise_stats->file_not_found.'</li>
    <li><span>Timeout : </span>'.$precise_stats->not_visible_after_timeout.'</li>
    <li><span>Object not found : </span>'.$precise_stats->wrong_locator.'</li>
    <li><span>Invalid Session ID : </span>'.$precise_stats->invalid_session_id.'</li>
</ul>';

$layout = Layout::get();
$layout->setTitle('Test report');
$layout->addJSFile('https://code.jquery.com/jquery-3.4.1.min.js');

$view = new Template('report');

$view->set(['title' => 'Test report']);
$view->set(['precise_stats' => $c_precise_stats]);
$view->set(['execution_id' => $id]);
$view->set(['links' => '<a class="link" href="'.BASEURL.'"><i class="material-icons">home</i> Home</a><a class="link" href="'.BASEURL.'graph.php"><i class="material-icons">timeline</i> Graph</a>']);

//recap
$recap = '<div class="recap_block suites" title="Execution time">
                <i class="material-icons">timer</i> <span>'.$execution->getTotalDuration().'</span>
            </div>
            <div class="recap_block suites" title="Number of suites">
                <i class="material-icons">library_books</i> <span>'.$execution->getSuites().'</span>
            </div>
            <div class="recap_block tests" title="Number of tests">
                <i class="material-icons">assignment</i> <span>'.$execution->getTests().'</span>
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

$content = '';
if (sizeof($campaignsAndFiles) > 0) {
    $cur_campaign = $campaignsAndFiles[0]->campaign;
    $content .= '<a name="'.$cur_campaign.'"></a>';
    $content .= '<div class="campaign_title" id="'.$cur_campaign.'">
    <h2><i class="material-icons">library_books</i> '.$cur_campaign.'</h2>
    </div>';
    $content .= '<article class="container_campaign" id="campaign_'.$cur_campaign.'">';
    foreach($campaignsAndFiles as $item) {
        if ($cur_campaign != $item->campaign) {
            $cur_campaign = $item->campaign;

            $content .= '</section>'; //closing the file container section
            $content .= '</article>'; //closing the article container
            $content .= '<a name="'.$cur_campaign.'"></a>';
            $content .= '<div class="campaign_title" id="'.$cur_campaign.'">
            <h2><i class="material-icons">library_books</i> '.$cur_campaign.'</h2>
            </div>';
            $content .= '<article class="container_campaign" id="campaign_'.$cur_campaign.'">';
        }
        //file part
        $indicators = '';
        if ($item->hasFailed > 0) {
            $indicators = '<span class="indicator failed" title="Tests failed">('.$item->hasFailed.')</span>';
        }

        $content .= '<a name="'.$item->file.'"></a>';
        $content .= '<div class="file_title" data-state="empty" data-campaign="'.$cur_campaign.'" data-file="'.Tools::sanitizeFilename($item->file).'" title="Click to load data">
                        <h3><i class="material-icons">assignment</i> '.$item->file.' '.$indicators.'</h3>
                     </div>';
        $content .= '<section class="container_file" id="'.Tools::sanitizeFilename($item->file).'">';
        $content .= '<div class="dynamic_container" id="file_container_'.Tools::sanitizeFilename($item->file).'"></div>';
        $content .= '</section>';
    }
    $content .= '</section>'; //closing the file container section
    $content .= '</article>'; //closing the article container
}

$view->set(['content' => $content]);

$layout->setView($view);
$layout->render($cache);

