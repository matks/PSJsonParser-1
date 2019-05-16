<?php

class Execution
{
    private $id;
    private $ref;
    private $stats;
    private $start_date;
    private $end_date;
    private $duration;
    private $version;
    private $db;

    /**
     * Execution constructor.
     * @param $db
     */
    function __construct($db) {
        $this->db = $db;
        return $this;
    }

    /**
     * @param $version
     * @param $stats
     * @return mixed
     * @throws Exception
     */
    function create($version, $stats) {
        $this->ref = date('YmdHis');
        $this->version = $version;
        $this->stats = $stats;

        $id = $this->db->insert('execution', ['ref' => $this->ref, 'version' => $this->version, 'duration' => $this->stats->duration, 'start_date' => $this->format_datetime($this->stats->start), 'end_date' => $this->format_datetime($this->stats->end)]);
        return $this->populate($id);
    }

    /**
     * @param $id
     * @return $this
     * @throws Exception
     */
    function populate($id) {
        $row = $this->db->find('* FROM execution WHERE id = :id', [':id' => $id]);
        if (!$row) {
            throw new Exception("Could not find an Execution with the id $id.");
        }
        $this->id = $row->id;
        $this->ref = $row->ref;
        $this->start_date = $row->start_date;
        $this->end_date = $row->end_date;
        $this->version = $row->version;

        return $this;
    }

    /**
     * @return mixed
     */
    function getId() {
        return $this->id;
    }

    function getRef() {
        return $this->ref;
    }

    function getTotalDuration() {
        $start = strtotime($this->start_date);
        $end = strtotime($this->end_date);
        $difference = $end - $start;

        $hours = floor($difference / 3600);
        $minutes = floor(($difference / 60) % 60);
        $seconds = $difference % 60;
        return $hours.'h'.$minutes.'m'.$seconds.'s';
    }

    private function format_datetime($value) {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}