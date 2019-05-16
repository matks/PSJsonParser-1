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
        $this->campaign = $this->extract_from_filename('campaign');
        $this->file = $this->extract_from_filename('filename');
        $this->duration = $suite->duration;
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


    /**
     * @param $type
     * @return string
     */
    private function extract_from_filename($type) {
        if(strlen($this->filename) > 0) {
            switch ($type) {
                case 'campaign':
                    $pattern = '/\/full\/(.*)\//';
                    preg_match($pattern, $this->filename, $matches);
                    return isset($matches[1]) ? $matches[1] : "";
                    break;
                case 'filename':
                    $pos = strrpos($this->filename, '/');
                    return substr($this->filename, $pos + 1);
                    break;
                default:
                    return "";
            }
        } else {
            return '';
        }
    }
}