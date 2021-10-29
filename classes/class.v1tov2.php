<?php

class v1tov2 {

    function __construct() {
        
    }

    function callScripts() {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilObjEvaluationManager.php');
        $evaluation_manager = new ilObjEvaluationManager;
        $input_lecture = $this->getLectureData();
        $input_module = $this->getModuleData();
        $evaluation_manager->createEntries($input_lecture, 'lec');
        $evaluation_manager->createEntries($input_module, 'mod');

		ilUtil::sendSuccess('Data migrated');
    }

    function getLectureData() {
        $final_lectures = array();
        $lectures = $this->getLectures();
        if (is_array($lectures)) {
            foreach ($lectures as $lecture) {
                $eval_id = $lecture["eval_id"];
                if ($lecture["eval_semester"] != "" AND $lecture["eval_semester"] != NULL AND $lecture["eval_key"] != "" AND $lecture["eval_semester"] != NULL) {
                    $lecture_assignments = $this->getLectureAssignments($eval_id);
                    if (is_array($lecture_assignments)) {
                        foreach ($lecture_assignments as $assignment) {
                            if ($assignment["ilias_obj"] != 0 AND $assignment["ilias_obj"] != NULL) {
                                $lecture["ilias_obj"] = $assignment["ilias_obj"];
                                $lecture["keywords"] = $this->getLectureKeywords($eval_id);
                                $final_lectures[] = $lecture;
                            }
                        }
                    } else {
                        $lecture["keywords"] = $this->getLectureKeywords($eval_id);
                        $final_lectures[] = $lecture;
                    }
                }
            }
        }
        $final_lectures["type"] = "lec";
        return $final_lectures;
    }

    function getModuleData() {
        $final_modules = array();
        $modules = $this->getModules();
        if (is_array($modules)) {
            foreach ($modules as $module) {
                $eval_id = $module["eval_id"];
                if ($module["eval_semester"] != "" AND $module["eval_semester"] != NULL AND $module["eval_key"] != "" AND $module["eval_semester"] != NULL) {
                    $module_assignments = $this->getModuleAssignments($eval_id);
                    if (is_array($module_assignments)) {
                        foreach ($module_assignments as $assignment) {
                            if ($assignment["ilias_obj"] != 0 AND $assignment["ilias_obj"] != NULL) {
                                $module["ilias_obj"] = $assignment["ilias_obj"];
                                $module["lecture_name"] = $assignment["lecture_name"];
                                $module["lecturer_name"] = $assignment["lecturer_name"];
                                $module["keywords"] = $this->getModuleKeywords($eval_id);
                                $final_modules[] = $module;
                            }
                        }
                    } else {
                        $module["keywords"] = $this->getModuleKeywords($eval_id);
                        $final_modules[] = $module;
                    }
                }
            }
        }
        $final_modules["type"] = "mod";
        return $final_modules;
    }

    function getLectures() {
        $lectures = array();
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_lectures";
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $row["eval_semester"] = strtolower($row["eval_semester"]);
            $lectures[] = $row;
        }
        return $lectures;
    }

    function getModules() {
        $modules = array();
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_modules";
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $row["eval_semester"] = strtolower($row["eval_semester"]);
            $modules[] = $row;
        }
        return $modules;
    }

    function getLectureAssignments($eval_id) {
        $lecture_assignments = array();
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_as_lec WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $lecture_assignments[] = $row;
        }
        if (empty($lecture_assignments)) {
            return false;
        }
        return $lecture_assignments;
    }

    function getModuleAssignments($eval_id) {
        $module_assignments = array();
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_as_mod WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $module_assignments[] = $row;
        }
        if (empty($module_assignments)) {
            return false;
        }
        return $module_assignments;
    }

    function getLectureKeywords($eval_id) {
        $lecture_keywords = array();
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_lec_key WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $lecture_keywords[] = $row["keyword"];
        }
        if (empty($lecture_keywords)) {
            return null;
        }
        return $lecture_keywords;
    }

    function getModuleKeywords($eval_id) {
        $module_keywords = array();
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_mod_key WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');
        $result = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($result)) {
            $module_keywords[] = $row["keyword"];
        }
        if (empty($module_keywords)) {
            return null;
        }
        return $module_keywords;
    }

}

?>
