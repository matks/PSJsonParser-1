<?php

class Test
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
    private $scenario_id = null;
    private $db;

    /**
     * Test constructor.
     * @param $db
     */
    function __construct($db) {
        $this->db = $db;
        return $this;
    }

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
            $this->err['message']   = $this->sanitize($test->err->message);
        }
        if (isset($test->err->diff)) {
            $this->err['diff']      = $this->sanitize($test->err->diff);
        }
        if (isset($test->err->estack)) {
            $this->err['estack']    = $this->sanitize($test->err->estack);
        }
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    function setScenarioId($id) {
        $this->scenario_id = $id;
        return $this;
    }

    /**
     * @return $this
     */
    function insert() {
        $data = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'state' => $this->state,
            'duration' => $this->duration,
            'error_message' => $this->err['message'],
            'stack_trace' => $this->err['estack'],
            'diff' => $this->err['diff'],
            'scenario_id' => $this->scenario_id,
        ];
        $this->id = $this->db->insert('test', $data);
        return $this;
    }

    /**
     * @param $scenario_id
     * @return row
     */
    function getByScenarioId($scenario_id) {
        return $this->db->select('* FROM test WHERE scenario_id = :scenario_id ORDER BY id ASC', [':scenario_id' => $scenario_id]);
    }

    private function sanitize($text) {
        $StrArr = str_split($text);
        $NewStr = '';
        foreach ($StrArr as $Char) {
            $CharNo = ord($Char);
            if ($CharNo == 163) { $NewStr .= $Char; continue; } // keep £
            if ($CharNo > 31 && $CharNo < 127) {
                $NewStr .= $Char;
            }
        }
        return $NewStr;
    }



}