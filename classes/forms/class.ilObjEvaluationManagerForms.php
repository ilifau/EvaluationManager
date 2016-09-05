<?php

/**
 * fim
 * Institut für Lern-Innovation
 * Friedrich-Alexander-Universität
 * Erlangen-Nürnberg
 * Germany
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Form factory class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjEvaluationManagerForms {

    private $type_of_form;
    private $type_of_entry;
    private $session_data;
    private $form_ref_id;

    /**
     * Constructor
     * @param string $type_of_form
     * @param string $type_of_entry
     * @param session $sesion_data
     * @param integer $form_ref_id
     */
    public function __construct($type_of_form, $type_of_entry, $sesion_data, $form_ref_id) {
        $this->setFormType($type_of_form);
        $this->setEntryType($type_of_entry);
        $this->setSessionData($sesion_data);
        $this->setFormRefId($form_ref_id);
    }

    /*     * ***************************************************
     * **************** GETTERS AND SETTERS ******************
     * **************************************************** */

    public function getFormType() {
        return $this->type_of_form;
    }

    public function setFormType($var) {
        $this->type_of_form = $var;
    }

    public function getEntryType() {
        return $this->type_of_entry;
    }

    public function setEntryType($var) {
        $this->type_of_entry = $var;
    }

    public function getSessionData() {
        return $this->session_data;
    }

    public function setSessionData($var) {
        $this->session_data = $var;
    }

    public function getFormRefId() {
        return $this->form_ref_id;
    }

    public function setFormRefId($var) {
        $this->form_ref_id = $var;
    }

    /*     * ***************************************************
     * **************** INIT FORM METHODS ******************
     * **************************************************** */

    /**
     * Factory for creation of forms
     * @param class $parent_gui
     * @param array $data
     * @param array $extra_data
     * @return \ilPropertyFormGUI
     */
    public function init($parent_gui, $data = "", $extra_data = "") {
        switch ($this->getFormType()) {
            case "import":
                return $this->initImportForm($parent_gui);
                break;
            case "filter":
                return $this->initFilterForm($parent_gui);
                break;
            case "add_entry":
                return $this->initAddEntryForm($parent_gui);
                break;
            case "add_assignment":
                return $this->initAddAssignmentForm($parent_gui, $data, $extra_data);
                break;
            case "edit_assignment":
                return $this->initEditAssignmentForm($parent_gui, $data);
                break;
            case "edit":
                return $this->initEditEntryForm($parent_gui, $data);
                break;
            case "delete":
                return $this->initDeleteEntriesForm($parent_gui, $data);
                break;
            case "delete_assignments":
                return $this->initDeleteAssignmentsForm($parent_gui, $data);
                break;
            case "evasys_toolbar":
                return $this->initExportToEvaSysToolbar($parent_gui);
                break;
            case "evasys_filter":
                return $this->initExportToEvaSysFilterForm($parent_gui);
                break;
            case "settings":
                return $this->initSettingsForm($parent_gui);
                break;
            case "add_mark":
                return $this->initAddMarkForm($parent_gui, $data);
                break;
        }
    }

    /**
     * Form for import CSV files to the system
     * @global $lng
     * @param class $parent_gui
     * @return \ilPropertyFormGUI
     */
    public function initImportForm($parent_gui) {
        global $lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "csvImport"));
        $form->setTitle($lng->txt("import"));

        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($lng->txt("import_file"), "importfile");
        $fi->setSuffixes(array("csv"));
        $fi->setRequired(true);
        $form->addItem($fi);

        //clean semester import
        $clean_semester_import = new ilCheckboxInputGUI($lng->txt('rep_robj_xema_clean_semester_import'), 'clean_semester_import');
        if ($this->getSessionData()->getSessionValue('import', 'clean_semester_import')) {
            $clean_semester_import->setChecked(true);
        } else {
            $clean_semester_import->setChecked(false);
        }

        //semester filter
        $semester = new ilSelectInputGUI($lng->txt('rep_robj_xema_clean_semester'), 'clean_semester');
        $options_sem = ilObjEvaluationManager::_getDifferentSemesters($this->getFormRefId(), $this->getEntryType());
        $semester->setOptions($options_sem);
        $semester->setValue($this->getSessionData()->getSessionValue('import', 'clean_semester'));
        $clean_semester_import->addSubItem($semester);
        $form->addItem($clean_semester_import);

        //import and cancel buttons
        $form->addCommandButton("csvImport", $lng->txt("import"));
        $form->addCommandButton("cancel", $lng->txt("cancel"));

        //Type of entry is a hidden field
        $type_of_entry = new ilHiddenInputGUI('type_of_entry');
        $type_of_entry->setValue($this->getEntryType());
        $form->addItem($type_of_entry);

        return $form;
    }

    /**
     * Filter of entries
     * @global $lng
     * @param class $parent_gui
     * @return \ilPropertyFormGUI
     */
    public function initFilterForm($parent_gui) {
        global $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        if ($this->getEntryType() == 'lec') {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
        } elseif ($this->getEntryType() == 'mod') {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
        } else {
            return null;
        }
        $form->setTitle($lng->txt('rep_robj_xema_filter'));

        //semester filter
        $semester = new ilSelectInputGUI($lng->txt('rep_robj_xema_semester_filter'), 'semester_filter');
        $options_sem = ilObjEvaluationManager::_getDifferentSemesters($this->getFormRefId(), $this->getEntryType());
        $semester->setOptions($options_sem);
        $semester->setValue($this->getSessionData()->getSessionValue('filter', 'semester_filter'));
        $form->addItem($semester);

        //course filter
        $course = new ilSelectInputGUI($lng->txt('rep_robj_xema_course_filter'), 'course_filter');
        $options_cour = array(
            "" => $lng->txt('rep_robj_xema_course_option_all'),
            0 => $lng->txt('rep_robj_xema_course_option_no_courses'),
            1 => $lng->txt('rep_robj_xema_course_option_one_course'),
            2 => $lng->txt('rep_robj_xema_course_option_more_courses')
        );
        $course->setOptions($options_cour);
        $course->setValue($this->getSessionData()->getSessionValue('filter', 'course_filter'));
        $form->addItem($course);

        //Keywords
        $keyword = new ilTextInputGUI($lng->txt('rep_robj_xema_keyword_filter'), 'keyword_filter');
        $keyword->setValue($this->getSessionData()->getSessionValue('filter', 'keyword_filter'));
        $keyword->setInfo($lng->txt('rep_robj_xema_keywords_info'));
        $form->addItem($keyword);

        //Keyword inverse
        $inverse = new ilCheckboxInputGUI($lng->txt('rep_robj_xema_keyword_inverse_filter'), 'keyword_inverse');
        if ($this->getSessionData()->getSessionValue('filter', 'keyword_inverse')) {
            $inverse->setChecked(true);
        } else {
            $inverse->setChecked(false);
        }
        $inverse->setInfo($lng->txt('rep_robj_xema_keyword_inverse_filter_info'));
        $form->addItem($inverse);

        if ($this->getEntryType() == 'lec') {
            $form->addCommandButton("applyFilterLec", $lng->txt('rep_robj_xema_apply_filter'));
            $form->addCommandButton("resetFilterLec", $lng->txt('rep_robj_xema_reset_filter'));
        } elseif ($this->getEntryType() == 'mod') {
            $form->addCommandButton("applyFilterMod", $lng->txt('rep_robj_xema_apply_filter'));
            $form->addCommandButton("resetFilterMod", $lng->txt('rep_robj_xema_reset_filter'));
        } else {
            
        }

        return $form;
    }

    /**
     * Form for adding a new entry to the system
     * @global $lng
     * @param class $parent_gui
     * @return \ilPropertyFormGUI
     */
    public function initAddEntryForm($parent_gui) {
        global $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        if ($this->getEntryType() == "lec") {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
            $form->setTitle($lng->txt('rep_robj_xema_add_lecture'));
        } elseif ($this->getEntryType() == "mod") {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
            $form->setTitle($lng->txt('rep_robj_xema_add_module'));
        }

        $type = new ilHiddenInputGUI('type');
        $type->setValue($this->getEntryType());
        $form->addItem($type);

        $semester = new ilTextInputGUI($lng->txt('rep_robj_xema_semester'), 'eval_semester');
        $semester->setValue($lng->txt('rep_robj_xema_semester_demo'));
        $semester->setRequired(true);
        $form->addItem($semester);

        //doc_function
        $doc_function = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_function'), 'doc_function');
        $doc_function->setValue($lng->txt('rep_robj_xema_doc_function_demo'));
        $form->addItem($doc_function);

        //doc_salutation
        $doc_salutation = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_salutation'), 'doc_salutation');
        $doc_salutation->setValue($lng->txt('rep_robj_xema_doc_salutation_demo'));
        $form->addItem($doc_salutation);

        //doc_title
        $doc_title = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_title'), 'doc_title');
        $doc_title->setValue($lng->txt('rep_robj_xema_doc_title_demo'));
        $form->addItem($doc_title);

        //doc_firstname
        $doc_firstname = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_firstname'), 'doc_firstname');
        $form->addItem($doc_firstname);

        //doc_lastname
        $doc_lastname = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_lastname'), 'doc_lastname');
        $form->addItem($doc_lastname);

        //doc_email
        $doc_email = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_email'), 'doc_email');
        $form->addItem($doc_email);

        //eval_name
        $eval_name = new ilTextInputGUI($this->getEntryType() == 'lec' ? $lng->txt('rep_robj_xema_lec_name') : $lng->txt('rep_robj_xema_mod_name'), 'eval_name');
        $form->addItem($eval_name);

        //eval_key
        $eval_key = new ilTextInputGUI($this->getEntryType() == 'lec' ? $lng->txt('rep_robj_xema_lec_key') : $lng->txt('rep_robj_xema_mod_key'), 'eval_key');
        $eval_key->setRequired(true);
        $eval_key->setInfo($lng->txt('rep_robj_xema_eval_key_info'));
        $form->addItem($eval_key);

        //eval_questionnaire
        $eval_questionnaire = new ilTextInputGUI($lng->txt('rep_robj_xema_eval_questionnaire'), 'eval_questionnaire');
        $form->addItem($eval_questionnaire);

        //eval_remarks
        $eval_remarks = new ilTextInputGUI($lng->txt('rep_robj_xema_eval_remarks'), 'eval_remarks');
        $form->addItem($eval_remarks);

        $keywords = new ilTextInputGUI($lng->txt('rep_robj_xema_keywords'), 'keywords');
        $keywords->setInfo($lng->txt('rep_robj_xema_keywords_info'));
        $form->addItem($keywords);

        if ($this->getEntryType() == "lec") {
            $form->addCommandButton("createLecture", $lng->txt('rep_robj_xema_add_lecture'));
            $form->addCommandButton("cancelLec", $lng->txt("cancel"));
        } elseif ($this->getEntryType() == "mod") {
            $form->addCommandButton("createModule", $lng->txt('rep_robj_xema_add_module'));
            $form->addCommandButton("cancelMod", $lng->txt("cancel"));
        }
        return $form;
    }

    /**
     * Form for adding a new assignment to an entry
     * @global $lng
     * @param class $parent_gui
     * @return \ilPropertyFormGUI
     */
    public function initAddAssignmentForm($parent_gui, $entry_eval_id, $assignment_ref_id = "") {
        global $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");

        $form = new ilPropertyFormGUI();
		$form->setTitle($lng->txt('rep_robj_xema_add_assignment'));

        if ($this->getEntryType() == "lec") {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
        } elseif ($this->getEntryType() == "mod") {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showModules"));

        }

        if ($this->getEntryType() == "lec") {
            $entry = ilObjLecture::_readLecture($entry_eval_id);
        } elseif ($this->getEntryType() == "mod") {
            $entry = ilObjModule::_readModule($entry_eval_id);
        } else {
            return null;
        }

        //eval_id
        $eval_id = new ilNonEditableValueGUI($this->getEntryType() == 'Lecture' ? $lng->txt('rep_robj_xema_lec_id') : $lng->txt('rep_robj_xema_mod_id'), 'eval_id');
        $eval_id->setValue($entry->getEvalId());
        $form->addItem($eval_id);

        //eval_key
        $eval_key = new ilNonEditableValueGUI($this->getEntryType() == 'Lecture' ? $lng->txt('rep_robj_xema_lec_key') : $lng->txt('rep_robj_xema_mod_key'), 'eval_key');
        $eval_key->setValue($entry->getEvalKey());
        $form->addItem($eval_key);

        //semester
        $semester = new ilNonEditableValueGUI($lng->txt('rep_robj_xema_semester'), 'eval_semester');
        $semester->setValue($entry->getEvalSemester());
        $form->addItem($semester);

        //ref_id
        $new_assignment_ref_id = new ilNumberInputGUI($lng->txt('rep_robj_xema_ref_id_new_assignment'), 'new_assignment_ref_id');
		$new_assignment_ref_id->setSize(10);
		$new_assignment_ref_id->allowDecimals(false);
        $new_assignment_ref_id->setValue($assignment_ref_id);
		$new_assignment_ref_id->setRequired(true);

		// repository selection
 		$rs = new ilRepositorySelectorInputGUI($lng->txt("rep_robj_xema_assignment_selection"), "assignment_ref_id");
		$rs->setClickableTypes(array('crs','grp'));
		$rs->setInfo($lng->txt("rep_robj_xema_assignment_selection_info"));
		$new_assignment_ref_id->addSubItem($rs);

		$form->addItem($new_assignment_ref_id);

		if ($this->getEntryType() == "mod") {
            //lecture name
            $lecture_name = new ilTextInputGUI($lng->txt('rep_robj_xema_lecture_name'), 'lecture_name');
            $form->addItem($lecture_name);
            //lecturer name
            $lecturer_name = new ilTextInputGUI($lng->txt('rep_robj_xema_lecturer_name'), 'lecturer_name');
            $form->addItem($lecturer_name);
        }

        if ($this->getEntryType() == "lec") {
            $form->addCommandButton("addAssignmentToLecture", $lng->txt('rep_robj_xema_save_assignment'));
            $form->addCommandButton("editAssignments", $lng->txt("cancel"));
        } elseif ($this->getEntryType() == "mod") {
            $form->addCommandButton("addAssignmentToModule", $lng->txt('rep_robj_xema_save_assignment'));
            $form->addCommandButton("editAssignments", $lng->txt("cancel"));
        }

        return $form;
    }

    public function initEditAssignmentForm($parent_gui, $data) {
        global $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "overview"));
        $form->setTitle($lng->txt('rep_robj_xema_edit_assignment'));

        if ($this->getEntryType() == "lec") {
            $entry = ilObjLecture::_readLecture($data["eval_id"]);
        } elseif ($this->getEntryType() == "mod") {
            $entry = ilObjModule::_readModule($data["eval_id"]);
        } else {
            return null;
        }

        $ref_id_ilias_obj = $data["ilias_obj"];


        //eval_id
        $eval_id = new ilNonEditableValueGUI($this->getEntryType() == 'Lecture' ? $lng->txt('rep_robj_xema_lec_id') : $lng->txt('rep_robj_xema_mod_id'), 'eval_id');
        $eval_id->setValue($entry->getEvalId());
        $form->addItem($eval_id);

        //eval_key
        $eval_key = new ilNonEditableValueGUI($this->getEntryType() == 'Lecture' ? $lng->txt('rep_robj_xema_lec_key') : $lng->txt('rep_robj_xema_mod_key'), 'eval_key');
        $eval_key->setValue($entry->getEvalKey());
        $form->addItem($eval_key);

        //semester
        $semester = new ilNonEditableValueGUI($lng->txt('rep_robj_xema_semester'), 'eval_semester');
        $semester->setValue($entry->getEvalSemester());
        $form->addItem($semester);

        //ref_id
        $assignment_ref_id = new ilNonEditableValueGUI($lng->txt('rep_robj_xema_ref_id_edited_assignment'), 'new_assignment_ref_id');
        $assignment_ref_id->setValue($ref_id_ilias_obj);
        $form->addItem($assignment_ref_id);

        if ($this->getEntryType() == "mod") {
            $module_assignment = ilObjModuleAssignment::_readModuleAssignment($data["eval_id"], $ref_id_ilias_obj);
            //lecture name
            $lecture_name = new ilTextInputGUI($lng->txt('rep_robj_xema_lecture_name'), 'lecture_name');
            $lecture_name->setValue($module_assignment->getLectureName());
            $form->addItem($lecture_name);

            //lecturer name
            $lecturer_name = new ilTextInputGUI($lng->txt('rep_robj_xema_lecturer_name'), 'lecturer_name');
            $lecturer_name->setValue($module_assignment->getLecturerName());
            $form->addItem($lecturer_name);
        }

        if ($this->getEntryType() == "lec") {
            $form->addCommandButton("addAssignmentToLecture", $lng->txt('rep_robj_xema_save_assignment'));
            $form->addCommandButton("editAssignments", $lng->txt("cancel"));
        } elseif ($this->getEntryType() == "mod") {
            $form->addCommandButton("addAssignmentToModule", $lng->txt('rep_robj_xema_save_assignment'));
            $form->addCommandButton("editAssignments", $lng->txt("cancel"));
        }

        return $form;
    }

    public function initEditEntryForm($parent_gui, $data) {
        global $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        if ($this->getEntryType() == "lec") {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
            $form->setTitle($lng->txt('rep_robj_xema_edit_lecture'));
        } elseif ($this->getEntryType() == "mod") {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
            $form->setTitle($lng->txt('rep_robj_xema_edit_module'));
        }

        $type = new ilHiddenInputGUI('type');
        $type->setValue($this->getEntryType());
        $form->addItem($type);

        //eval_key
        $eval_key = new ilNonEditableValueGUI($this->getEntryType() == 'Lecture' ? $lng->txt('rep_robj_xema_lec_key') : $lng->txt('rep_robj_xema_mod_key'), 'eval_key');
        $eval_key->setValue($data->getEvalKey());
        $form->addItem($eval_key);

        //semester
        $semester = new ilNonEditableValueGUI($lng->txt('rep_robj_xema_semester'), 'eval_semester');
        $semester->setValue($data->getEvalSemester());
        $form->addItem($semester);

        //doc_function
        $doc_function = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_function'), 'doc_function');
        $doc_function->setValue($data->getDocFunction());
        $form->addItem($doc_function);

        //doc_salutation
        $doc_salutation = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_salutation'), 'doc_salutation');
        $doc_salutation->setValue($data->getDocSalutation());
        $form->addItem($doc_salutation);

        //doc_title
        $doc_title = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_title'), 'doc_title');
        $doc_title->setValue($data->getDocTitle());
        $form->addItem($doc_title);

        //doc_firstname
        $doc_firstname = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_firstname'), 'doc_firstname');
        $doc_firstname->setValue($data->getDocFirstname());
        $form->addItem($doc_firstname);

        //doc_lastname
        $doc_lastname = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_lastname'), 'doc_lastname');
        $doc_lastname->setValue($data->getDocLastname());
        $form->addItem($doc_lastname);

        //doc_email
        $doc_email = new ilTextInputGUI($lng->txt('rep_robj_xema_doc_email'), 'doc_email');
        $doc_email->setValue($data->getDocEmail());
        $form->addItem($doc_email);

        //eval_name
        $eval_name = new ilTextInputGUI($this->getEntryType() == 'lec' ? $lng->txt('rep_robj_xema_lec_name') : $lng->txt('rep_robj_xema_mod_name'), 'eval_name');
        $eval_name->setValue($data->getEvalName());
        $form->addItem($eval_name);

        //eval_questionnaire
        $eval_questionnaire = new ilTextInputGUI($lng->txt('rep_robj_xema_eval_questionnaire'), 'eval_questionnaire');
        $eval_questionnaire->setValue($data->getEvalQuestionnaire());
        $form->addItem($eval_questionnaire);

        //eval_remarks
        $eval_remarks = new ilTextInputGUI($lng->txt('rep_robj_xema_eval_remarks'), 'eval_remarks');
        $eval_remarks->setValue($data->getEvalRemarks());
        $form->addItem($eval_remarks);

        //Keywords
        $keywords = new ilTextInputGUI($lng->txt('rep_robj_xema_keywords'), 'keywords');
        $keyword_string = "";
        if (is_array($data->getKeywords())) {
            foreach ($data->getKeywords() as $keyword) {
                $keyword_string.=$keyword->getKeyword() . ",";
            }
        }
        $keywords->setValue($keyword_string);
        $keywords->setInfo($lng->txt('rep_robj_xema_keywords_info'));
        $form->addItem($keywords);

        if ($this->getEntryType() == "lec") {
            $form->addCommandButton("editLecture", $lng->txt('rep_robj_xema_save_lecture'));
            $form->addCommandButton("cancelLec", $lng->txt("cancel"));
        } elseif ($this->getEntryType() == "mod") {
            $form->addCommandButton("editModule", $lng->txt('rep_robj_xema_save_module'));
            $form->addCommandButton("cancelMod", $lng->txt("cancel"));
        }
        return $form;
    }

    public function initDeleteEntriesForm($parent_gui, $data) {
        global $lng;
        if ($this->getEntryType() == "lec") {
            if (is_array($data) && !empty($data)) {
                include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
                $confirm = new ilConfirmationGUI();
                $confirm->setHeaderText($lng->txt('rep_robj_xema_confirm_delete_lectures'));
                foreach ($data as $eval_id) {
                    $lecture = ilObjLecture::_readLecture($eval_id);
                    $confirm->addItem('eval_id[]', $lecture->getEvalId(), $lecture->getEvalKey() . " " . $lecture->getEvalSemester());
                }
                $confirm->setCancel($lng->txt('cancel'), $parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
                $confirm->setConfirm($lng->txt('confirm'), 'deleteLectures');
                $confirm->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
                return $confirm;
            }
        } elseif ($this->getEntryType() == "mod") {
            if (is_array($data) && !empty($data)) {
                include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
                $confirm = new ilConfirmationGUI();
                $confirm->setHeaderText($lng->txt('rep_robj_xema_confirm_delete_modules'));
                foreach ($data as $eval_id) {
                    $module = ilObjModule::_readModule($eval_id);
                    $confirm->addItem('eval_id[]', $module->getEvalId(), $module->getEvalKey() . " " . $module->getEvalSemester());
                }
                $confirm->setCancel($lng->txt('cancel'), $parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
                $confirm->setConfirm($lng->txt('confirm'), 'deleteModules');
                $confirm->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
                return $confirm;
            }
        } elseif ($this->getEntryType() == "overview") {
            if (is_array($data) && !empty($data)) {
                include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
                $confirm = new ilConfirmationGUI();
                $confirm->setHeaderText($lng->txt('rep_robj_xema_confirm_delete_mark'));
                foreach ($data as $ref_id) {
                    $confirm->addItem('ref_id[]', $ref_id, $ref_id);
                }
                $confirm->setCancel($lng->txt('cancel'), $parent_gui->ctrl->getFormAction($parent_gui, "overview"));
                $confirm->setConfirm($lng->txt('confirm'), 'deleteMark');
                $confirm->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "overview"));
                return $confirm;
            }
        } else {
            return null;
        }
    }

    public function initDeleteAssignmentsForm($parent_gui, $data) {
        global $lng;
        if ($this->getEntryType() == "lec") {
            if (is_array($data["ilias_objs"]) && !empty($data["ilias_objs"])) {
                include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
                $confirm = new ilConfirmationGUI();
                $confirm->setHeaderText($lng->txt('rep_robj_xema_confirm_delete_assignments'));
                $eval_id = $data["entry_eval_id"];
                $confirm->addHiddenItem("eval_id", $eval_id, $eval_id);
                foreach ($data["ilias_objs"] as $ilias_obj) {
                    $lecture_assignment = ilObjLectureAssignment::_readLectureAssignment($eval_id, $ilias_obj);
                    $confirm->addItem('ref_id[]', $lecture_assignment->getIliasObj(), $lecture_assignment->getTitle());
                }
                $confirm->setCancel($lng->txt('cancel'), $parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
                $confirm->setConfirm($lng->txt('confirm'), 'deleteLectureAssignments');
                $confirm->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showLectures"));
                return $confirm;
            }
        } elseif ($this->getEntryType() == "mod") {
            if (is_array($data["ilias_objs"]) && !empty($data["ilias_objs"])) {
                include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
                $confirm = new ilConfirmationGUI();
                $confirm->setHeaderText($lng->txt('rep_robj_xema_confirm_delete_assignments'));
                $eval_id = $data["entry_eval_id"];
                $confirm->addHiddenItem("eval_id", $eval_id, $eval_id);
                foreach ($data["ilias_objs"] as $ilias_obj) {
                    $module_assignment = ilObjModuleAssignment::_readModuleAssignment($eval_id, $ilias_obj);
                    $confirm->addItem('ref_id[]', $module_assignment->getIliasObj(), $module_assignment->getTitle());
                }
                $confirm->setCancel($lng->txt('cancel'), $parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
                $confirm->setConfirm($lng->txt('confirm'), 'deleteModuleAssignments');
                $confirm->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "showModules"));
                return $confirm;
            } else {
                return null;
            }
        }
    }

    public function initExportToEvaSysToolbar($parent_gui) {
        global $lng;

        include_once "./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php";
        $toolbar = new ilToolbarGui();
        $toolbar->addButton($lng->txt('rep_robj_xema_export_lec'), $parent_gui->ctrl->getLinkTarget($parent_gui, "exportEvaSysLec"));
        $toolbar->addSeparator();
        $toolbar->addButton($lng->txt('rep_robj_xema_export_mod'), $parent_gui->ctrl->getLinkTarget($parent_gui, "exportEvaSysMod"));

        return $toolbar;
    }

    public function initExportToEvaSysFilterForm($parent_gui) {
        global $lng;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        if ($this->getEntryType() == 'lec') {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "applyFilterEvaSysLec"));
        } elseif ($this->getEntryType() == 'mod') {
            $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "applyFilterEvaSysMod"));
        } else {
            return null;
        }
        $form->setTitle($lng->txt('rep_robj_xema_filter'));

        //semester filter
        $semester = new ilSelectInputGUI($lng->txt('rep_robj_xema_semester_filter'), 'semester_filter');
        $options_sem = ilObjEvaluationManager::_getDifferentSemesters($this->getFormRefId(), $this->getEntryType());
        $semester->setOptions($options_sem);
        $semester->setValue($this->getSessionData()->getSessionValue('filter', 'semester_filter'));
        $form->addItem($semester);

        //course filter
        $course = new ilSelectInputGUI($lng->txt('rep_robj_xema_course_filter'), 'course_filter');
        $options_cour = array(
            "" => $lng->txt('rep_robj_xema_course_option_all'),
            0 => $lng->txt('rep_robj_xema_course_option_no_courses'),
            1 => $lng->txt('rep_robj_xema_course_option_one_course'),
            2 => $lng->txt('rep_robj_xema_course_option_more_courses')
        );
        $course->setOptions($options_cour);
        $course->setValue($this->getSessionData()->getSessionValue('filter', 'course_filter'));
        $form->addItem($course);

        //Keywords
        $keyword = new ilTextInputGUI($lng->txt('rep_robj_xema_keyword_filter'), 'keyword_filter');
        $keyword->setValue($this->getSessionData()->getSessionValue('filter', 'keyword_filter'));
        $keyword->setInfo($lng->txt('rep_robj_xema_keywords_info'));
        $form->addItem($keyword);

        //Keyword inverse
        $inverse = new ilCheckboxInputGUI($lng->txt('rep_robj_xema_keyword_inverse_filter'), 'keyword_inverse');
        if ($this->getSessionData()->getSessionValue('filter', 'keyword_inverse')) {
            $inverse->setChecked(true);
        } else {
            $inverse->setChecked(false);
        }
        $inverse->setInfo($lng->txt('rep_robj_xema_keyword_inverse_filter_info'));
        $form->addItem($inverse);

        $document_name = new ilTextInputGUI($lng->txt('rep_robj_xema_document_name'), 'document_name');
        $document_name->setValue($this->getSessionData()->getSessionValue('filter', 'document_name'));
        $document_name->setInfo($lng->txt('rep_robj_xema_document_name_info'));
        $form->addItem($document_name);

        if ($this->getEntryType() == 'lec') {
            $form->addCommandButton("applyFilterEvaSysLec", $lng->txt('rep_robj_xema_apply_filter'));
            $form->addCommandButton("resetFilterEvaSysLec", $lng->txt('rep_robj_xema_reset_filter'));
        } elseif ($this->getEntryType() == 'mod') {
            $form->addCommandButton("applyFilterEvaSysMod", $lng->txt('rep_robj_xema_apply_filter'));
            $form->addCommandButton("resetFilterEvaSysMod", $lng->txt('rep_robj_xema_reset_filter'));
        } else {
            
        }

        return $form;
    }

    public function initSettingsForm($parent_gui) {
        global $lng;
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "applySettings"));
        $form->setTitle($lng->txt('rep_robj_xema_settings'));

        //ref_id of the root
        $semester = new ilTextInputGUI($lng->txt('rep_robj_xema_root_ref_id'), 'root_ref_id');
        $semester->setValue(ilObjEvaluationManager::_getRootRefId($this->getFormRefId()));
        $form->addItem($semester);
        $form->addCommandButton("applySettings", $lng->txt('rep_robj_xema_apply_settings'));

        return $form;
    }

    public function initAddMarkForm($parent_gui, $data = "") {
        global $lng, $ilCtrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($parent_gui->ctrl->getFormAction($parent_gui, "addMark"));
        $form->setTitle($lng->txt('rep_robj_xema_add_mark'));

        $ilCtrl->setParameter($parent_gui, "edit", "mark");

        //ref_id
        $new_assignment_ref_id = new ilNumberInputGUI($lng->txt('rep_robj_xema_ref_id_new_assignment'), 'new_assignment_ref_id');
		$new_assignment_ref_id->allowDecimals(false);
		$new_assignment_ref_id->setSize(10);
		$new_assignment_ref_id->setValue($data);
		$new_assignment_ref_id->setRequired(true);

		$rs = new ilRepositorySelectorInputGUI($lng->txt("rep_robj_xema_assignment_selection"), "assignment_ref_id");
		$rs->setClickableTypes(array("grp", "crs"));
        $rs->setInfo($lng->txt("rep_robj_xema_assignment_selection_info"));
		$new_assignment_ref_id->addSubItem($rs);
		$form->addItem($new_assignment_ref_id);

        $form->addCommandButton("addMark", $lng->txt('rep_robj_xema_add_mark'));
        $form->addCommandButton("overview", $lng->txt("cancel"));

        return $form;
    }

}

?>