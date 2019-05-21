<?php

require_once('config.php');

$start_date = date('Y-m-d', strtotime('-2 weeks'));
$end_date = date('Y-m-d');

$selected_version = '';
$selected_campaign = '';

//db values
$execution = new Execution($db);
//versions
$versions = $execution->getVersions();
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


?>
<html>
<head>
    <title>Stats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>
<body>
<div class="navbar">
    <div class="navbar_container">
        <div class="title">
            <h2>Graph reporting</h2>
        </div>
    </div>
</div>
<div class="container">
    <div class="details">
        <div class="options">
            <div class="blocks_container">
                <form action="" method="GET" id="graphform">
                    <div class="form_block">
                        <div class="form_container">
                            <label for="start_date">
                                Start date
                                <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>"/>
                            </label>
                        </div>
                        <div class="form_container">
                            <label for="end_date">
                                End date
                                <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>" max="<?php echo date('Y-m-d'); ?>"/>
                            </label>
                        </div>
                    </div><div class="form_block">
                        <label for="version">
                            Version
                            <select name="version" id="version">
                                <?php
                                    foreach($versions as $v) {
                                        $s = ($selected_version == $v->version) ? 'selected' : '';
                                        echo '<option value="'.$v->version.'" '.$s.'>'.$v->version.'</option>';
                                    }
                                ?>
                            </select>
                        </label>
                    </div><div class="form_block">
                        <label for="Campaign">
                            Campaign
                            <select name="campaign" id="campaign">
                                <option value="">All</option>
                                <?php
                                    foreach($campaigns as $c) {
                                        $s = ($selected_campaign == $c->campaign) ? 'selected' : '';
                                        echo '<option value="'.$c->campaign.'" '.$s.'>'.$c->campaign.'</option>';
                                    }
                                ?>
                            </select>
                        </label>
                    </div><div class="form_block">
                        <button>Filter</button>
                    </div>
                </form>
            </div>
        </div>




    </div>
</div>
</body>
</html>