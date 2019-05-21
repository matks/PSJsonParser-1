<?php

class Parser
{
    private $db;
    private $execution;
    private $file;
    private $suite_filename = '';
    private $suite_campaignname = '';

    function __construct($db) {
        $this->db = $db;
    }

    function init($version, $file) {
        $this->file = json_decode(file_get_contents($file));

        $execution = new Execution($this->db);
        $this->execution = $execution->create($version, $this->file->stats);

        //parse through
        $suite = new Suite($this->db);
        $suite->populate($this->file->suites)->setParentId(null);

        $this->loop_through($suite);
    }

    private function loop_through($suite) {


        if ($suite->getCampaign() != $this->suite_campaignname) {
            $this->suite_campaignname = $suite->getCampaign();
        }
        if ($suite->getFilename() != $this->suite_filename) {
            $this->suite_filename = $suite->getFilename();
        }

        //inserting current suite
        $suite->setExecutionId($this->execution->getId());
        $result = $suite->insert();

        if ($result) {
            //insert tests
            if ($suite->getTests()) {
                foreach($suite->getTests() as $test) {
                    $cur_test = new Test($this->db);
                    $cur_test->populate($test)->setSuiteId($suite->getId())->insert();
                }
            }

            if ($suite->getChildren()) {
                foreach($suite->getChildren() as $s) {
                    $cur_suite = new Suite($this->db);
                    $cur_suite->populate($s)->setParentId(($suite->getId()));
                    $this->loop_through($cur_suite);
                }
            }
        } else {
            //damn, this suite already existed...
            //we don't want to abort, just log this
            echo "[WARN] Suite already present in database, skipping...<br />";
        }
    }
}