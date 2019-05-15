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

    function init($file) {
        $this->file = json_decode(file_get_contents($file));

        $execution = new Execution($this->db);
        $this->execution = $execution->create('1.7.6.x', $this->file->stats);
        return $this;
    }

    function save() {
        //parse through
        $suite = new Suite($this->db);
        $suite->populate($this->file->suites)->setParentId((null));

        $this->loop_through($suite);
    }

    function loop_through($suite) {


        if ($suite->getCampaign() != $this->suite_campaignname) {
            $this->suite_campaignname = $suite->getCampaign();
        }
        if ($suite->getFilename() != $this->suite_filename) {
            $this->suite_filename = $suite->getFilename();
        }

        //insertion de la suite
        $suite->setExecutionId($this->execution->getId());
        $suite->insert();

        //insert tests
        if ($suite->getTests()) {
            foreach($suite->getTests() as $test) {
                $cur_test = new Test($this->db);
                $cur_test->populate($test)->setScenarioId($suite->getId())->insert();
            }
        }

        if ($suite->getChildren()) {
            foreach($suite->getChildren() as $s) {
                $cur_suite = new Suite($this->db);
                $cur_suite->populate($s)->setParentId(($suite->getId()));
                $this->loop_through($cur_suite);
            }
        }
    }
}