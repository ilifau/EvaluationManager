<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/abstract/class.ilObjAssignment.php');

/**
 * Module obj class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjModuleAssignment extends ilObjAssignment {

    private $title;
//Special attributes for modules
    private $lecture_name;
    private $lecturer_name;

    /**
     * Constructor
     * 
     * The constructor should receives the eval_id of the module and the ilias_obj
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     * @param integer 	ilias_obj should be the ref_id of the object to read
     * @param text 	lecture_name is the name of the lecture assigned
     * @param text 	lecturer_name is the name of the lecturer assigned
     */
    function __construct($a_eval_id, $a_ilias_obj, $lecture_name = "", $lecturer_name = "") {

        $this->setEvalId($a_eval_id);
        $this->setIliasObj($a_ilias_obj);
        $this->setAssignmentType("mod"); //Assignment type is established as mod because this is a module assignment.
        $this->setLectureName($lecture_name);
        $this->setLecturerName($lecturer_name);
    }

    /*     * ***************************************************
     * **************** GETTERS AND SETTERS ******************
     * **************************************************** */

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

    /*     * ***************************************************
     * **** INDIVIDUAL DATABASE MANAGEMENT OF MODULES *****
     * **************************************************** */

    /**
     * Insert module assignment
     * 
     * It's neccessary to create a Module assignment object before calling this method
     */
    public function insertModuleAssignment() {
        global $ilDB;
        $ilDB->insert("rep_robj_xema_assign", array(
            "eval_id" => array("integer", $this->getEvalId()),
            "ilias_obj" => array("integer", $this->getIliasObj()),
            "type" => array("text", $this->getAssignmentType()),
            "lecture_name" => array("text", $this->getLectureName()),
            "lecturer_name" => array("text", $this->getLecturerName())));

//returns the object of the module assignment inserted
        return $this;
    }

    /**
     * Update module assignment
     * 
     * @param integer 	eval_id of the module_assignment to update
     * @param object 	module_assignment should be the module_assignment object to update
     */
    public static function _updateModuleAssignment($eval_id, $module_assignment) {
        global $ilDB;

        $ilDB->replace("rep_robj_xema_assign", array(
            "eval_id" => array("integer", $eval_id),
            "ilias_obj" => array("integer", $module_assignment->getIliasObj())
                ), array(
            "type" => array("text", "mod"),
            "lecture_name" => array("text", $module_assignment->getLectureName()),
            "lecturer_name" => array("text", $module_assignment->getLecturerName())));

        $module_assignment->setEvalId($eval_id);

        return $module_assignment;
    }

    /**
     * Delete module assignment
     * 
     * @param integer 	eval_id should be the eval_id of the object to delete
     * @param integer 	ilias_obj can be the ref_id of the object to delete, if is missing
     * it's because all assignments with the eval_id should be deleted.
     */
    public static function _deleteModuleAssignment($eval_id, $ilias_obj = "") {
        global $ilDB;

        $query = "DELETE FROM rep_robj_xema_assign "
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        if ($ilias_obj) {
            $query.= " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'integer');
        }

        $ilDB->query($query);

        return true;
    }

    /**
     * Read module assignment
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     * @param integer 	ilias_obj should be the ref_id of the object to read
     */
    public static function _readModuleAssignment($eval_id, $ilias_obj) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_assign"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'integer');

        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);

        $module_assignment = new ilObjModuleAssignment($data["eval_id"], $data["ilias_obj"], $data["lecture_name"], $data["lecturer_name"]);

        return $module_assignment;
    }

    /*     * ***************************************************
     * **** MULTIPLE DATABASE MANAGEMENT OF MODULES ASSIGNMENTS*****
     * **************************************************** */

    /**
     * Read module assignment
     * 
     * @param integer 	$eval_id should be the eval_id of the module to read the assignments
     * @param boolean 	$evasys_export If true, return a special array for the evasys export just with the ilias_obj
     */
    public static function _readModuleAssignments($eval_id, $evasys_export = false) {
        global $DIC;
        $ilDB = $DIC->database();

        $rows = [];
        if (isset(static::$cache)) {
            $rows = (array) static::$cache[$eval_id];
        }
        else {
            $query = "SELECT * FROM rep_robj_xema_assign"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND type = 'mod'";
            $result = $ilDB->query($query);

            while ($row = $ilDB->fetchAssoc($result)) {
                $rows[] = $row;
            }
        }

        $module_assignments = [];
        $array_for_evasys = [];

        foreach ($rows as $data) {
            $array_for_evasys[$data["ilias_obj"]] = $data["ilias_obj"];
            $module_assignments[] = new ilObjModuleAssignment($data["eval_id"], $data["ilias_obj"], $data["lecture_name"], $data["lecturer_name"]);
        }
        if ($evasys_export) {
            return $array_for_evasys;
        } else {
            return $module_assignments;
        }
    }

    /*     * ***************************************************
     * **** CHECK DATABASE MANAGEMENT OF MODULE ASSIGNMENTS*****
     * **************************************************** */

    /**
     * This method checks the existence of a module assignment to decide if a new module assignment should be inserted
     * or a previous module assignment must be updated.
     * 
     * Returns a boolean that will be true if module assignment already exists and false if not.
     * 
     * @param integer 	eval_id of the module assignment to check their existence
     * @param integer 	ilias_obj is the ref_id of the course or group to check their existence
     */
    public static function _checkExistenceOfModuleAssignment($eval_id, $ilias_obj) {
        global $ilDB;
        $query = "SELECT eval_id FROM rep_robj_xema_assign WHERE eval_id = " . $ilDB->quote($eval_id, 'integer') .
                " AND type = 'mod'" .
                " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'integer');
        $result = $ilDB->query($query);
        if ($ilDB->fetchAssoc($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method updates an assignment from an array of assignments without change anything into the database
     * 
     * Returns the new array of lecture assignments
     * 
     * @param array 	lecture_assignments is an array of lecture assignments objects
     * @param object 	updated_assignment is the new lecture assignment to insert into the array
     */
    public static function _addModuleAssignmentToList($module_assignments, $updated_assignment) {

        if (is_array($module_assignments)) {
            foreach ($module_assignments as $key => $module) {
                if ($module->getEvalId() == $updated_assignment->getEvalId()) {
                    unset($module_assignments[$key]);
                }
            }
            $module_assignments[] = $updated_assignment;
        }
        return $module_assignments;
    }

    /**
     * This method gets the title of an assignment from the database
     * 
     * Returns the title of the Ilias Obj of module assignments
     * 
     */
    public function getTitle() {
        global $ilDB;
        $query = "SELECT d.title FROM rep_robj_xema_assign a, object_reference o, object_data d" .
                " WHERE a.eval_id = " . $ilDB->quote($this->getEvalId(), 'integer') .
                " AND a.ilias_obj = " . $ilDB->quote($this->getIliasObj(), 'integer') .
                " AND a.type = 'mod'" .
                " AND a.ilias_obj = o.ref_id" .
                " AND o.obj_id = d.obj_id";
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        if ($row) {
            $this->title = $row["title"];
            return $this->title;
        }
    }

    /*     * ***************************************************
     * **** METHODS FOR SHOWING LECTURE ASSIGNMENTS*****
     * **************************************************** */

    /**
     * 
     * @global type $ilDB
     * @param integer $eval_id is the id of the entry to show their assignments
     * @return array
     */
    public static function _getAssignmentData($eval_id) {
        global $ilDB, $lng;

        $info = array();
        $info_array = array();

        $counter = 0;
        $query = "SELECT * FROM rep_robj_xema_assign WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $info[$counter]["ref_id"] = $data["ilias_obj"];
            if ($data["type"] == "mod") {
                $info_array[$counter]["lecture_name"] = $data["lecture_name"];
                $info_array[$counter]["lecturer_name"] = $data["lecturer_name"];
            }
            $query2 = "SELECT obj_id FROM object_reference WHERE ref_id = " . $ilDB->quote($data["ilias_obj"], 'integer');
            $result2 = $ilDB->query($query2);
            $data2 = $ilDB->fetchAssoc($result2);
            if ($data2) {
                $info[$counter]["obj_id"] = $data2["obj_id"];
            }
            $query3 = "SELECT type, title FROM object_data WHERE obj_id = " . $ilDB->quote($data2["obj_id"], 'integer');
            $result3 = $ilDB->query($query3);
            $data3 = $ilDB->fetchAssoc($result3);
            if ($data3) {
                $info[$counter]["type"] = $data3["type"];
                $info[$counter]["title"] = $data3["title"];
            }
            $counter++;
        }

        $counter2 = 0;

        include_once("./Services/Tree/classes/class.ilPathGUI.php");
        $path = new ilPathGUI();
        $path->enableTextOnly(false);

        foreach ($info as $row) {
            $info_array[$counter2]["ref_id"] = $row["ref_id"];
            $info_array[$counter2]["title"] = $row["title"];
            $info_array[$counter2]["path"] = $path->getPath(ROOT_FOLDER_ID, $row["ref_id"]);
            $info_array[$counter2]["link"] = ilLink::_getStaticLink($row["ref_id"], $row['type']);
            $lectures = ilObjLectureAssignment::_getLecturesAssigned($row["ref_id"]);
            $info_array[$counter2]["evaluation"] = array();
            foreach ($lectures as $lecture) {
                $info_array[$counter2]["evaluation"][] = $lng->txt("rep_robj_xema_lecture_short_label") . $lecture["eval_name"];
            }
            $modules = ilObjLectureAssignment::_getModulesAssigned($row["ref_id"]);
            foreach ($modules as $module) {
                $info_array[$counter2]["evaluation"][] = $lng->txt("rep_robj_xema_module_short_label") . $module["eval_name"];
            }
            $counter2++;
        }

        return $info_array;
    }

    public static function _getLecturesAssigned($ref_id) {
        global $ilDB;

        $lectures = array();
        $counter = 0;

        $query = "SELECT eval_id FROM rep_robj_xema_assign WHERE type = 'lec' AND ilias_obj = " . $ilDB->quote($ref_id, 'integer');

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $lectures[$counter]["eval_id"] = $data["eval_id"];
            $query2 = "SELECT eval_name FROM rep_robj_xema_eval WHERE eval_id = " . $ilDB->quote($data["eval_id"], 'integer');

            $result2 = $ilDB->query($query2);
            $data2 = $ilDB->fetchAssoc($result2);
            if ($data2) {
                $lectures[$counter]["eval_name"] = $data2["eval_name"];
            }
            $counter++;
        }
        return $lectures;
    }

    public static function _getModulesAssigned($ref_id) {
        global $ilDB;

        $modules = array();
        $counter = 0;

        $query = "SELECT eval_id FROM rep_robj_xema_assign WHERE type = 'mod' AND ilias_obj = " . $ilDB->quote($ref_id, 'integer');

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $modules[$counter]["eval_id"] = $data["eval_id"];
            $query2 = "SELECT eval_name FROM rep_robj_xema_eval WHERE eval_id = " . $ilDB->quote($data["eval_id"], 'integer');

            $result2 = $ilDB->query($query2);
            $data2 = $ilDB->fetchAssoc($result2);
            if ($data2) {
                $modules[$counter]["eval_name"] = $data2["eval_name"];
            }
            $counter++;
        }
        return $modules;
    }

}

?>
