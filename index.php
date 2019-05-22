<?php

require_once('config.php');

$execution = new Execution($db);
$execution_list = $execution->getAllInformation();

?>
<html>
<head>
    <title>Nightly testing</title>
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
            <a class="link" href="<?php echo BASEURL; ?>graph.php">
                <i class="material-icons">timeline</i> Graph
            </a>
        </div>
        <div class="title">
            <h2>Tests recap</h2>
        </div>
    </div>
</div>
<div class="container">
    <div class="details">
        <div class="table_container">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Version</th>
                        <th>Duration</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (sizeof($execution_list) > 0) {
                    foreach($execution_list as $item) {
                        $exec = new Execution($db);
                        $elem = $exec->populate($item->id);

                        $content = '';
                        if ($elem->getPassed() > 0) {
                            $content .= '<div class="content_block count_passed" title="Tests passed"><i class="material-icons">check_circle_outline</i> '.$elem->getPassed().'</div>';
                        }
                        if ($elem->getFailed() > 0) {
                            $content .= '<div class="content_block count_failed" title="Tests failed"><i class="material-icons">highlight_off</i> '.$elem->getFailed().'</div>';
                        }
                        if ($elem->getSkipped() > 0) {
                            $content .= '<div class="content_block count_skipped" title="Tests skipped"><i class="material-icons">radio_button_checked</i> '.$elem->getSkipped().'</div>';
                        }
                        echo '<tr>';
                        echo '<td><a href="display.php?id='.$elem->getId().'" target="_blank"><i class="material-icons">visibility</i> Show report</a></td>';
                        echo '<td>'.date('d/m/Y', strtotime($elem->getStartDate())).'</td>';
                        echo '<td>'.$elem->getVersion().'</td>';
                        echo '<td>'.date('H:i', strtotime($elem->getStartDate())).' - '.date('H:i', strtotime($elem->getEndDate())).' ('.$elem->getTotalDuration().')</td>';
                        echo '<td>'.$content.'</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No data available</td></tr>';
                }

                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>