<?php

require_once('config.php');

$start_date = date('Y-m-d', strtotime('-2 weeks'));
$end_date = date('Y-m-d');


$selected_campaign = '';

//db values
$execution = new Execution($db);
//versions
$versions = $execution->getVersions();
$selected_version = $versions[0]->version;
//campaigns
$suite = new Suite($db);
$campaigns = $suite->getCampaigns();

//merge with data from the GET array
$get = $_GET;
if (isset($get) && sizeof($get) > 0) {
    if (isset($get['start_date']) && $get['start_date'] != '') {
        $start_date = $get['start_date'];
    }
    if (isset($get['end_date']) && $get['end_date'] != '') {
        $end_date = $get['end_date'];
    }
    if (isset($get['version']) && $get['version'] != '') {
        $selected_version = $get['version'];
    }
    if (isset($get['campaign']) && $get['campaign'] != '') {
        $selected_campaign = $get['campaign'];
    }
}

//get the data
$criteria = [
    'start_date' => $start_date,
    'end_date' => date('Y-m-d', strtotime($end_date) + 3600*24),
    'version' => $selected_version,
    'campaign' => $selected_campaign
];

$execution = new Execution($db);
$data = $execution->getCustomData($criteria);


$execution = new Execution($db);
$precise = $execution->getPreciseStats($criteria);

/*
 *
 * Layout stuff
 *
 */
$layout = Layout::get();
$layout->setTitle('Stats');
$layout->addJSFile('https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js');
$view = new Template('graph');
$view->set(['title' => 'Stats']);
$view->set(['links' => '<a class="link" href="'.BASEURL.'"><i class="material-icons">home</i> Home</a><a class="link" href="'.BASEURL.'graph.php"><i class="material-icons">timeline</i> Graph</a>']);

$view->set(['start_date' => $start_date]);
$view->set(['end_date' => $end_date]);
$view->set(['current_date' => date('Y-m-d')]);


$select_version = '';
foreach($versions as $v) {
    $s = ($selected_version == $v->version) ? 'selected' : '';
    $select_version .= '<option value="'.$v->version.'" '.$s.'>'.$v->version.'</option>';
}
$view->set(['select_version' => $select_version]);

$select_campaign = '';
foreach($campaigns as $c) {
    $s = ($selected_campaign == $c->campaign) ? 'selected' : '';
    $select_campaign .= '<option value="'.$c->campaign.'" '.$s.'>'.$c->campaign.'</option>';
}
$view->set(['select_campaign' => $select_campaign]);

$view->set(['json_data' => json_encode($data)]);

$view->set(['json_failures_data' => json_encode($precise)]);

$layout->setView($view);
$layout->render();


?>