<?php

class Suite
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
    private $parent_id = null;

    private $db;

    /**
     * Suite constructor.
     * @param $db
     */
    function __construct($db) {
        $this->db = $db;
        return $this;
    }

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
        $this->extractNames();
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
        return $this->db->select("campaign, file FROM suite WHERE execution_id = :execution_id AND campaign IS NOT NULL GROUP BY campaign, file ORDER BY campaign, file;", ['execution_id' => $execution_id]);
    }

    private function extractNames() {
        if (strlen($this->filename) > 0) {
            $pattern = '/\/full\/(.*?)\/(.*)/';
            preg_match($pattern, $this->filename, $matches);
            $this->campaign = isset($matches[1]) ? $matches[1] : "";
            $this->file = isset($matches[2]) ? $matches[2] : "";
        } else {
            return "";
        }
    }

    function getCampaigns() {
        return $this->db->select('DISTINCT(campaign) FROM suite WHERE campaign IS NOT NULL ORDER BY campaign;');
    }

    private function checkExistence() {
        return $this->db->select("* FROM suite WHERE uuid=:uuid AND execution_id=:execution_id;", ['uuid' => $this->uuid, 'execution_id' => $this->execution_id]);
    }
}