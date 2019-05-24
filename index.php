<?php

require_once('config.php');

$cache = new Cache('index');

//display versions labels
$execution = new Execution($db);
$versions = $execution->getVersions();
$versions_html = '';
if (sizeof($versions) > 1) {
    foreach($versions as $version) {
        $versions_html .= '<span class="label filter_version active" data-for="version_'.str_replace('.', '', $version->version).'" data-active="true">'.$version->version.'</span>';
    }
}

//table content
$execution = new Execution($db);
$execution_list = $execution->getAllInformation();

$table_html = '';
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
        $table_html .= '<tr class="version_'.str_replace('.', '', $elem->getVersion()).'">';
        $table_html .= '<td><a href="report.php?id='.$elem->getId().'" target="_blank"><i class="material-icons">visibility</i> Show report</a></td>';
        $table_html .= '<td>'.date('d/m/Y', strtotime($elem->getStartDate())).'</td>';
        $table_html .= '<td>'.$elem->getVersion().'</td>';
        $table_html .= '<td>'.date('H:i', strtotime($elem->getStartDate())).' - '.date('H:i', strtotime($elem->getEndDate())).' ('.$elem->getTotalDuration().')</td>';
        $table_html .= '<td>'.$content.'</td>';
        $table_html .= '</tr>';
    }
} else {
    $table_html .= '<tr><td colspan="5">No data available</td></tr>';
}

$layout = Layout::get();
$view = new Template('index');
$view->set(['title' => 'Tests recap']);
$view->set(['links' => '<a class="link" href="'.BASEURL.'graph.php"><i class="material-icons">timeline</i> Graph</a>']);
$view->set(['filters' => $versions_html]);
$view->set(['table_content' => $table_html]);

$layout->setView($view);
$layout->render();

$cache->store();