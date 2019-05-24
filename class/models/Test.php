<?php

class Test extends Model
{
    private $uuid;
    private $id;
    private $title;
    private $state;
    private $duration;
    private $err = [
        'message' => null,
        'diff' => null,
        'estack' => null
    ];
    private $suite_id = null;

    /**
     * @param $test
     * @return $this
     */
    function populate($test) {
        $this->uuid         = $test->uuid;
        $this->title        = $test->title;
        $this->state        = $test->state;
        $this->duration     = $test->duration;

        if (isset($test->err->message)) {
            $this->err['message']   = Tools::sanitize($test->err->message);
        }
        if (isset($test->err->diff)) {
            $this->err['diff']      = Tools::sanitize($test->err->diff);
        }
        if (isset($test->err->estack)) {
            $this->err['estack']    = Tools::sanitize($test->err->estack);
        }
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    function setSuiteId($id) {
        $this->suite_id = $id;
        return $this;
    }

    /**
     * @return $this
     */
    function insert() {
        //check if this uuid exists for this execution before inserting
        $check = $this->checkExistence($this->uuid, $this->suite_id);
        $data = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'state' => $this->state,
            'duration' => $this->duration,
            'error_message' => $this->err['message'],
            'stack_trace' => $this->err['estack'],
            'diff' => $this->err['diff'],
            'suite_id' => $this->suite_id,
        ];
        $this->id = $this->db->insert('test', $data);
        return $this;
    }

    /**
     * @param $suite_id
     * @return row
     */
    function getBySuiteId($suite_id) {
        return $this->db->select('* FROM test WHERE suite_id = :suite_id ORDER BY id ASC', [':suite_id' => $suite_id]);
    }

    function getAllByExecutionId($execution_id) {
        $sth = $this->db->prepare("
        SELECT t.*
        FROM test t 
        INNER JOIN suite s ON t.suite_id = s.id
        WHERE s.execution_id = :execution_id
        ORDER BY t.id;");
        $sth->execute(['execution_id' => $execution_id]);
        return $sth->fetchAll(PDO::FETCH_OBJ);
    }

    function getAllTestsByFile($execution_id, $campaign, $file)
    {
        return $this->db->select("t.* FROM test t INNER JOIN suite s ON s.id=t.suite_id WHERE s.campaign=:campaign AND s.file=:file AND s.execution_id=:execution_id;", ['execution_id' => $execution_id, 'campaign' => $campaign, 'file' => $file]);
    }

    function getSubset($execution_id, $criteria)
    {
        return $this->db->select('* FROM test t INNER JOIN suite s ON s.id=t.suite_id WHERE s.execution_id=:execution_id AND error_message LIKE :criteria;', ['execution_id' => $execution_id, 'criteria' => '%'.$criteria.'%']);
    }

}