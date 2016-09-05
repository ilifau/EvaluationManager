<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Overview class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 *
 */
class ilEvaluationManagerOverview {

    public static function _getOverview($semester = null) {
        $info = ilEvaluationManagerOverview::_getKeyInfoToOverview($semester);
        return ilEvaluationManagerOverview::_getExtraInfoToOverview($info);
    }

    public static function _getKeyInfoToOverview($semester = null) {
        global $ilDB;

		$query = "SELECT m.ref_id, d.obj_id, d.type, d.title FROM eval_marked_objects m"
			. " INNER JOIN object_reference r ON m.ref_id = r.ref_id"
			. " INNER JOIN object_data d ON r.obj_id = d.obj_id";

		if (!empty($semester))
		{
			$query .= " WHERE ". $ilDB->like('import_id', 'text', $semester.'%');
		}

		$result = $ilDB->query($query);

		$info = array();
		while ($data = $ilDB->fetchAssoc($result)) {
			$info[] = $data;
		}
        return $info;
    }

    public static function _getExtraInfoToOverview($key_info) {
        global $ilDB, $lng, $ilias, $tree;

        $info_array = array();
        $counter = 0;

        include_once("./Services/Tree/classes/class.ilPathGUI.php");
        $path = new ilPathGUI();
        $path->enableTextOnly(false);

        foreach ($key_info as $row) {
            if ($row["type"] == "crs") {
                $end_obj_id = (integer) $row["ref_id"];
                $root_obj_id = (integer) ilObjEvaluationManager::_getRootRefId($_GET["ref_id"]);
                if ($tree->isGrandChild($root_obj_id, $end_obj_id)) {
                    $info_array[$counter]["ref_id"] = $row["ref_id"];
                    $info_array[$counter]["title"] = $row["title"];
                    $info_array[$counter]["path"] = $path->getPath(ROOT_FOLDER_ID, $row["ref_id"]);
                    $info_array[$counter]["link"] = ilLink::_getStaticLink($row["ref_id"], $row['type']);
                    $query = "SELECT contact_name, contact_email FROM crs_settings WHERE obj_id = " . $ilDB->quote($row["obj_id"], 'integer');
                    $result = $ilDB->query($query);
                    $data = $ilDB->fetchAssoc($result);
                    if ($data) {
                        $info_array[$counter]["contact_name"] = $data["contact_name"];
                        $info_array[$counter]["contact_email"] = $data["contact_email"];
                    }
                    $lectures = ilEvaluationManagerOverview::_getLecturesAssigned($row["ref_id"]);
                    $info_array[$counter]["evaluation"] = array();
                    foreach ($lectures as $lecture) {
                        $info_array[$counter]["evaluation"][] = $lng->txt("rep_robj_xema_lecture_short_label") . $lecture["eval_name"];
                    }
                    $modules = ilEvaluationManagerOverview::_getModulesAssigned($row["ref_id"]);
                    foreach ($modules as $module) {
                        $info_array[$counter]["evaluation"][] = $lng->txt("rep_robj_xema_module_short_label") . $module["eval_name"];
                    }
                }
            } elseif ($row["type"] == "grp") {
                $end_obj_id = (integer) $row["ref_id"];
                $root_obj_id = (integer) ilObjEvaluationManager::_getRootRefId($_GET["ref_id"]);
                if ($tree->isGrandChild($root_obj_id, $end_obj_id)) {
                    $info_array[$counter]["ref_id"] = $row["ref_id"];
                    $info_array[$counter]["title"] = $row["title"];
                    $info_array[$counter]["path"] = $path->getPath(ROOT_FOLDER_ID, $row["ref_id"]);
                    $info_array[$counter]["link"] = ilLink::_getStaticLink($row["ref_id"], $row['type']);
                    
                    $lectures = ilEvaluationManagerOverview::_getLecturesAssigned($row["ref_id"]);
                    $info_array[$counter]["evaluation"] = array();
                    foreach ($lectures as $lecture) {
                        $info_array[$counter]["evaluation"][] = $lng->txt("rep_robj_xema_lecture_short_label") . $lecture["eval_name"];
                    }
                    $modules = ilEvaluationManagerOverview::_getModulesAssigned($row["ref_id"]);
                    foreach ($modules as $module) {
                        $info_array[$counter]["evaluation"][] = $lng->txt("rep_robj_xema_module_short_label") . $module["eval_name"];
                    }
                }
            } else {
                
            }
            $counter++;
        }

        return $info_array;
    }

    public static function _getLecturesAssigned($ref_id) {
        global $ilDB;

        $lectures = array();
        $counter = 0;

        $query = "SELECT eval_id FROM rep_robj_xema_assign WHERE type = 'lec' AND  ilias_obj = " . $ilDB->quote($ref_id, 'integer');

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

    public static function _deleteAssigned($ref_id) {
        global $ilDB;
        $query = "DELETE FROM eval_marked_objects WHERE ref_id = " . $ilDB->quote($ref_id, 'integer');
        $ilDB->query($query);
    }

    public static function _addMarkToObj($ref_id) {
        global $ilDB;
        if (!ilEvaluationManagerOverview::_isMarked((integer) $ref_id)) {
            $ilDB->insert("eval_marked_objects ", array(
                "ref_id" => array("integer", $ilDB->quote($ref_id, 'integer')))
            );
        }
    }

    public static function _isMarked($ref_id) {
        global $ilDB;
        $query = "SELECT ref_id FROM eval_marked_objects WHERE ref_id = " . $ilDB->quote($ref_id, 'integer');
        $result = $ilDB->query($query);
        if ($ilDB->fetchAssoc($result)) {
            return true;
        } else {
            return false;
        }
    }

}

?>
