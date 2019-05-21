<?php

require_once('config.php');

$start_date = date('Y-m-d', strtotime('-2 weeks'));
$end_date = date('Y-m-d');

$version = '';
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
                <form action="" method="GET" id="displayform">
                    <div class="form_block">
                        <label for="start_date">
                            Start date
                            <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>"/>
                        </label>
                    </div>
                    <div class="form_block">
                        <label for="end_date">
                            End date
                            <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>"/>
                        </label>
                    </div>
                    <div class="form_block">
                        <label for="version">
                            Version
                            <select name="version" id="version">
                                <?php
                                    foreach($version as $v) {
                                        $s = ($version == $v->version) ? 'selected' : '';
                                        echo '<option value="'.$v->version.'" '.$s.'>'.$v->version.'</option>';
                                    }
                                ?>
                            </select>
                        </label>
                    </div>
                </form>
            </div>
        </div>




    </div>
</div>
</body>
</html>