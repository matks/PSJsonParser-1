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

?>
<html>
<head>
    <title>Graph and statistics</title>
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
            <a class="link" href="<?php echo BASEURL; ?>">
                <i class="material-icons">home</i> Home
            </a>
            <a class="link" href="<?php echo BASEURL; ?>graph.php">
                <i class="material-icons">timeline</i> Graph
            </a>
        </div>
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
        <div class="canvas_container">
            <canvas id="chart" style="width: 100%;" height="250"></canvas>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
<script>
    const data = <?php echo json_encode($data); ?>;
    const labels = Array.from(data, x => x.custom_start_date);
    const passed_percent = Array.from(data, x => Math.round((parseFloat(x.totalPasses)*10000 / (parseFloat(x.totalPasses) + parseFloat(x.totalSkipped) + parseFloat(x.totalFailures))))/100 );
    const failed_percent = Array.from(data, x => Math.round((parseFloat(x.totalFailures)*10000 / (parseFloat(x.totalPasses) + parseFloat(x.totalSkipped) + parseFloat(x.totalFailures))))/100 );
    const minValue_percent = Math.min.apply(null, passed_percent) - 20;

    var canvas = document.getElementById('chart');
    var ctx = canvas.getContext('2d');
    var testChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: '% passed',
                    data: passed_percent,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    fill: 'origin'
                },
                {
                    label: '% failed',
                    data: failed_percent,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    fill: '-1'
                }]
        },
        options: {
            scales: {
                xAxes: [{
                    stacked: true
                }],
                yAxes: [{
                    stacked: true,
                    ticks: {
                        min: minValue_percent
                    }
                }]
            },
            legend: {
                display: true,
                labels: {
                    fontColor: 'rgb(255, 99, 132)'
                }
            }
        }
    });

    canvas.onclick = function(e) {
        var slice = testChart.getElementAtEvent(e);
        if (!slice.length) return; // return if not clicked on slice
        var label = slice[0]._model.label;
        let item = data.find(function(element) {
            return element.custom_start_date == label;
        });
        window.open('<?php echo BASEURL; ?>display.php?id='+item.id, '_blank');
    }
</script>
</body>
</html>