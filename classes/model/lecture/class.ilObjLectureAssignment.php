<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/abstract/class.ilObjAssignment.php');

/**
 * Lecture obj class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjLectureAssignment extends ilObjAssignment {

    private $title;

    /**
     * Constructor
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     * @param integer 	ilias_obj should be the ref_id of the object to read
     * The constructor should receives the eval_id of the lecture and the ilias_obj
     */
    function __construct($a_eval_id, $a_ilias_obj) {

        $this->setEvalId($a_eval_id);
        $this->setIliasObj($a_ilias_obj);
        $this->setAssignmentType("lec"); //Assignment type is established as lec because this is a lecture assignment.
    }

    /*     * ***************************************************
     * INDIVIDUAL DATABASE MANAGEMENT OF LECTURE ASSIGNMENTS ***
     * **************************************************** */

    /**
     * Insert lecture assignment
     * 
     * It's neccessary to create a Lecture assignment object before calling this method
     */
    public function insertLectureAssignment() {
        global $ilDB;
        $ilDB->insert("rep_robj_xema_assign", array(
            "eval_id" => array("integer", $this->getEvalId()),
            "ilias_obj" => array("integer", $this->getIliasObj()),
            "type" => array("text", $this->getAssignmentType())));

        //returns the object of the lecture assignment inserted
        return $this;
    }

    /**
     * Update lecture assignment
     * 
     * @param integer 	eval_id of the lecture_assignment to update
     * @param object 	lecture_assignment should be the lecture_assignment object to update
     */
    public static function _updateLectureAssignment($eval_id, $lecture_assignment) {
        global $ilDB;

        $ilDB->replace("rep_robj_xema_assign", array(
            "eval_id" => array("integer", $eval_id),
            "ilias_obj" => array("integer", $lecture_assignment->getIliasObj())
                ), array(
            "type" => array("text", $lecture_assignment->getAssignmentType())));

        $lecture_assignment->setEvalId($eval_id);

        return $lecture_assignment;
    }

    /**
     * Delete lecture assignment
     * 
     * @param integer 	eval_id should be the eval_id of the object to delete
     * @param integer 	ilias_obj can be the ref_id of the object to delete, if is missing
     * it's because all assignments with the eval_id should be deleted.
     */
    public static function _deleteLectureAssignment($eval_id, $ilias_obj = "") {
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
     * Read lecture assignment
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     * @param integer 	ilias_obj should be the ref_id of the object to read
     */
    public static function _readLectureAssignment($eval_id, $ilias_obj) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_assign"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND ilias_obj = " . $ilDB->quote($ilias_obj, 'integer')
                . " AND type = 'lec'";

        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);

        $lecture_assignment = new ilObjLectureAssignment($data["eval_id"], $data["ilias_obj"]);

        return $lecture_assignment;
    }

    /*     * ***************************************************
     * **** MULTIPLE DATABASE MANAGEMENT OF LECTURES ASSIGNMENTS*****
     * **************************************************** */

    /**
     * Read lecture assignment
     * 
     * @param integer 	$eval_id should be the eval_id of the lecture to read the assignmen$eval_idts
     * @param boolean 	$evasys_export If true, return a special array for the evasys export just with the ilias_obj
     */
    public static function _readLectureAssignments($eval_id, $evasys_export = false) {
        global $ilDB;
        $lecture_assignments = array();
        if ($evasys_export) {
            $array_for_evasys = array();
        }

        $query = "SELECT * FROM rep_robj_xema_assign"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND type = 'lec'";

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            if ($evasys_export) {
                $array_for_evasys[$data["ilias_obj"]] = $data["ilias_obj"];
            }
            $lecture_assignment = new ilObjLectureAssignment($data["eval_id"], $data["ilias_obj"]);
            $lecture_assignments[] = $lecture_assignment;
        }
        if ($evasys_export) {
            return $array_for_evasys;
        } else {
            return $lecture_assignments;
        }
    }

    /*     * ***************************************************
     * **** CHECK DATABASE MANAGEMENT OF LECTURE ASSIGNMENTS*****
     * **************************************************** */

    /**
     * This method checks the existence of a lecture assignment to decide if a new lecture assignment should be inserted
     * or a previous lecture assignment must be updated.
     * 
     * Returns a boolean that will be true if lecture assignment already exists and false if not.
     * 
     * @param integer 	eval_id of the lecture assignment to check their existence
     * @param integer 	ilias_obj is the ref_id of the course or group to check their existence
     */
    public static function _checkExistenceOfLectureAssignment($eval_id, $ilias_obj) {
        global $ilDB;
        $query = "SELECT eval_id FROM rep_robj_xema_assign WHERE eval_id = " . $ilDB->quote($eval_id, 'integer') .
                " AND type = 'lec'" .
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
    public static function _addLectureAssignmentToList($lecture_assignments, $updated_assignment) {

        if (is_array($lecture_assignments)) {
            foreach ($lecture_assignments as $key => $lecture) {
                if ($lecture->getEvalId() == $updated_assignment->getEvalId()) {
                    unset($lecture_assignments[$key]);
                }
            }
            $lecture_assignments[] = $updated_assignment;
        }
        return $lecture_assignments;
    }

    /**
     * This method gets the title of an assignment from the database
     * 
     * Returns the title of the Ilias Obj of lecture assignments
     * 
     */
    public function getTitle() {
        global $ilDB;
        $query = "SELECT d.title FROM rep_robj_xema_assign a, object_reference o, object_data d" .
                " WHERE a.eval_id = " . $ilDB->quote($this->getEvalId(), 'integer') .
                " AND a.ilias_obj = " . $ilDB->quote($this->getIliasObj(), 'integer') .
                " AND a.type = 'lec'" .
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
        $counter = 0;
        $query = "SELECT * FROM rep_robj_xema_assign WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $info[$counter]["ref_id"] = $data["ilias_obj"];
            if ($data["type"] == "mod") {
                $info[$counter]["lecture_name"] = $data["lecture_name"];
                $info[$counter]["lecturer_name"] = $data["lecturer_name"];
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

        $info_array = array();
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
