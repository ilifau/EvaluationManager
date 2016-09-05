<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Modules Assignment class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjModuleAssignment {

    private $eval_id;
    private $ilias_obj;
    private $lecture_name;
    private $lecturer_name;

    /**
     * Constructor
     * 
     * @param array 	data
     * The constructor should receive a data array.
     */
    function __construct($a_eval_id, $a_ilias_obj, $lecture_name = "", $lecturer_name = "") {

        $this->setEvalId($a_eval_id);
        $this->setIliasObj($a_ilias_obj);
        $this->setLectureName($lecture_name);
        $this->setLecturerName($lecturer_name);
    }

    /*
     * Getters and setters for atributes
     */

    public function getEvalId() {
        return $this->eval_id;
    }

    public function setEvalId($var) {
        $this->eval_id = $var;
    }

    public function getIliasObj() {
        return $this->ilias_obj;
    }

    public function setIliasObj($var) {
        $this->ilias_obj = $var;
    }

    public function getLectureName() {
        return $this->lecture_name;
    }

    public function setLectureName($var) {
        $this->lecture_name = $var;
    }

    public function getLecturerName() {
        return $this->lecturer_name;
    }

    public function setLecturerName($var) {
        $this->lecturer_name = $var;
    }

    //DB Management

    /*
     * CRUD Operations
     */


    public function insertModuleAssignment() {
        global $ilDB;
        $ilDB->insert("rep_robj_xema_as_mod", array(
            "eval_id" => array("integer", $this->getEvalId()),
            "ilias_obj" => array("integer", $this->getIliasObj()),
            "lecture_name" => array("text", $this->getLectureName()),
            "lecturer_name" => array("text", $this->getLecturerName())
        ));
    }

    public function updateModuleAssignment($eval_id, $ilias_obj, $lecture_name, $lecturer_name) {
        global $ilDB;

        $ilDB->replace("rep_robj_xema_as_mod", array(
            "eval_id" => array("integer", $eval_id),
            "ref_id" => array("integer", $ilias_obj)
                ), array(
            "lecture_name" => array("text", $lecture_name),
            "lecturer_name" => array("text", $lecturer_name)));

        return true;
    }

    //DELETE
    public static function _deleteModuleAssignment($eval_id, $ilias_obj) {
        global $ilDB;

        $query = "DELETE FROM rep_robj_xema_as_mod "
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'integer');

        $ilDB->query($query);
    }

    //READ
    public static function _readModuleAssignment($eval_id, $ilias_obj) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_as_mod"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'integer');

        $result = $ilDB->query($query);
        return $ilDB->fetchAssoc($result);
    }

    //READ ALL ASSIGNMENTS OF A MODULE
    public static function _readModuleAssignments($eval_id) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_as_mod"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $results[$row["ilias_obj"]] = $row["ilias_obj"];
        }
        return $results;
    }

    /*
     * This function return the eval_id and ilias_obj or false depending if the module assignment is already
     * inserted into the DB as a current evaluation manager module assignment or not
     */

    public static function _moduleAssignmentExists($eval_id, $ilias_obj) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_as_mod WHERE eval_id = " . $ilDB->quote($eval_id, 'text') .
                " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'text');
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        if ($row) {
            $data = array();
            $data["eval_id"] = $row["eval_id"];
            $data["ilias_obj"] = $row["ilias_obj"];
            return $data;
        } else {
            return false;
        }
    }

    /*
     * This function check the assignments of a module and call delete assignments
     */

    public static function _checkDeleteModuleAssignments($eval_id) {
        global $ilDB;

        $query = "SELECT eval_id, ilias_obj FROM rep_robj_xema_as_mod WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            ilObjModuleAssignment::_deleteModuleAssignment($row["eval_id"], $row["ilias_obj"]);
        }
    }

}

?>