<?php

class Model
{
    protected $db;

    function __construct($db)
    {
        $this->db = $db;
    }
}