<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once('./Services/Link/classes/class.ilLink.php');
require_once('./Services/Utilities/classes/class.ilSessionValues.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilObjEvaluationManager.php');

/**
 * Table class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 *
 */
class ilEvaluationManagerTableShowEntriesGUI extends ilTable2GUI {

    private $type;
    private $ref_id;

    /**
     * contruct
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $type, $ref_id) {

		$this->setId('xema_tab_entries');

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTableType($type);
        $this->setTableRefId($ref_id);
    }

    /*     * ***************************************************
     * **************** GETTERS, AND SETTERS ******************
     * **************************************************** */

    public function getTableType() {
        return $this->type;
    }

    public function setTableType($var) {
        $this->type = $var;
    }

    public function getTableRefId() {
        return $this->ref_id;
    }

    public function setTableRefId($var) {
        $this->ref_id = $var;
    }

    public function init($parent_obj, $type_of_table) {
        if ($type_of_table == "show") {
            return $this->initShowEntries($parent_obj);
        } elseif ($type_of_table == "evasys") {
            return $this->initEvaSysEntries($parent_obj);
        } else {
            return null;
        }
    }

    public function initShowEntries($parent_obj) {

        global $ilCtrl, $lng;

        $this->session = new ilSessionValues(get_class($parent_obj));

        if ($this->getTableType() == "lec") {
            $this->setTitle($lng->txt("rep_robj_xema_lectures"));
            $this->addCommandButton('addLecture', $lng->txt('rep_robj_xema_add_lecture'));
            $this->addCommandButton('csvImportLec', $lng->txt('rep_robj_xema_csv_import_lectures'));
            $this->addCommandButton('csvExportLec', $lng->txt('rep_robj_xema_csv_export_lectures'));
        } elseif ($this->getTableType() == "mod") {
            $this->setTitle($lng->txt("rep_robj_xema_modules"));
            $this->addCommandButton('addModule', $lng->txt('rep_robj_xema_add_module'));
            $this->addCommandButton('csvImportMod', $lng->txt('rep_robj_xema_csv_import_modules'));
            $this->addCommandButton('csvExportMod', $lng->txt('rep_robj_xema_csv_export_modules'));
        } else {
            //ERROR TYPE NOT VALID
        }

        $this->addColumn('', 'eval_id', 1);
        $this->addColumn($lng->txt("rep_robj_xema_identifier"), "", "5%");
        $this->addColumn($lng->txt("name"), "", "20%");
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
        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->setSelectAllCheckbox("eval_id[]");

        $this->setRowTemplate("tpl.xema_row_1.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");

        if ($this->getTableType() == "lec") {
            $this->addMultiCommand("confirmDeleteLectures", $lng->txt("delete"));
        } elseif ($this->getTableType() == "mod") {
            $this->addMultiCommand("confirmDeleteModules", $lng->txt("delete"));
        }
        return $this;
    }

