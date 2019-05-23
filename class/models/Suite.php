<?php

class Suite extends Model
{

    private $execution_id;
    private $id;
    private $uuid;
    private $title;
    private $filename;
    private $campaign;
    private $suites;
    private $tests;
    private $file;
    private $duration;
    private $hasSkipped;
    private $hasPasses;
    private $hasFailures;
    private $totalSkipped;
    private $totalPasses;
    private $totalFailures;
    private $hasSuites;
    private $hasTests;
    private $parent_id = null;

    /**
     * @param $suite
     * @return $this
     */
    function populate($suite) {
        $this->uuid = $suite->uuid;
        $this->title = $suite->title;
        $this->suites = $suite->suites;
        $this->tests = $suite->tests;
        $this->filename = $suite->file;
        $this->duration = $suite->duration;
        $this->hasSkipped = $suite->hasSkipped;
        $this->hasPasses = $suite->hasPasses;
        $this->hasFailures = $suite->hasFailures;
        $this->totalSkipped = $suite->totalSkipped;
        $this->totalPasses = $suite->totalPasses;
        $this->totalFailures = $suite->totalFailures;
        $this->hasSuites = $suite->hasSuites;
        $this->hasTests = $suite->hasTests;
        $this->file = Tools::extractNames($this->filename, 'file');
        $this->campaign = Tools::extractNames($this->filename, 'campaign');
        return $this;
    }

    /**
     * @param $execution_id
     * @return $this
     */
    function setExecutionId($execution_id) {
        $this->execution_id = $execution_id;
        return $this;
    }

    /**
     * @param $parent_id
     * @return $this
     */
    function setParentId($parent_id) {
        $this->parent_id = $parent_id;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     * @return int
     */
    function insert() {
        if (!$this->execution_id) {
            throw new Exception("Please provide an execution ID");
        }
        if ($this->checkExistence()) {
            //Houston, we have a problem in this JSON... let's skip this one
            return false;
        }
        $data = [
            'execution_id'  => $this->execution_id,
            'uuid'          => $this->uuid,
            'title'         => $this->title,
            'campaign'      => $this->campaign,
            'file'          => $this->file,
            'duration'      => $this->duration,
            'hasSkipped'      => $this->hasSkipped ? 1 : 0,
            'hasPasses'      => $this->hasPasses ? 1 : 0,
            'hasFailures'      => $this->hasFailures ? 1 : 0,
            'totalSkipped'      => $this->totalSkipped,
            'totalPasses'      => $this->totalPasses,
            'totalFailures'      => $this->totalFailures,
            'hasSuites'      => $this->hasSuites ? 1 : 0,
            'hasTests'      => $this->hasTests ? 1 : 0,
            'parent_id'     => $this->parent_id
        ];
        $this->id = $this->db->insert('suite', $data);
        return $this;
    }

    /**
     * @param $parent_id
     * @return int
     */
    function getAllSuiteByParent($parent_id) {
        return $this->db->select('* FROM suite WHERE parent_id = :parent_id', [':parent_id' => $parent_id]);
    }

    /**
     * @return |null
     */
    function getChildren() {
        return empty($this->suites) ? null : $this->suites;
    }

    function getTests() {
        return empty($this->tests) ? null : $this->tests;
    }

    function getCampaign() {
        return $this->campaign;
    }

    function getFilename() {
        return $this->filename;
    }

    function getId() {
        return $this->id;
    }

    function getAllByExecutionId($execution_id) {
        return $this->db->select('* FROM suite WHERE execution_id = :execution_id ORDER BY campaign, id', ['execution_id' => $execution_id]);
    }

    function getAllCampaignsAndFilesByExecutionId($execution_id)
    {
        return $this->db->select(" 
                s.campaign, 
                SUM(IF(t.state = 'skipped', 1, 0)) hasSkipped, 
                SUM(IF(t.state = 'failed', 1, 0)) hasFailed, 
                SUM(IF(t.state = 'passed', 1, 0)) hasPassed, 
                file 
            FROM suite s
            INNER JOIN test t ON t.suite_id = s.id
            WHERE s.execution_id = :execution_id
            AND s.campaign IS NOT NULL 
            GROUP BY s.campaign, s.file 
            ORDER BY s.campaign, s.file", ['execution_id' => $execution_id]);
    }

    function getCampaigns() {
        return $this->db->select('DISTINCT(campaign) FROM suite WHERE campaign IS NOT NULL ORDER BY campaign;');
    }

    private function checkExistence() {
        return $this->db->select("* FROM suite WHERE uuid=:uuid AND execution_id=:execution_id;", ['uuid' => $this->uuid, 'execution_id' => $this->execution_id]);
    }
}