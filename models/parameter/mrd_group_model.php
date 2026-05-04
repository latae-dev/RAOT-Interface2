<?php

class MrdGroupModel
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function readMrdGroup()
    {
        $sql = "SELECT id, name, code FROM parameters.tb_wrd_groups ORDER BY name ASC";
        $result = pg_query($this->db, $sql);

        if (!$result) {
            return false;
        }

        return pg_fetch_all($result);
    }

    public function readMrdGroupOne($id)
    {
        $sql = "SELECT id, name, code FROM parameters.tb_wrd_groups WHERE id = $1";
        $result = pg_query_params($this->db, $sql, array($id));

        if (!$result) {
            return false;
        }

        return pg_fetch_assoc($result);
    }
}
