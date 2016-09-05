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
class ilEvaluationManagerTableOverviewGUI extends ilTable2GUI {

    private $type;
    private $ref_id;
	private $semester;

    /**
     * contruct
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $type, $ref_id, $a_semester = null) {

		$this->setId('xema_tab_over');

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTableType($type);
        $this->setTableRefId($ref_id);
		$this->semester = $a_semester;
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

    public function init($parent_obj) {
        global $ilCtrl, $lng;
        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("rep_robj_xema_ref_id"));
        $this->addColumn($lng->txt("rep_robj_xema_course_group"));
        $this->addColumn($lng->txt("contact"));
        $this->addColumn($lng->txt("rep_robj_xema_assigned_evaluations"));
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.xema_overview_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager");
        $this->addMultiCommand("confirmDeleteMark", $lng->txt("rep_robj_xema_remove_mark"));
        $this->addCommandButton('addMarkToCourseOrGroup', $lng->txt('rep_robj_xema_add_mark'));
        $this->addCommandButton('exportOverview', $lng->txt('rep_robj_xema_export_overview'));
    }
    
    public function getContent() {
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
        $this->setDefaultOrderField("ref_id");
        $this->setDefaultOrderDirection("asc");
        $this->setData(ilEvaluationManagerOverview::_getOverview($this->semester));
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
        $this->tpl->setVariable("TXT_CONTACT_NAME", $a_set["contact_name"]);
        $this->tpl->setVariable("TXT_CONTACT_EMAIL", $a_set["contact_email"]);

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
        $this->tpl->setVariable("TXT_ACTION1", $a_set["TXT_ACTION1"]);
        $this->tpl->setVariable("TXT_ACTION2", $a_set["TXT_ACTION2"]);
    }

}

?>