    public function initEvaSysEntries($parent_obj) {

        global $ilCtrl, $lng;

        $this->session = new ilSessionValues(get_class($parent_obj));

        if ($this->getTableType() == "lec") {
            $this->setTitle($lng->txt("rep_robj_xema_export_lectures_to_evasys"));
            $this->addMultiCommand('exportLecturesToEvasys', $lng->txt('export'));
        } elseif ($this->getTableType() == "mod") {
            $this->setTitle($lng->txt("rep_robj_xema_export_modules_to_evasys"));
            $this->addMultiCommand('exportModulesToEvasys', $lng->txt('export'));
        } else {
            //ERROR TYPE NOT VALID
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
        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->setSelectAllCheckbox("eval_id[]");

        $this->setRowTemplate("tpl.xema_row_1.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");
        return $this;
    }

    public function getContent() {

        if ($this->getTableType() == "evasys_lec") {
            $this->setTableType("lec");
        } elseif ($this->getTableType() == "evasys_mod") {
            $this->setTableType("mod");
        }
        //Get session parameters
        $semester = $this->session->getSessionValue('filter', 'semester_filter');
        $number_of_assignments = $this->session->getSessionValue('filter', 'course_filter');
        $keywords = $this->session->getSessionValue('filter', 'keyword_filter');
        $keywords_inverse = $this->session->getSessionValue('filter', 'keyword_inverse');

        $evaluation_manager = new ilObjEvaluationManager();
        $array_of_objects = $evaluation_manager->_getEntries($this->getTableRefId(), $this->getTableType(), $semester, $number_of_assignments, $keywords, $keywords_inverse);

        $this->setDefaultOrderField("eval_id");
        $this->setDefaultOrderDirection("asc");
        $this->setData($this->convertToArray($array_of_objects));
    }

    /**
     * Fill a single data row.
     */
    protected function fillRow($entry) {

        global $ilCtrl;

        $this->tpl->setVariable("CHECKBOX_EVAL_ID", $entry["eval_id"]);
        $this->tpl->setVariable("VAL_IDENTIFIER", $entry["eval_key"]);
        $this->tpl->setVariable("TXT_TITLE", $entry["eval_name"]);
        $this->tpl->setVariable("TXT_RESPONSIBLE", $entry["doc_firstname"] . " " . $entry["doc_lastname"]);
        $this->tpl->setVariable("TXT_QUESTIONNAIRE", $entry["eval_questionnaire"]);

        //Assignments
        $objects = [];
        foreach ($entry["assignments"] as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            if (!empty($obj_id) && !ilObject::_isInTrash($ref_id)) {
                $title = ilObject::_lookupTitle($obj_id);
                $objects[] = '<a href="'. ilLink::_getStaticLink($ref_id). '">'.$title .'</a>';
            }
        }
        $this->tpl->setVariable("OBJECTS", implode(', ', $objects));

        //Keywords
        $this->tpl->setVariable('KEYWORDS',  implode(', ', $entry["keywords"]));


        //eval id for edit purpose
        if ($ilCtrl->getCmd() != "showRepositorySelection" AND $ilCtrl->getCmd() != "selectRepositoryItem") {
            $ilCtrl->setParameter($this->getParentObject(), "edit", $entry["eval_id"]);
        }else{
            $ilCtrl->setParameter($this->getParentObject(), "edit", $_REQUEST["edit"]);
        }
        $this->tpl->setVariable('TXT_ACTION1', $ilCtrl->getLinkTarget($this->getParentObject(), "editEntry"));
        $this->tpl->setVariable('TXT_ACTION2', $ilCtrl->getLinkTarget($this->getParentObject(), "editAssignments"));
    }

    public function convertToArray($array_of_objects) {
        $array = array();
        if (is_array($array_of_objects)) {
            foreach ($array_of_objects as $key => $entry) {
                $data = [];
                $data["eval_id"] = $entry->getEvalId();
                $data["eval_semester"] = $entry->getEvalSemester();
                $data["doc_function"] = $entry->getDocFunction();
                $data["doc_salutation"] = $entry->getDocSalutation();
                $data["doc_title"] = $entry->getDocTitle();
                $data["doc_firstname"] = $entry->getDocFirstname();
                $data["doc_lastname"] = $entry->getDocLastname();
                $data["doc_email"] = $entry->getDocEmail();
                $data["eval_name"] = $entry->getEvalName();
                $data["eval_key"] = $entry->getEvalKey();
                $data["eval_type"] = $entry->getEvalType();
                $data["eval_questionnaire"] = $entry->getEvalQuestionnaire();
                $data["eval_remarks"] = $entry->getEvalRemarks();
                $data["em_ref_id"] = $entry->getEMRefId();
                $data["keywords"] = [];
                $keywords_object_array = $entry->getKeywords();
                if (is_array($keywords_object_array)) {
                    foreach ($keywords_object_array as $keyword) {
                        $data["keywords"][] = $keyword->getKeyword();
                    }
                }
                $data["assignments"] = [];
                $assignments_object_array = $entry->getAssignments();
                if (is_array($assignments_object_array)) {
                    foreach ($assignments_object_array as $assignment) {
                        $data["assignments"][] = $assignment->getIliasObj();
                    }
                }
                $array[] = $data;
            }
        }

        return $array;
    }

}

?>
