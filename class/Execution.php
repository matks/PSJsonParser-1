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
    private $suites;
    private $tests;
    private $skipped;
    private $passes;
    private $failures;
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

        $id = $this->db->insert('execution', [
            'ref' => $this->ref,
            'version' => $this->version,
            'duration' => $this->stats->duration,
            'start_date' => $this->format_datetime($this->stats->start),
            'end_date' => $this->format_datetime($this->stats->end),
            'suites' => $this->stats->suites,
            'tests' => $this->stats->tests,
            'skipped' => $this->stats->skipped,
            'passes' => $this->stats->passes,
            'failures' => $this->stats->failures ,
        ]);
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
        $this->suites = $row->suites;
        $this->tests = $row->tests;
        $this->skipped = $row->skipped;
        $this->passes = $row->passes;
        $this->failures = $row->failures;

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

    function getStartDate() {
        return $this->start_date;
    }

    function getEndDate() {
        return $this->end_date;
    }

    function getPassed() {
        return $this->passes;
    }

    function getSkipped() {
        return $this->skipped;
    }

    function getFailed() {
        return $this->failures;
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

    function getVersions() {
        return $this->db->select('DISTINCT(version) FROM execution;');
    }

    function getCustomData($criteria) {
        $req = "e.id, e.ref, e.start_date, e.end_date, e.skipped, e.passes, e.failures,
            SUM(IF(t.state = 'skipped', 1, 0)) totalSkipped, SUM(IF(t.state = 'passed', 1, 0)) totalPasses, SUM(IF(t.state = 'failed', 1, 0)) totalFailures
            FROM execution e
            INNER JOIN suite s ON e.id=s.execution_id
            INNER JOIN test t ON s.id = t.suite_id
            WHERE 1=1
            AND e.version = :version
            AND e.start_date BETWEEN :start_date AND :end_date
            ";
        if (isset($criteria['campaign']) && $criteria['campaign'] != '') {
            $req .= "
               AND s.campaign = :campaign
            ";
        } else {
            unset($criteria['campaign']);
        }
        $req .= "
            GROUP BY e.id, e.start_date, e.end_date, e.skipped, e.passes, e.failures";
        return $this->db->select($req, $criteria);
    }

    private function format_datetime($value) {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}