<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilObjEvaluationManager.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilObjEvaluationManagerGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilObjEvaluationManagerEvaSysGUI.php');
require_once('./Services/Link/classes/class.ilLink.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilSessionValuesEM.php');

/**
 * Table class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 *
 */
class ilEvaluationManagerTableGUI extends ilTable2GUI {

    public $row_data = array();

    function __construct($a_parent_obj, $a_parent_cmd) {
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /* Init */

    public function init($a_parent_obj, $a_obj_id, $a_type, $a_filter_semester = "", $a_filter_course = "", $a_filter_keyword = "", $a_filter_keyword_inverse = "") {

        global $ilCtrl, $lng;

        $this->session = new ilSessionValuesEM(get_class($a_parent_obj));

        if ($a_type == "Lectures") {
        	$this->setTitle($lng->txt("rep_robj_xema_lectures"));
        	$this->addCommandButton('addLecture', $lng->txt('rep_robj_xema_add_lecture'));
            $this->addCommandButton('csvImportLec', $lng->txt('rep_robj_xema_csv_import_lectures'));
            $this->addCommandButton('csvExportLec', $lng->txt('rep_robj_xema_csv_export_lectures'));
        } elseif ($a_type == "Modules") {
        	$this->setTitle($lng->txt("rep_robj_xema_modules"));
        	$this->addCommandButton('addModule', $lng->txt('rep_robj_xema_add_module'));
            $this->addCommandButton('csvImportMod', $lng->txt('rep_robj_xema_csv_import_lectures'));
            $this->addCommandButton('csvExportMod', $lng->txt('rep_robj_xema_csv_export_modules'));
        } elseif ($a_type == "evaSysLec") {
        	$this->setTitle($lng->txt("rep_robj_xema_export_lectures_to_evasys"));
        	$this->addMultiCommand('exportLecturesToEvasys', $lng->txt('export'));
         } elseif ($a_type == "evaSysMod") {
       		$this->setTitle($lng->txt("rep_robj_xema_export_modules_to_evasys"));
            $this->addMultiCommand('exportModulesToEvasys', $lng->txt('export'));
         }

        $this->addColumn('', 'eval_id', 1);
        $this->addColumn($lng->txt("rep_robj_xema_identifier"), "", "5%");
        $this->addColumn($lng->txt("rep_robj_xema_title"), "", "20%");
        $this->addColumn($lng->txt("rep_robj_xema_responsible"), "", "20%");
        $this->addColumn($lng->txt("rep_robj_xema_questionnaire"), "", "10%");
        $this->addColumn($lng->txt("rep_robj_xema_course"), "", "20%");
        $this->addColumn($lng->txt("rep_robj_xema_keywords"), "", "10%");
        $this->addColumn($lng->txt("rep_robj_xema_action_1"), "", "10%");
        $this->addColumn($lng->txt("rep_robj_xema_action_2"), "", "5%");

        $this->addHiddenInput('semester_filter', $_REQUEST['semester_filter']);
        $this->addHiddenInput('course_filter', $_REQUEST['course_filter']);
        $this->addHiddenInput('keyword_filter', $_REQUEST['keyword_filter']);
        $this->addHiddenInput('keyword_inverse', $_REQUEST['keyword_inverse']);

        $this->setNoEntriesText($lng->txt("rep_robj_xema_no_events_found"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox("eval_id[]");

        $this->setRowTemplate("tpl.xema_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");

        if ($a_type == "Lectures") {
            $this->addMultiCommand("deleteEntriesLec", $lng->txt("delete"));
            $this->getLecturesResults($a_obj_id, $a_filter_semester, $a_filter_course, $a_filter_keyword, $a_filter_keyword_inverse);
        } elseif ($a_type == "Modules") {
            $this->addMultiCommand("deleteEntriesMod", $lng->txt("delete"));
            $this->getModulesResults($a_obj_id, $a_filter_semester, $a_filter_course, $a_filter_keyword, $a_filter_keyword_inverse);
        } elseif ($a_type == "evaSysLec") {
            $this->getLecturesResults($a_obj_id, $a_filter_semester, $a_filter_course, $a_filter_keyword, $a_filter_keyword_inverse);
        } elseif ($a_type == "evaSysMod") {
            $this->getModulesResults($a_obj_id, $a_filter_semester, $a_filter_course, $a_filter_keyword, $a_filter_keyword_inverse);
        }
        return $this;
    }

    function initAssignments($a_parent_obj, $a_type, $a_eval_id) {

        global $ilCtrl, $lng;

        $this->addColumn('', 'ass_id', 1);
        $this->addColumn($lng->txt("rep_robj_xema_title"), "ass_title", "20%");
        $this->addColumn($lng->txt("rep_robj_xema_root"), "ass_root", "20%");
        $this->addColumn($lng->txt("rep_robj_xema_teacher"), "ass_teacher", "20%");

        $this->setTitle($lng->txt("rep_robj_xema_assignments"));
        $this->setNoEntriesText($lng->txt("rep_robj_xema_no_events_found"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setSelectAllCheckbox("ass_id[]");

        $this->setRowTemplate("tpl.xema_assignment_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");

        $this->getAssignments($a_eval_id, $a_type);
    }

    function initOverviewTable($a_parent_obj) {
        
    }

    /**
     * Get data and put it into an array
     */
    function getLecturesResults($a_obj_id, $a_filter_semester = "", $a_filter_course = "", $a_filter_keyword = "", $a_filter_keyword_inverse = "") {

        global $lng, $ilCtrl;

        $EM_Object = new ilObjEvaluationManager();

        $a_filter_semester = $this->session->getSessionValue('filter', 'semester_filter');
        $a_filter_course = $this->session->getSessionValue('filter', 'course_filter');
        $a_filter_keyword = $this->session->getSessionValue('filter', 'keyword_filter');
        $a_filter_keyword_inverse = $this->session->getSessionValue('filter', 'keyword_inverse_filter');

        $ref_id = $EM_Object->getObjRefId($a_obj_id);
        $lectures = $EM_Object->getData($ref_id, "Lecture", $a_filter_semester, $a_filter_course, $a_filter_keyword, $a_filter_keyword_inverse, true);

        for ($i = 0; $i < sizeof($lectures); $i++) {
            $assignments = ilObjLecture::_getAssignmentsToLecture($lectures[$i]["eval_id"], "o.ref_id, d.title");

            if ($assignments) {
                if (sizeof($assignments) == 1) {
                    $lectures[$i]["assigned"] = ilLink::_getStaticLink($assignments[0]["ref_id"]);
                    $lectures[$i]["assigned_title"] = $assignments[0]["title"];
                } else {
                    for ($j = 0; $j < sizeof($assignments); $j++) {
                        $lectures[$i]["assigned"][$j] = ilLink::_getStaticLink($assignments[$j]["ref_id"]);
                        $lectures[$i]["assigned_title"][$j] = $assignments[$j]["title"];
                    }
                }
            } else {
                $lectures[$i]["assigned"] = $lng->txt("rep_robj_xema_no_courses_or_groups_found");
            }
            if (is_array($lectures[$i]["keywords"])) {
                $lectures[$i]["keywords"] = implode(", ", $lectures[$i]["keywords"]);
            }
        }

        $this->setDefaultOrderField("eval_id");
        $this->setDefaultOrderDirection("asc");
        $this->setData($lectures);
    }

    /**
     * Get data and put it into an array
     */
    function getModulesResults($a_obj_id, $a_filter_semester = "", $a_filter_course = "", $a_filter_keyword = "", $a_filter_keyword_inverse = "") {

        global $lng, $ilCtrl;

        $EM_Object = new ilObjEvaluationManager();

        $a_filter_semester = $this->session->getSessionValue('filter', 'semester_filter');
        $a_filter_course = $this->session->getSessionValue('filter', 'course_filter');
        $a_filter_keyword = $this->session->getSessionValue('filter', 'keyword_filter');
        $a_filter_keyword_inverse = $this->session->getSessionValue('filter', 'keyword_inverse_filter');

        $ref_id = $EM_Object->getObjRefId($a_obj_id);
        $modules = $EM_Object->getData($ref_id, "Module", $a_filter_semester, $a_filter_course, $a_filter_keyword, $a_filter_keyword_inverse, true);

        for ($i = 0; $i < sizeof($modules); $i++) {
            $assignments = ilObjModule::_getAssignmentsToModule($modules[$i]["eval_id"], "o.ref_id, d.title");
            if ($assignments) {
                if (sizeof($assignments) == 1) {
                    $modules[$i]["assigned"] = ilLink::_getStaticLink($assignments[0]["ref_id"]);
                    $modules[$i]["assigned_title"] = $assignments[0]["title"];
                } else {
                    for ($j = 0; $j < sizeof($assignments); $j++) {
                        $modules[$i]["assigned"][$j] = ilLink::_getStaticLink($assignments[$j]["ref_id"]);
                        $modules[$i]["assigned_title"][$j] = $assignments[$j]["title"];
                    }
                }
            } else {
                $modules[$i]["assigned"] = $lng->txt("rep_robj_xema_no_courses_or_groups_found");
            }
            if (is_array($modules[$i]["keywords"])) {
                $modules[$i]["keywords"] = implode(", ", $modules[$i]["keywords"]);
            }
        }

        $this->setDefaultOrderField("eval_id");
        $this->setDefaultOrderDirection("asc");
        $this->setData($modules);
    }

    function getAssignments($a_type, $eval_id) {

        $EM_Object = new ilObjEvaluationManager();
        if ($a_type == "Lectures") {
            $EM_Object->getLectureAssignments($eval_id);
        } elseif ($a_type == "Modules") {
            $EM_Object->getModuleAssignments($eval_id);
        }
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set) {

        global $ilCtrl;

        $ilCtrl->setParameter($this->getParentObject(), "edit", $a_set["eval_id"]);
        if ($ilCtrl->getCmd() == "showLectures") {
            $ilCtrl->setParameter($this->getParentObject(), "type_entry", "Lecture");
        } elseif ($ilCtrl->getCmd() == "showModules") {
            $ilCtrl->setParameter($this->getParentObject(), "type_entry", "Module");
        }

        // fred: deactivated for production use
        // $ilCtrl->setParameter($this->getParentObject(), "type_entry", "Module");

        $this->tpl->setVariable("CHECKBOX_EVAL_ID", $a_set["eval_id"]);
        $this->tpl->setVariable("VAL_IDENTIFIER", $a_set["eval_key"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["eval_name"]);
        $this->tpl->setVariable("TXT_RESPONSIBLE", $a_set["doc_firstname"] . " " . $a_set["doc_lastname"]);
        $this->tpl->setVariable("TXT_QUESTIONNAIRE", $a_set["eval_questionnaire"]);
        if (!is_array($a_set["assigned"])) {
            $this->tpl->setCurrentBlock('course_or_group');
            $this->tpl->setVariable('TXT_COURSE', $a_set["assigned"]);
            $this->tpl->setVariable('TXT_COURSE_TITLE', $a_set["assigned_title"]);
            $this->tpl->ParseCurrentBlock();
        } else {
            for ($i = 0; $i < sizeof($a_set["assigned"]); $i++) {
                $this->tpl->setCurrentBlock('course_or_group');
                $this->tpl->setVariable('TXT_COURSE', $a_set["assigned"][$i]);
                $this->tpl->setVariable('TXT_COURSE_TITLE', $a_set["assigned_title"][$i]);
                $this->tpl->ParseCurrentBlock();
            }
        }
        $this->tpl->setVariable("TXT_KEYWORDS", $a_set["keywords"]);
        $this->tpl->setVariable("TXT_ACTION1", $ilCtrl->getLinkTarget($this->getParentObject(), "getEntryForm"));
        // fred: deactivated for production use
        // $this->tpl->setVariable("TXT_ACTION2", $ilCtrl->getLinkTarget($this->getParentObject(), "seeAssignments"));
    }

}

?>
