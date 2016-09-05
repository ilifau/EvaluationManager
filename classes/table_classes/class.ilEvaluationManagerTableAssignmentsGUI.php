<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Overview table
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilEvaluationManagerTableAssignmentsGUI extends ilTable2GUI {

    private $type;
    private $eval_id;

    /**
     * contruct
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $type, $eval_id) {

		$this->setId('xema_tab_ass');

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTableType($type);
        $this->setTableEvalId($eval_id);
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

    public function getTableEvalId() {
        return $this->eval_id;
    }

    public function setTableEvalId($var) {
        $this->eval_id = $var;
    }

    public function init($parent_obj, $entry_key, $entry_semester, $entry_name) {
        global $ilCtrl, $lng;
        if ($this->getTableType() == "lec") {
            $this->setTitle($entry_key . " " . $entry_semester . " " . $entry_name);
            $this->addCommandButton('addLectureAssignment', $lng->txt('rep_robj_xema_add_assignment'));
            $ilCtrl->setParameter($this->getParentObject(), "edit", $this->getTableEvalId());
            $this->addCommandButton('showLectures', $lng->txt('back'));
            $this->addColumn("", "", "1");
            $this->addColumn($lng->txt("rep_robj_xema_ref_id"));
            $this->addColumn($lng->txt("rep_robj_xema_course_group"));
            $this->addColumn($lng->txt("rep_robj_xema_assigned_evaluations"));
            $this->addColumn($lng->txt("rep_robj_xema_action_1"), "", "10%");

            $this->setEnableHeader(true);
            $this->setFormAction($ilCtrl->getFormAction($parent_obj));
            $this->setSelectAllCheckbox("ref_id[]");
            $this->setRowTemplate("tpl.xema_assignment_lecture_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");
            $this->addMultiCommand("confirmDeleteLectureAssignments", $lng->txt("rep_robj_xema_remove_assignment"));
        } elseif ($this->getTableType() == "mod") {
            $this->setTitle($entry_key . " " . $entry_semester . " " . $entry_name);
            $this->addCommandButton('addModuleAssignment', $lng->txt('rep_robj_xema_add_assignment'));
            $ilCtrl->setParameter($this->getParentObject(), "edit", $this->getTableEvalId());
            $this->addCommandButton('showModules', $lng->txt('back'));
            $this->addColumn("", "", "1");
            $this->addColumn($lng->txt("rep_robj_xema_ref_id"));
            $this->addColumn($lng->txt("rep_robj_xema_course_group"));
            $this->addColumn($lng->txt("rep_robj_xema_lecture_lecturer"));
            $this->addColumn($lng->txt("rep_robj_xema_assigned_evaluations"));
            $this->addColumn($lng->txt("rep_robj_xema_action_1"), "", "10%");

            $this->setEnableHeader(true);
            $this->setFormAction($ilCtrl->getFormAction($parent_obj));
            $this->setSelectAllCheckbox("ref_id[]");
            $this->setRowTemplate("tpl.xema_assignment_module_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");
            $this->addMultiCommand("confirmDeleteModuleAssignments", $lng->txt("rep_robj_xema_remove_assignment"));
        } else {
            return null;
        }
    }

    public function getContent($eval_id) {
        $this->setDefaultOrderField("ref_id");
        $this->setDefaultOrderDirection("asc");
        if ($this->getTableType() == "lec") {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLectureAssignment.php');
            $this->setData(ilObjLectureAssignment::_getAssignmentData($eval_id));
        } elseif ($this->getTableType() == "mod") {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModuleAssignment.php');
            $this->setData(ilObjModuleAssignment::_getAssignmentData($eval_id));
        } else {
            return null;
        }
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set) {
        global $ilCtrl;
        $this->tpl->setVariable("CHECKBOX_EVAL_ID", $a_set["ref_id"]);
        $this->tpl->setVariable("TXT_REF_ID", $a_set["ref_id"]);
        $this->tpl->setVariable("LINK_COURSE_GROUP", $a_set["link"]);
        $this->tpl->setVariable("TXT_COURSE_GROUP_NAME", $a_set["title"]);
        $this->tpl->setVariable("TXT_PATH", $a_set["path"]);
        if ($this->getTableType() == "mod") {
            $this->tpl->setVariable("TXT_LECTURE_NAME", $a_set["lecture_name"]);
            $this->tpl->setVariable("TXT_LECTURER_NAME", $a_set["lecturer_name"]);
        }
        if (!is_array($a_set["evaluation"])) {
            $this->tpl->setCurrentBlock('course_or_group');
            $this->tpl->setVariable('TXT_EVALUATION', $a_set["evaluation"]);
            $this->tpl->ParseCurrentBlock();
        } else {
            for ($i = 0; $i < sizeof($a_set["evaluation"]); $i++) {
                $this->tpl->setCurrentBlock('course_or_group');
                $this->tpl->setVariable('TXT_EVALUATION', $a_set["evaluation"][$i]);
                $this->tpl->ParseCurrentBlock();
            }
        }
        //eval id for edit purpose
        $ilCtrl->setParameter($this->getParentObject(), "edit_assignment", $a_set["ref_id"]);
        if ($this->getTableType() == "mod") {
            $this->tpl->setVariable("TXT_ACTION1", $ilCtrl->getLinkTarget($this->getParentObject(), "editAssignment"));
        }
    }

    public function convertToArray($array_of_objects) {
        $array = array();
        if (is_array($array_of_objects)) {
            foreach ($array_of_objects as $key => $entry) {
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
                $keywords_object_array = $entry->getKeywords();
                if (is_array($keywords_object_array)) {
                    foreach ($keywords_object_array as $keyword) {
                        $data["keywords"][] = $keyword->getKeyword();
                    }
                }
                $assignments_object_array = $entry->getAssignments();
                if (is_array($assignments_object_array)) {
                    foreach ($assignments_object_array as $key => $assignment) {
                        $data["assignments"][$key]["ilias_obj"] = ilLink::_getStaticLink($assignment->getIliasObj());
                        $data["assignments"][$key]["title"] = $assignment->getTitle();
                    }
                }
                $array[] = $data;
                $data = "";
            }
        }

        return $array;
    }

}

?>