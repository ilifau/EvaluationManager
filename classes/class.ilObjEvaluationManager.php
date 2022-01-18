<?php

/**
 * fim
 * Institut für Lern-Innovation
 * Friedrich-Alexander-Universität
 * Erlangen-Nürnberg
 * Germany
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLecture.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLectureAssignment.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLectureKeyword.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModule.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModuleAssignment.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModuleKeyword.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilObjEvaluationManagerFilter.php');

/**
 * Application class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjEvaluationManager extends ilObjectPlugin {

    private $lectures = array();
    private $modules = array();
	private $root_ref_id = null;

    /**
     * Constructor
     *
     * @access	public
     */
    function __construct($a_ref_id = 0) {
        parent::__construct($a_ref_id);
    }

    /**
     * Get type.
     */
    public final function initType() {
        $this->setType("xema");
    }

    /*     * ***************************************************
     * **************** GETTERS AND SETTERS ******************
     * **************************************************** */

    public function getLectures() {
        return $this->lectures;
    }

    public function getModules() {
        return $this->modules;
    }

	public function getRootRefId() {

		if (!isset($this->root_ref_id))
		{
			$this->root_ref_id = self::_getRootRefId($this->getRefId());
		}
		return $this->root_ref_id;
	}

    /**
     * Set lectures
     * 
     * @param array 	var should be and array of lecture objects
     */
    public function setLectures($var) {
        $this->lectures = $var;
    }

    /**
     * Set modules
     * 
     * @param array 	var should be and array of module objects
     */
    public function setModules($var) {
        $this->modules = $var;
    }

    /*     * ***************************************************
     * ************* EVALUATION MANAGER METHODS **********
     * **************************************************** */

    /**
     * _getEntries return an array of entries filtered in case of filter is active, it's used by the show methods, and the export methods.
     * 
     * @param string 	$evaluation_manager if the ref_id of the evaluation manager objects which call the function.
     * @param string 	$type is the type of entry to be shown.
     * @param string 	$semester is the semester to filter entries.
     * @param string 	$number_of_assignments is the case of filter by number of assignments
     * @param string 	$keywords is the keywords to filter
     * @param string 	$keywords_inverse is the inverse filter
     * @return array    Returns an array with the entries needed.
     */
    public static function _getEntries($evaluation_manager, $type, $semester = null, $number_of_assignments = null, $keywords = null, $keywords_inverse = null) {
        //Create filter obj
        $filter = new ilObjEvaluationManagerFilter($evaluation_manager, $type, $semester, $number_of_assignments, $keywords, $keywords_inverse);
        return $filter->filter();
    }

    /**
     * Create entries
     * 
     * This function create the entries into the database if doesn't exist and updates it if exists.
     * 
     * @param arrat  $input is the array of string with the data to create the objects.
     * @return	boolean	if entries have been created is true.
     * @access	public
     * 
     */
    public function createEntries($input, $type) {
        global $lng;
        if (is_array($input)) {
            if ($input["type"]) {
                unset($input["type"]);
            }
            if (is_array($input)) {
                require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
                foreach ($input as $entry) {
                    if ($type == 'lec') {
                        $lecture = new ilObjLecture($entry);
                        $lecture_eval_id = ilObjLecture::_checkExistenceOfLecture($lecture->getEvalSemester(), $lecture->getEvalKey(), $lecture->getEMRefId());
                        if (!$lecture_eval_id) {
                            $lecture->insertLecture();
                        } else {
                            $lecture->updateLecture($lecture_eval_id, $lecture);
                        }
                        //Keywords creation
                        $array_of_keywords = $lecture->createKeywords($entry["keywords"]);
                        ilObjLectureKeyword::_deleteLectureKeywords($lecture->getEvalId());
                        if (is_array($array_of_keywords)) {
                            foreach ($array_of_keywords as $keyword) {
                                $keyword->insertLectureKeyword();
                            }
                            $lecture->setKeywords(ilObjLectureKeyword::_readLectureKeywords($lecture->getEvalId()));
                        }
                        //Assignment creation
                        if ($entry["ilias_obj"]) {
                            $assignment = $lecture->createAssignment($entry["ilias_obj"]);
                            if (!ilObjLectureAssignment::_checkExistenceOfLectureAssignment($assignment->getEvalId(), $assignment->getIliasObj())) {
                                $assignment->insertLectureAssignment();
                                //add mark to overview
                                ilEvaluationManagerOverview::_addMarkToObj($assignment->getIliasObj());
                            } else {
                                ilObjLectureAssignment::_updateLectureAssignment($assignment->getEvalId(), $assignment);
                            }
                            //comprobation of course or group
                            if (!ilObjEvaluationManagerExportEvaSys::_isCourseOrGroup($assignment->getIliasObj())) {
                                $error[] = $assignment->getTitle();
                            }
                            $lecture->setAssignments(ilObjLectureAssignment::_readLectureAssignments($lecture->getEvalId()));
                        }
                    } elseif ($type == 'mod') {
                        $module = new ilObjModule($entry);
                        $module_eval_id = ilObjModule::_checkExistenceOfModule($module->getEvalSemester(), $module->getEvalKey(), $module->getEMRefId());
                        if (!$module_eval_id) {
                            $module->insertModule();
                        } else {
                            $module->updateModule($module_eval_id, $module);
                        }
                        //Keywords creation
                        $array_of_keywords = $module->createKeywords($entry["keywords"]);
                        ilObjModuleKeyword::_deleteModuleKeywords($module->getEvalId());
                        if (is_array($array_of_keywords)) {
                            foreach ($array_of_keywords as $keyword) {
                                $keyword->insertModuleKeyword();
                            }
                            $module->setKeywords(ilObjModuleKeyword::_readModuleKeywords($module->getEvalId()));
                        }
                        //Assignment creation
                        if ($entry["ilias_obj"]) {
                            $assignment = $module->createAssignment($entry["ilias_obj"], $entry["lecture_name"], $entry["lecturer_name"]);
                            if (!ilObjModuleAssignment::_checkExistenceOfModuleAssignment($assignment->getEvalId(), $assignment->getIliasObj())) {
                                $assignment->insertModuleAssignment();
                                //add mark to overview
                                ilEvaluationManagerOverview::_addMarkToObj($assignment->getIliasObj());
                            } else {
                                ilObjModuleAssignment::_updateModuleAssignment($assignment->getEvalId(), $assignment);
                            }
                            //comprobation of course or group
                            if (!ilObjEvaluationManagerExportEvaSys::_isCourseOrGroup($assignment->getIliasObj())) {
                                $error[] = $assignment->getTitle();
                            }
                            $module->setAssignments(ilObjModuleAssignment::_readModuleAssignments($module->getEvalId()));
                        }
                    } else {
                        return false;
                    }
                }
                return $error;
            }
        }
    }

    public function createAssignments($input, $type) {
        if (is_array($input)) {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
            foreach ($input as $entry) {
                if ($type == 'lec') {
                    if ($entry["new_assignment_ref_id"]) {
                        $lecture = ilObjLecture::_readLecture($entry["eval_id"]);
                        $assignment = $lecture->createAssignment($entry["new_assignment_ref_id"]);
                        if (!ilObjLectureAssignment::_checkExistenceOfLectureAssignment($assignment->getEvalId(), $assignment->getIliasObj())) {
                            $assignment->insertLectureAssignment();
                            ilEvaluationManagerOverview::_addMarkToObj($entry["new_assignment_ref_id"]);
                        } else {
                            ilObjLectureAssignment::_updateLectureAssignment($assignment->getEvalId(), $assignment);
                        }
                    }
                } elseif ($type == 'mod') {
                    if ($entry["new_assignment_ref_id"]) {
                        $module = ilObjModule::_readModule($entry["eval_id"]);
                        $assignment = $module->createAssignment($entry["new_assignment_ref_id"], $entry["lecture_name"], $entry["lecturer_name"]);
                        if (!ilObjModuleAssignment::_checkExistenceOfModuleAssignment($assignment->getEvalId(), $assignment->getIliasObj())) {
                            $assignment->insertModuleAssignment();
                            ilEvaluationManagerOverview::_addMarkToObj($entry["new_assignment_ref_id"]);
                        } else {
                            ilObjModuleAssignment::_updateModuleAssignment($assignment->getEvalId(), $assignment);
                        }
                    }
                } else {
                    return false;
                }
            }
        }
    }

    /*     * ***************************************************
     * ******* DATABASE METHODS FOR DIFFERENT PURPOSES*******
     * **************************************************** */

    /**
     * _getDifferentSemesters returns an array with the different semester of a evaluation manager object.
     * 
     * @param string 	$ref_id if the ref_id of the evaluation manager objects which call the function.
     * @param string 	$type is the type of entry to be shown.
     * @return array    Returns an array with the semesters.
     */
    public static function _getDifferentSemesters($ref_id, $type) {
        global $ilDB, $lng;


        $semester = array();
        $query = "SELECT DISTINCT eval_semester FROM rep_robj_xema_eval WHERE em_ref_id = " . $ref_id
                . " AND eval_type = " . $ilDB->quote($type, 'text');
        $result = $ilDB->query($query);
        $semester[""] = $lng->txt("rep_robj_xema_all_semesters");
        while ($data = $ilDB->fetchAssoc($result)) {
            $semester[$data["eval_semester"]] = $data["eval_semester"];
        }
        return $semester;
    }

    /**
     * _deleteEntriesBySemester Deletes the entries of a semester and their assignments.
     * 
     * @param string 	$semester if the semester to delete the assignments
     * @param string 	$ref_id if the ref_id of the evaluation manager objects which call the function.
     * @param string 	$type is the type of entry to be shown.
     */
    public static function _deleteEntriesBySemester($semester, $ref_id, $type) {
        global $ilDB;

        //#1 Select all the eval_id to delete all the assignments with these eval_ids
        $select_query = "SELECT eval_id FROM "
                . " WHERE em_ref_id = " . $ilDB->quote($ref_id, 'integer')
                . " AND eval_type = " . $ilDB->quote($type, 'text');
        if ($semester) {
            $select_query .= " AND eval_semester = " . $ilDB->quote($semester, 'text');
        }
        $select_result = $ilDB->query($select_query);
        while ($data = $ilDB->fetchAssoc($select_result)) {
            $delete_assignments_query = "DELETE FROM rep_robj_xema_assign WHERE eval_id = " . $ilDB->quote($data["eval_id"], 'integer');
            $ilDB->query($delete_assignments_query);
        }
        //#2 Delete the assignments
        $delete_query = "DELETE FROM rep_robj_xema_eval"
                . " WHERE em_ref_id = " . $ilDB->quote($ref_id, 'integer')
                . " AND eval_type = " . $ilDB->quote($type, 'text');
        if ($semester) {
            $delete_query .= " AND eval_semester = " . $ilDB->quote($semester, 'text');
        }
        $ilDB->query($delete_query);
    }

    /**
     * 
     * @global object $ilDB
     * @param integer $eval_id if the eval_id of the entry to check type.
     * @return string|boolean type or false if not exists
     */
    public static function _getTypeByEvalId($eval_id) {
        global $ilDB;
        $query = "SELECT eval_type from rep_robj_xema_eval WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        $select_result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($select_result);
        if ($data) {
            return $data["eval_type"];
        } else {
            return false;
        }
    }

    public static function _getRootRefId($evaluation_manager_ref_id) {
        global $ilDB;

        $query = "SELECT root_ref_id from rep_robj_xema_settings WHERE ref_id = " . $ilDB->quote($evaluation_manager_ref_id, 'integer');
        $select_result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($select_result);
        if ($data) {
            return $data["root_ref_id"];
        } else {
            return false;
        }
    }

    public static function _manageRootForOverview($evaluation_manager_ref_id, $root_ref_id) {

        global $ilDB;

		$query = "DELETE FROM rep_robj_xema_settings"
			. " WHERE ref_id = " . $ilDB->quote($evaluation_manager_ref_id, 'integer');
		$ilDB->query($query);

		$ilDB->insert("rep_robj_xema_settings", array(
                "ref_id" => array("integer", $ilDB->quote($evaluation_manager_ref_id, 'integer')),
                "root_ref_id" => array("integer", $ilDB->quote($root_ref_id, 'integer'))));
    }

	/**
	 * check a course or group ref_id
	 * @param integer 	$a_ref_id
	 * @return string	error message or empty
	 */
	public function checkCourseOrGroupRefId($a_ref_id)
	{
		global $lng, $tree;

		if (empty($a_ref_id))
		{
			return $lng->txt('rep_robj_xema_error_no_course_or_group_selected');
		}
		elseif(!in_array(ilObject::_lookupType($a_ref_id, true), array('crs','grp')))
		{
			return sprintf($lng->txt('rep_robj_xema_error_is_no_course_or_group'),$a_ref_id);
		}
		elseif ($this->getRootRefId() and !$tree->isGrandChild($this->getRootRefId(), $a_ref_id))
		{
			return sprintf($lng->txt('rep_robj_xema_error_course_or_group_not_in_root'), $a_ref_id);
		}
		else
		{
			return "";
		}
	}
}

?>