<?php

/**
 * fim
 * Institut für Lern-Innovation
 * Friedrich-Alexander-Universität
 * Erlangen-Nürnberg
 * Germany
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once('./Services/Utilities/classes/class.ilSessionValues.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilObjEvaluationManager.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/table_classes/class.ilEvaluationManagerTableShowEntriesGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/table_classes/class.ilEvaluationManagerTableEvaSysExportGUI.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/import/class.ilObjEvaluationManagerImportCSV.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/export/class.ilObjEvaluationManagerExportCSV.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/forms/class.ilObjEvaluationManagerForms.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModule.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLecture.php');

/**
 * Application class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 *
 * @ilCtrl_isCalledBy ilObjEvaluationManagerGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjEvaluationManagerGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI,
 * @ilCtrl_Calls ilObjEvaluationManagerGUI: ilPropertyFormGUI, ilPageObjectGUI, ilRepositorySelectorInputGUI
 * @ilCtrl_Calls ilObjEvaluationManagerGUI: ilCommonActionDispatcherGUI
 */
class ilObjEvaluationManagerGUI extends ilObjectPluginGUI
{
    /**
     * @var ilCtrl
     */
    public $ctrl;

	/**
	 * Get type.
	 */
	public final function getType()
	{
		return "xema";
	}

	/**
	 * Initialisation
	 */
	protected function afterConstructor()
	{
		require_once('./Services/Utilities/classes/class.ilSessionValues.php');
		$this->session = new ilSessionValues(get_class($this));
	}

	/**
	 * Handles all commmands of this class, centralizes permission checks
	 */
	function performCommand($cmd)
	{
		switch ($cmd)
		{
			case "showContent":
				$this->checkPermission("read");
				$this->showLectures();
				break;

			case "showLectures":
			case "showModules":
			case "applyFilterLec":
			case "resetFilterLec":
			case "applyFilterMod":
			case "resetFilterMod":
			case "csvExportLec":
			case "csvExportMod":
			case "csvImport":
			case "csvImportLec":
			case "csvImportMod":
			case "addImportedCSV":
			case "seeAssignments":
			case "addLecture":
			case "addModule":
			case "cancelLec":
			case "cancelMod":
			case "createLecture":
			case "createModule":
			case "editLecture":
			case "editModule":
			case "confirmDeleteLectures":
			case "confirmDeleteModules":
			case "deleteLectures":
			case "deleteModules":
				$this->checkPermission("read");
				$this->$cmd();
				break;

			case "editEntry":
			case "editAssignments":
			case "addLectureAssignment":
			case "addModuleAssignment":
			case "addAssignmentToLecture":
			case "addAssignmentToModule":
			case "confirmDeleteLectureAssignments":
			case "confirmDeleteModuleAssignments":
			case "deleteLectureAssignments":
			case "deleteModuleAssignments":
			case "editAssignment":
			case "createOrUpdateEntry":
			case "applyFilterEvaSysLec":
			case "resetFilterEvaSysLec":
			case "applyFilterEvaSysMod":
			case "resetFilterEvaSysMod":
			case "applyFilterMarked":
			case "resetFilterMarked":
			case "overview":
			case "deleteMark":
			case "addMarkToCourseOrGroup":
			case "addMark":
			case "confirmDeleteMark":
			case "exportOverview":
				$this->checkPermission("read");
				$this->$cmd();
				break;
			case "evasysExport":
			case "exportEvaSysLec":
			case "exportEvaSysMod":
			case "exportLecturesToEvasys":
			case "exportModulesToEvasys":
			case "downloadEvasysFile":
			case "deleteEvasysFile":
			case "showRepositorySelection":
			case "selectRepositoryItem":
			case "reset":
			$this->checkPermission("write");
				$this->$cmd();
				break;
			case "settings":
			case "applySettings":
				$this->checkPermission("edit_permissions");
				$this->$cmd();
				break;
		}
	}

	/**
	 * After object has been created -> jump to this command
	 */
	function getAfterCreationCmd()
	{
		return "showLectures";
	}

	/**
	 * Get standard command
	 */
	function getStandardCmd()
	{
		return $this->showLectures();
	}

//
// DISPLAY TABS
//

	/**
	 * Set tabs
	 */
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;

		// tab for the "show Lectures" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("lec", $this->txt("lectures"), $ilCtrl->getLinkTarget($this, "showLectures"));
		}
		// tab for the "show Modules" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("mod", $this->txt("modules"), $ilCtrl->getLinkTarget($this, "showModules"));
		}
		// tab for the "overview" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("overview", $this->txt("overview_marked_courses_and_groups"), $ilCtrl->getLinkTarget($this, "overview"));
		}
		// tab for the "exportEvasys" command
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("evasys", $this->txt("exportEvaSys"), $ilCtrl->getLinkTarget($this, "evasysExport"));
		}
		// tab for the edit root for overview marked courses or groups command
		if ($ilAccess->checkAccess("edit_permissions", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("settings", $this->txt("settings"), $ilCtrl->getLinkTarget($this, "settings"));
		}
		// standard info screen tab
		$this->addInfoTab();

		// standard epermission tab
		$this->addPermissionTab();
	}

	/*	 * ***************************************************
	 * **************** METHODS FOR COMMANDS ******************
	 * **************************************************** */

	/*	 * ***************************************************
	 * ************* SHOW AND FILTER ENTRIES ***************
	 * **************************************************** */

	/**
	 * Show Lectures
	 */
	public function showLectures()
	{
		global $ilTabs;

		$ilTabs->activateTab("lec");

		$table_gui = new ilEvaluationManagerTableShowEntriesGUI($this, "showLectures", "lec", $this->object->getRefId());
		$table_gui->init($this, "show");
		$table_gui->getContent();

		$form_obj = new ilObjEvaluationManagerForms("filter", "lec", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);

		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML() . "</br>" . $table_gui->getHTML());
	}

	/**
	 * Show Modules
	 */
	public function showModules()
	{
		global $ilTabs;

		$ilTabs->activateTab("mod");

		$table_gui = new ilEvaluationManagerTableShowEntriesGUI($this, "showModules", "mod", $this->object->getRefId());
		$table_gui->init($this, "show");
		$table_gui->getContent();

		$form_obj = new ilObjEvaluationManagerForms("filter", "mod", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);

		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML() . "</br>" . $table_gui->getHTML());
	}

	/*
	 * Apply filter lectures
	 */

	public function applyFilterLec()
	{
		$this->session->saveRequestValue('filter', 'semester_filter');
		$this->session->saveRequestValue('filter', 'course_filter');
		$this->session->setSessionValue('filter', 'keyword_filter', preg_replace('/\s+/', '', $this->session->getRequestValue('keyword_filter')));
		$this->session->saveRequestValue('filter', 'keyword_inverse');
		$this->showLectures();
	}

	/*
	 * Apply filter modules
	 */

	public function applyFilterMod()
	{
		$this->session->saveRequestValue('filter', 'semester_filter');
		$this->session->saveRequestValue('filter', 'course_filter');
		$this->session->setSessionValue('filter', 'keyword_filter', preg_replace('/\s+/', '', $this->session->getRequestValue('keyword_filter')));
		$this->session->saveRequestValue('filter', 'keyword_inverse');
		$this->showModules();
	}

	/*
	 * Reset filter in lectures
	 */

	public function resetFilterLec()
	{
		$this->session->deleteSessionValues('filter');
		$this->showLectures();
	}

	/*
	 * Reset filter in modules
	 */

	public function resetFilterMod()
	{
		$this->session->deleteSessionValues('filter');
		$this->showModules();
	}

	function cancelLec()
	{
		$this->ctrl->redirect($this, 'showLectures');
	}

	function cancelMod()
	{
		$this->ctrl->redirect($this, 'showModules');
	}

	/*	 * ***************************************************
	 * ************* EXPORT CSV FUNCTIONALITIES ***************
	 * **************************************************** */

	/*
	 * Export lectures to CSV
	 */

	public function csvExportLec()
	{
		//Get session parameters
		$semester = $this->session->getSessionValue('filter', 'semester_filter');
		$number_of_assignments = $this->session->getSessionValue('filter', 'course_filter');
		$keywords = $this->session->getSessionValue('filter', 'keyword_filter');
		$keywords_inverse = $this->session->getSessionValue('filter', 'keyword_inverse');

		//Get entries
		$array_of_entries = ilObjEvaluationManager::_getEntries($this->object->getRefId(), "lec", $semester, $number_of_assignments, $keywords, $keywords_inverse);
		ilObjEvaluationManagerExportCSV::_writeCSVofEntries($array_of_entries, "lec");
	}

	/*
	 * Export modules to CSV
	 */

	public function csvExportMod()
	{
		//Get session parameters
		$semester = $this->session->getSessionValue('filter', 'semester_filter');
		$number_of_assignments = $this->session->getSessionValue('filter', 'course_filter');
		$keywords = $this->session->getSessionValue('filter', 'keyword_filter');
		$keywords_inverse = $this->session->getSessionValue('filter', 'keyword_inverse');

		//Get entries
		$array_of_entries = ilObjEvaluationManager::_getEntries($this->object->getRefId(), "mod", $semester, $number_of_assignments, $keywords, $keywords_inverse);

		ilObjEvaluationManagerExportCSV::_writeCSVofEntries($array_of_entries, "mod");
	}

	/*	 * ***************************************************
	 * ************* IMPORT CSV FUNCTIONALITIES ***************
	 * **************************************************** */

	/* -----------------
	 * CALL TO FORMS *
	  ----------------
	 * Get CSV form for lectures
	 */

	public function csvImportLec()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("lec");

		$form_obj = new ilObjEvaluationManagerForms("import", "lec", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);

		$tpl->setContent($form->getHTML());
	}

	/*
	 * Get CSV form for modules
	 */

	public function csvImportMod()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("mod");

		$form_obj = new ilObjEvaluationManagerForms("import", "mod", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);

		$tpl->setContent($form->getHTML());
	}

	/* -----------------
	 * MANAGE CSV IMPORT *
	  ----------------
	 * Import from CSV
	 */

	public function csvImport()
	{
		// save the import settings in the session
		$this->session->saveRequestValue('import', 'clean_semester_import');
		$this->session->saveRequestValue('import', 'clean_semester');

		//Get imported file
		$file = $_FILES["importfile"]["tmp_name"];

		$type = $_POST["type_of_entry"];

		//Read and write
		//; is the delimiter for the columns and , the delimiter between keywords
		$import = new ilObjEvaluationManagerImportCSV($file, $type, ";", ",");
		$input = $import->readCSV($file, $type);
		//Throw error if was problems reading the CSV file
		if ($input == "ERROR_WRONG_TYPE")
		{
			ilUtil::sendFailure($this->txt('imported_file_has_wrong_type'));
		}
		elseif ($input == "ERROR_SEMESTER_OR_KEY_NOT_FOUND")
		{
			ilUtil::sendFailure($this->txt('imported_file_has_entries_without_key_or_semester'));
		}
		else
		{

			//If delete by semester
			if ($this->session->getSessionValue('import', 'clean_semester_import'))
			{
				$semester = $this->session->getSessionValue('import', 'clean_semester');
				ilObjEvaluationManager::_deleteEntriesBySemester($semester, $this->object->getRefId(), $type);
			}

			$evaluation_manager = new ilObjEvaluationManager();

			//Manage of importing
			$is_error = $evaluation_manager->createEntries($input, $type);
			if (is_array($is_error))
			{
				//Error message: "The following assignments are not courses or groups: "
				ilUtil::sendFailure(sprintf($this->txt("warning_no_courses_or_groups"), implode(', ', $is_error)));
			}
		}
		if ($type == "lec")
		{
			$this->showLectures();
		}
		elseif ($type == "mod")
		{
			$this->showModules();
		}
	}

	/*	 * ***************************************************
	 * ****************** ADD ENTRIES ***********************
	 * **************************************************** */

	/* -----------------
	 * CALL TO FORMS *
	  ---------------- */

	public function addLecture()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("lec");

		$form_obj = new ilObjEvaluationManagerForms("add_entry", "lec", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);

		$tpl->setContent($form->getHTML());
	}

	public function addModule()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("mod");

		$form_obj = new ilObjEvaluationManagerForms("add_entry", "mod", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);

		$tpl->setContent($form->getHTML());
	}

	/* ------------------------------
	 * MANAGE ADD ENTRIES *
	  ------------------------------- */

	public function createLecture()
	{
		$input = $this->getDataFromForm();
		if ($input == "ERROR_NO_SEMESTER_OR_KEY")
		{
			ilUtil::sendFailure($this->txt("error_no_semester_or_key_given"));
		}
		$lecture = array();
		$lecture[] = $input;
		$this->object->createEntries($lecture, "lec");
		$this->showLectures();
	}

	public function createModule()
	{
		$input = $this->getDataFromForm();
		if ($input == "ERROR_NO_SEMESTER_OR_KEY")
		{
			ilUtil::sendFailure($this->txt("error_no_semester_or_key_given"));
		}
		$module = array();
		$module[] = $input;
		$this->object->createEntries($module, "mod");
		$this->showModules();
	}

	/*	 * ***************************************************
	 * ****************** EDIT ENTRIES ***********************
	 * **************************************************** */

	/* ------------------------------
	 * CALL TO FORMS *
	  ------------------------------- */

	public function editEntry()
	{
		global $tpl, $ilTabs;
		$entry_eval_id = $_GET["edit"];
		$entry_type = ilObjEvaluationManager::_getTypeByEvalId($entry_eval_id);
		if ($entry_type == "lec")
		{
			$ilTabs->activateTab("lec");
			$lecture = ilObjLecture::_readLecture($entry_eval_id);
			$lecture_keywords = ilObjLectureKeyword::_readLectureKeywords($entry_eval_id);
			$lecture->setKeywords($lecture_keywords);

			$form_obj = new ilObjEvaluationManagerForms("edit", "lec", $this->session, $this->object->getRefId());
			$form = $form_obj->init($this, $lecture);
		}
		elseif ($entry_type == "mod")
		{
			$ilTabs->activateTab("mod");
			$module = ilObjModule::_readModule($entry_eval_id);
			$module_keywords = ilObjModuleKeyword::_readModuleKeywords($entry_eval_id);
			$module->setKeywords($module_keywords);

			$form_obj = new ilObjEvaluationManagerForms("edit", "mod", $this->session, $this->object->getRefId());
			$form = $form_obj->init($this, $module);
		}
		$tpl->setContent($form->getHTML());
	}

	/* ------------------------------
	 * MANAGE EDIT ENTRIES *
	  ------------------------------- */

	public function editLecture()
	{
		$input = $this->getDataFromForm();
		if ($input == "ERROR_NO_SEMESTER_OR_KEY")
		{
			ilUtil::sendFailure($this->txt("error_no_semester_or_key_given"));
		}
		$lecture = array();
		$lecture[] = $input;
		$this->object->createEntries($lecture, "lec");
		$this->showLectures();
	}

	public function editModule()
	{
		$input = $this->getDataFromForm();
		if ($input == "ERROR_NO_SEMESTER_OR_KEY")
		{
			ilUtil::sendFailure($this->txt("error_no_semester_or_key_given"));
		}
		$module = array();
		$module[] = $input;
		$this->object->createEntries($module, "mod");
		$this->showModules();
	}

	/*	 * ***************************************************
	 * ****************** DELETE ENTRIES ***********************
	 * **************************************************** */

	/* ------------------------------
	 * CALL TO FORMS *
	  ------------------------------- */

	public function confirmDeleteLectures()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("lec");

		$eval_ids = $_POST['eval_id'];

		if (!is_array($eval_ids) or !count($eval_ids))
		{
			ilUtil::sendInfo($this->txt('select_one'));
			return $this->showLectures();
		}

		$form_obj = new ilObjEvaluationManagerForms("delete", "lec", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this, $eval_ids);

		$tpl->setContent($form->getHTML());
	}

	public function confirmDeleteModules()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("mod");

		$eval_ids = $_POST['eval_id'];

		if (!is_array($eval_ids) or !count($eval_ids))
		{
			ilUtil::sendInfo($this->txt('select_one'));
			return $this->showModules();
		}

		$form_obj = new ilObjEvaluationManagerForms("delete", "mod", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this, $eval_ids);

		$tpl->setContent($form->getHTML());
	}

	/* ------------------------------
	 * MANAGE DELETE ENTRIES *
	  ------------------------------- */

	public function deleteLectures()
	{
		foreach ($_POST["eval_id"] as $event_id)
		{
			ilObjLectureAssignment::_deleteLectureAssignment($event_id);
			ilObjLectureKeyword::_deleteLectureKeywords($event_id);
			ilObjLecture::_deleteLecture($event_id);
		}
		ilUtil::sendSuccess($this->txt('events_deleted'));
		$this->showLectures();
	}

	public function deleteModules()
	{
		foreach ($_POST["eval_id"] as $event_id)
		{
			ilObjModuleAssignment::_deleteModuleAssignment($event_id);
			ilObjModuleKeyword::_deleteModuleKeywords($event_id);
			ilObjModule::_deleteModule($event_id);
		}
		ilUtil::sendSuccess($this->txt('events_deleted'));
		$this->showModules();
	}

	/*	 * ***************************************************
	 * ****************** ADD ASSIGNMENTS ******************
	 * **************************************************** */

	/* ------------------------------
	 * CALL TO FORMS *
	  ------------------------------- */

	public function editAssignments()
	{
		global $tpl, $ilTabs;
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/table_classes/class.ilEvaluationManagerTableAssignmentsGUI.php');
		$entry_eval_id = $_GET["edit"];
		if ($_REQUEST["eval_id"])
		{
			$entry_eval_id = $_REQUEST["eval_id"];
		}
		$entry_type = ilObjEvaluationManager::_getTypeByEvalId($entry_eval_id);
		if ($entry_type == "lec")
		{
			$ilTabs->activateTab("lec");
			$lecture = ilObjLecture::_readLecture($entry_eval_id);
			$table_gui = new ilEvaluationManagerTableAssignmentsGUI($this, "showLectures", "lec", $entry_eval_id);
			$table_gui->init($this, $lecture->getEvalKey(), $lecture->getEvalSemester(), $lecture->getEvalName());
			$table_gui->getContent($entry_eval_id);
		}
		elseif ($entry_type == "mod")
		{
			$ilTabs->activateTab("mod");
			$module = ilObjModule::_readModule($entry_eval_id);
			$table_gui = new ilEvaluationManagerTableAssignmentsGUI($this, "showModules", "mod", $entry_eval_id);
			$table_gui->init($this, $module->getEvalKey(), $module->getEvalSemester(), $module->getEvalName());
			$table_gui->getContent($entry_eval_id);
		}
		$tpl->setContent($table_gui->getHTML());
	}

	public function addLectureAssignment($assignment_ref_id = "")
	{
		global $tpl, $ilTabs, $ilCtrl;
		$ilTabs->activateTab("lec");
		$entry_eval_id = $_GET["edit"];
		$ilCtrl->setParameter($this, "edit", $entry_eval_id);
		$form_obj = new ilObjEvaluationManagerForms("add_assignment", "lec", $this->session, $entry_eval_id);
		$form = $form_obj->init($this, $entry_eval_id, $assignment_ref_id);
		$tpl->setContent($form->getHTML());
	}

	public function addModuleAssignment($assignment_ref_id = "")
	{
		global $tpl, $ilTabs, $ilCtrl;
		$ilTabs->activateTab("mod");
		$entry_eval_id = $_GET["edit"];
		$ilCtrl->setParameter($this, "edit", $entry_eval_id);
		$form_obj = new ilObjEvaluationManagerForms("add_assignment", "mod", $this->session, $entry_eval_id);
		$form = $form_obj->init($this, $entry_eval_id, $assignment_ref_id);
		$tpl->setContent($form->getHTML());
	}

	/* ------------------------------
	 * MANAGE ADD ASSIGNMENTS *
	  ------------------------------- */

	public function addAssignmentToLecture()
	{

		$input = $this->getDataFromForm("assignment");
		if ($message = $this->object->checkCourseOrGroupRefId($input["new_assignment_ref_id"]))
		{
			ilUtil::sendFailure($message);
			$this->editAssignments();
			return;
		}
		$lecture_assignment = array();
		$lecture_assignment[] = $input;
		$this->object->createAssignments($lecture_assignment, "lec");

		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
		ilEvaluationManagerOverview::_addMarkToObj($input["new_assignment_ref_id"]);

		ilUtil::sendSuccess($this->txt("assignment_created"));
		$this->editAssignments();
	}

	public function addAssignmentToModule()
	{
		$input = $this->getDataFromForm("assignment");
		if ($message = $this->object->checkCourseOrGroupRefId($input["new_assignment_ref_id"]))
		{
			ilUtil::sendFailure($message);
			$this->editAssignments();
			return;
		}
		$module_assignment = array();
		$module_assignment[] = $input;
		$this->object->createAssignments($module_assignment, "mod");

		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
		ilEvaluationManagerOverview::_addMarkToObj($input["new_assignment_ref_id"]);

		ilUtil::sendSuccess($this->txt("assignment_created"));
		$this->editAssignments();
	}

	/*	 * ***************************************************
	 * ****************** EDIT ASSIGNMENTS ******************
	 * **************************************************** */

	public function editAssignment()
	{
		global $tpl, $ilTabs, $ilCtrl;
		$entry_eval_id = $_GET["edit"];
		$ilias_obj_ref_id = $_GET["edit_assignment"];
		$entry_type = ilObjEvaluationManager::_getTypeByEvalId($entry_eval_id);
		if ($entry_type == "lec")
		{
			$ilTabs->activateTab("lec");
			$ilCtrl->setParameter($this, "edit", $entry_eval_id);
			$this->editAssignments();
		}
		elseif ($entry_type == "mod")
		{
			$ilTabs->activateTab("mod");
			$data = array();
			$data["eval_id"] = $entry_eval_id;
			$data["ilias_obj"] = $ilias_obj_ref_id;
			$form_obj = new ilObjEvaluationManagerForms("edit_assignment", "mod", $this->session, $entry_eval_id);
			$form = $form_obj->init($this, $data);
			$tpl->setContent($form->getHTML());
		}
	}

	/*	 * ***************************************************
	 * ****************** DELETE ASSIGNMENTS ***********************
	 * **************************************************** */

	/* ------------------------------
	 * CALL TO FORMS *
	  ------------------------------- */

	public function confirmDeleteLectureAssignments()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("lec");
		$data = array();

		$ref_ids = $_POST['ref_id'];
		$eval_id = $_REQUEST["edit"];
		$data["entry_eval_id"] = $eval_id;
		$data["ilias_objs"] = $ref_ids;

		if (!is_array($ref_ids) or !count($ref_ids))
		{
			ilUtil::sendInfo($this->txt('select_one'));
			return $this->editAssignments();
		}

		$form_obj = new ilObjEvaluationManagerForms("delete_assignments", "lec", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this, $data);

		$tpl->setContent($form->getHTML());
	}

	public function confirmDeleteModuleAssignments()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("mod");
		$data = array();

		$ref_ids = $_POST['ref_id'];
		$eval_id = $_REQUEST["edit"];
		$data["entry_eval_id"] = $eval_id;
		$data["ilias_objs"] = $ref_ids;

		if (!is_array($ref_ids) or !count($ref_ids))
		{
			ilUtil::sendInfo($this->txt('select_one'));
			return $this->editAssignments();
		}

		$form_obj = new ilObjEvaluationManagerForms("delete_assignments", "mod", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this, $data);

		$tpl->setContent($form->getHTML());
	}

	/* ------------------------------
	 * MANAGE DELETE ASSIGNMENTS *
	  ------------------------------- */

	public function deleteLectureAssignments()
	{
		$eval_id = $_REQUEST["eval_id"];
		foreach ($_POST["ref_id"] as $ref_id)
		{
			ilObjLectureAssignment::_deleteLectureAssignment($eval_id, $ref_id);
		}
		ilUtil::sendSuccess($this->txt('assignments_deleted'));
		$this->editAssignments();
	}

	public function deleteModuleAssignments()
	{
		$eval_id = $_REQUEST["eval_id"];
		foreach ($_POST["ref_id"] as $ref_id)
		{
			ilObjModuleAssignment::_deleteModuleAssignment($eval_id, $ref_id);
		}
		ilUtil::sendSuccess($this->txt('assignments_deleted'));
		$this->editAssignments();
	}

	/**
	 * getDataFromForm gets the data sent from forms and set it with the correct style to be used by the plugin.
	 * @return array $input Array of data from the forms.
	 */
	public function getDataFromForm($type = "")
	{
		$input = array();

		if ($type == "assignment")
		{

			if ($_REQUEST["eval_id"] != "" AND $_REQUEST["eval_id"] != $this->txt('reval_id_demo'))
			{
				$input["eval_id"] = ilUtil::stripSlashes($_REQUEST["eval_id"]);
			}
			if ($_REQUEST["lecture_name"] != "" AND $_REQUEST["lecture_name"] != $this->txt('lecture_name_demo'))
			{
				$input["lecture_name"] = ilUtil::stripSlashes($_REQUEST["lecture_name"]);
			}
			if ($_REQUEST["lecturer_name"] != "" AND $_REQUEST["lecturer_name"] != $this->txt('lecturer_name_demo'))
			{
				$input["lecturer_name"] = ilUtil::stripSlashes($_REQUEST["lecturer_name"]);
			}
			if ($_REQUEST["eval_key"] != "" AND $_REQUEST["eval_key"] != $this->txt('eval_key_demo'))
			{
				$input["eval_key"] = ilUtil::stripSlashes($_REQUEST["eval_key"]);
			}
			if ($_REQUEST["eval_semester"] != "" AND $_REQUEST["eval_semester"] != $this->txt('eval_semester_demo'))
			{
				$input["eval_semester"] = ilUtil::stripSlashes($_REQUEST["eval_semester"]);
			}
			if ($_REQUEST["new_assignment_ref_id"] != "" AND $_REQUEST["new_assignment_ref_id"] != $this->txt('new_assignment_ref_id_demo'))
			{
				$input["new_assignment_ref_id"] = ilUtil::stripSlashes($_REQUEST["new_assignment_ref_id"]);
			}
			return $input;
		}

		//?ISSET

		if ($_REQUEST["eval_id"] != "" AND $_REQUEST["eval_id"] != $this->txt('eval_id_demo'))
		{
			$input["eval_id"] = ilUtil::stripSlashes($_REQUEST["eval_id"]);
		}
		if ($_REQUEST["eval_semester"] != "" AND $_REQUEST["eval_semester"] != $this->txt('eval_semester_demo'))
		{
			$input["eval_semester"] = ilUtil::stripSlashes($_REQUEST["eval_semester"]);
		}
		if ($_REQUEST["doc_function"] != "" AND $_REQUEST["doc_function"] != $this->txt('doc_function_demo'))
		{
			$input["doc_function"] = ilUtil::stripSlashes($_REQUEST["doc_function"]);
		}
		if ($_REQUEST["doc_salutation"] != "" AND $_REQUEST["doc_salutation"] != $this->txt('doc_salutation_demo'))
		{
			$input["doc_salutation"] = ilUtil::stripSlashes($_REQUEST["doc_salutation"]);
		}
		if ($_REQUEST["doc_title"] != "" AND $_REQUEST["doc_title"] != $this->txt('doc_title_demo'))
		{
			$input["doc_title"] = ilUtil::stripSlashes($_REQUEST["doc_title"]);
		}
		if ($_REQUEST["doc_firstname"] != "" AND $_REQUEST["doc_firstname"] != $this->txt('doc_firstname_demo'))
		{
			$input["doc_firstname"] = ilUtil::stripSlashes($_REQUEST["doc_firstname"]);
		}
		if ($_REQUEST["doc_lastname"] != "" AND $_REQUEST["doc_lastname"] != $this->txt('doc_lastname_demo'))
		{
			$input["doc_lastname"] = ilUtil::stripSlashes($_REQUEST["doc_lastname"]);
		}
		if ($_REQUEST["doc_email"] != "" AND $_REQUEST["doc_email"] != $this->txt('doc_email_demo'))
		{
			$input["doc_email"] = ilUtil::stripSlashes($_REQUEST["doc_email"]);
		}
		if ($_REQUEST["eval_name"] != "" AND $_REQUEST["eval_name"] != $this->txt('eval_name_demo'))
		{
			$input["eval_name"] = ilUtil::stripSlashes($_REQUEST["eval_name"]);
		}
		if ($_REQUEST["eval_key"] != "" AND $_REQUEST["eval_key"] != $this->txt('eval_key_demo'))
		{
			$input["eval_key"] = ilUtil::stripSlashes($_REQUEST["eval_key"]);
		}
		if ($_REQUEST["eval_type"] != "" AND $_REQUEST["eval_type"] != $this->txt('eval_type_demo'))
		{
			$input["eval_type"] = ilUtil::stripSlashes($_REQUEST["eval_type"]);
		}
		if ($_REQUEST["eval_questionnaire"] != "" AND $_REQUEST["eval_questionnaire"] != $this->txt('questionnaire_demo'))
		{
			$input["eval_questionnaire"] = ilUtil::stripSlashes($_REQUEST["eval_questionnaire"]);
		}
		if ($_REQUEST["eval_remarks"] != "" AND $_REQUEST["eval_remarks"] != $this->txt('eval_remarks_demo'))
		{
			$pre = str_replace(";", "", ilUtil::stripSlashes($_REQUEST["eval_remarks"]));
			$input["eval_remarks"] = trim($pre, "/n");
		}
		if ($_REQUEST["ilias_obj"] != "" AND $_REQUEST["ilias_obj"] !=$this->txt('ilias_obj_demo'))
		{
			$input["ilias_obj"] = ilUtil::stripSlashes($_REQUEST["ilias_obj"]);
		}
		if ($_REQUEST["keywords"] != "" AND $_REQUEST["keywords"] != $this->txt('keywords_demo'))
		{
			$input["keywords"] = array_filter(explode(",", ilUtil::stripSlashes($_REQUEST["keywords"])));
		}
		if ($_REQUEST["lecture_name"] != "" AND $_REQUEST["lecture_name"] != $this->txt('lecture_name_demo'))
		{
			$input["lecture_name"] = ilUtil::stripSlashes($_REQUEST["lecture_name"]);
		}
		if ($_REQUEST["lecturer_name"] != "" AND $_REQUEST["lecturer_name"] != $this->txt('lecturer_name_demo'))
		{
			$input["lecturer_name"] = ilUtil::stripSlashes($_REQUEST["lecturer_name"]);
		}

		$input["em_ref_id"] = $this->object->getRefId();

		if ($input["eval_semester"] == "" OR $input["eval_key"] == "")
		{
			$input = "ERROR_NO_SEMESTER_OR_KEY";
		}

		return $input;
	}


	/*	 * ***************************************************
	 * ****************** EVASYS EXPORT ***********************
	 * **************************************************** */

	/**
	 * This method shows a table with the current csv files created for evasys, with the possibility of
	 * download and delete it and a Toolbar with two buttons, one to export lectures and another to expor modules.
	 */
	public function evasysExport()
	{
		global $ilTabs;
		$ilTabs->activateTab("evasys");
		//Create and initialize the table of exported files
		$table_gui = new ilEvaluationManagerTableEvaSysExportGUI($this, "evasysExport", "all", $this->object);
		$table_gui->init($this);
		//Create and initialize the toolbal
		$form_obj = new ilObjEvaluationManagerForms("evasys_toolbar", "all", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);
		//Show both
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML() . "</br>" . $table_gui->getHTML());
	}

	/**
	 * This method Initializes the screen for choosing lectures to export
	 */
	public function exportEvasysLec()
	{
		global $ilTabs;
		$ilTabs->activateTab("evasys");
		//Create, initialize and get content of the lectures export to evasys table.
		$table_gui = new ilEvaluationManagerTableShowEntriesGUI($this, "exportEvasysLec", "lec", $this->object->getRefId());
		$table_gui->init($this, "evasys");
		$table_gui->getContent();
		//Create and initialize evasys filter
		$form_obj = new ilObjEvaluationManagerForms("evasys_filter", "lec", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);
		//Show both
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML() . "</br>" . $table_gui->getHTML());
	}

	/**
	 * This method Initializes the screen for choosing modules to export
	 */
	public function exportEvasysMod()
	{
		global $ilTabs;
		$ilTabs->activateTab("evasys");
		//Create, initialize and get content of the modules export to evasys table.
		$table_gui = new ilEvaluationManagerTableShowEntriesGUI($this, "exportEvasysMod", "mod", $this->object->getRefId());
		$table_gui->init($this, "evasys");
		$table_gui->getContent();
		//Create and initialize evasys filter
		$form_obj = new ilObjEvaluationManagerForms("evasys_filter", "mod", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);
		//Show both
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML() . "</br>" . $table_gui->getHTML());
	}

	/**
	 * This method creates the files for evaSys
	 */
	public function exportLecturesToEvaSys()
	{
		$evasys = new ilObjEvaluationManagerExportEvaSys($this->object->getId(), "lec", $_POST["eval_id"], $this->session->getSessionValue('filter', 'document_name'));
		$evasys->init();
		$this->evasysExport();
	}

	/**
	 * This method creates the files for evaSys
	 */
	public function exportModulesToEvaSys()
	{
		$evasys = new ilObjEvaluationManagerExportEvaSys($this->object->getId(), "mod", $_POST["eval_id"], $this->session->getSessionValue('filter', 'document_name'));
		$evasys->init();
		$this->evasysExport();
	}

	/**
	 * This method applies a filter in the creation of a lecture export to evasys file.
	 */
	public function applyFilterEvaSysLec()
	{
		$this->session->saveRequestValue('filter', 'semester_filter');
		$this->session->saveRequestValue('filter', 'course_filter');
		$this->session->setSessionValue('filter', 'keyword_filter', preg_replace('/\s+/', '', $this->session->getRequestValue('keyword_filter')));
		$this->session->saveRequestValue('filter', 'keyword_inverse');
		$this->session->saveRequestValue('filter', 'document_name');
		$this->exportEvasysLec();
	}

	/**
	 * This method applies a filter in the creation of a module export to evasys file.
	 */
	public function applyFilterEvaSysMod()
	{
		$this->session->saveRequestValue('filter', 'semester_filter');
		$this->session->saveRequestValue('filter', 'course_filter');
		$this->session->setSessionValue('filter', 'keyword_filter', preg_replace('/\s+/', '', $this->session->getRequestValue('keyword_filter')));
		$this->session->saveRequestValue('filter', 'keyword_inverse');
		$this->session->saveRequestValue('filter', 'document_name');
		$this->exportEvasysMod();
	}

	/**
	 * This method applies a filter in the overview of marked courses and groups.
	 */
	function applyFilterMarked() {
		$this->session->saveRequestValue('filter', 'marked_semester_filter');
		$this->overview();
	}


	/**
	 * This method reset the filter in the creation of a lecture export to evasys file.
	 */
	function resetFilterEvaSysLec()
	{
		$this->session->deleteSessionValues('filter');
		$this->exportEvasysLec();
	}

	/**
	 * This method reset the filter in the creation of a module export to evasys file.
	 */
	function resetFilterEvaSysMod()
	{
		$this->session->deleteSessionValues('filter');
		$this->exportEvasysMod();
	}

	/**
	 * This method resets a filter in the overview of marked courses and groups.
	 */
	function resetFilterMarked() {
		$this->session->setSessionValue('filter', 'marked_semester_filter', null);
		$this->overview();
	}


	/**
	 * Download csv file
	 */
	function downloadEvasysFile()
	{
		global $lng;
		$lng->loadLanguageModule("export");
		//Error no files selected
		if (!isset($_POST["file"]))
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), false);
			return $this->evasysExport();
		}
		//Error more than one file selected
		if (count($_POST["file"]) > 1)
		{
			ilUtil::sendFailure($lng->txt("select_max_one_item"), false);
			return $this->evasysExport();
		}
		//Download the file
		$file = explode(":", $_POST["file"][0]);
		include_once("./Services/Export/classes/class.ilExport.php");
		$export_dir = ilExport::_getExportDirectory($this->object->getId());
		ilUtil::deliverFile($export_dir . "/" . $file[1], $file[1]);
		return $this->evasysExport();
	}

	/**
	 * Delete files
	 */
	function deleteEvasysFile()
	{
		global $lng;
		$lng->loadLanguageModule("export");
		//Error no files selected
		if (!isset($_POST["file"]))
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			return $this->evasysExport();
		}
		//Delete files
		include_once("./Services/Export/classes/class.ilExport.php");
		$export_dir = ilExport::_getExportDirectory($this->object->getId());
		foreach ($_POST["file"] as $selected)
		{
			$file = explode(":", $selected);
			unlink($export_dir . "/" . $file[1]);
		}
		return $this->evasysExport();
	}

	/*	 * ***************************************************
	 * **** OVERVIEW OF MARKED COURSES AND GROUPS ************
	 * **************************************************** */

	public function overview()
	{
		global $ilCtrl, $ilTabs;

		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/table_classes/class.ilEvaluationManagerTableOverviewGUI.php');

		$ilTabs->activateTab("overview");


		//semester filter
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$options[''] = $this->txt("please_select");
		for ($year = date('Y') - 3; $year < date('Y') + 3; $year++)
		{
			$options[(string) $year . 's'] = sprintf('%s SS', $year);
			$options[(string) $year . 'w'] = sprintf('%s / %s WS', $year, $year + 1);
		}
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($this->txt('filter'));
		$semester = new ilSelectInputGUI($this->txt('semester_filter'), 'marked_semester_filter');
		$semester->setOptions($options);
		$semester->setValue($this->session->getSessionValue('filter', 'marked_semester_filter'));
		$form->addItem($semester);
		$form->addCommandButton("applyFilterMarked", $this->txt('apply_filter'));
		$form->addCommandButton("resetFilterMarked", $this->txt('reset_filter'));

		$table_gui = new ilEvaluationManagerTableOverviewGUI($this, "overview", "all", $this->object->getRefId(), $this->session->getSessionValue('filter', 'marked_semester_filter'));
		$table_gui->init($this);
		$table_gui->getContent();

		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML() . $table_gui->getHTML());
	}

	public function addMarkToCourseOrGroup($data = "")
	{
		global $ilTabs;
		$ilTabs->activateTab("overview");
		//Create and initialize form
		$form_obj = new ilObjEvaluationManagerForms("add_mark", "all", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this, $data);
		//Show both
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
	}

	public function addMark()
	{
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');

		if ($message = $this->object->checkCourseOrGroupRefId($_REQUEST["new_assignment_ref_id"]))
		{
			ilUtil::SendFailure($message);
			$this->overview();
			return;
		}
		ilEvaluationManagerOverview::_addMarkToObj($_REQUEST["new_assignment_ref_id"]);
		ilUtil::sendInfo($this->txt('overview_created'));
		$this->overview();
	}

	public function confirmDeleteMark()
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab("overview");
		$ref_ids = $_POST['ref_id'];

		if (!is_array($ref_ids) or !count($ref_ids))
		{
			ilUtil::sendInfo($this->txt('select_one'));
			return $this->overview();
		}

		$form_obj = new ilObjEvaluationManagerForms("delete", "overview", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this, $ref_ids);

		$tpl->setContent($form->getHTML());
	}

	public function deleteMark()
	{
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
		foreach ($_POST["ref_id"] as $event_id)
		{
			ilEvaluationManagerOverview::_deleteAssigned($event_id);
		}
		ilUtil::sendSuccess($this->txt('overview_deleted'));
		$this->overview();
	}

	public function exportOverview()
	{
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/class.ilEvaluationManagerOverview.php');
		require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/export/class.ilObjEvaluationManagerExportCSV.php');
		$assignments = ilEvaluationManagerOverview::_getOverview($this->session->getSessionValue('filter', 'marked_semester_filter'));
		$final_assignments = array();
		if (is_array($assignments))
		{
			foreach ($assignments as $assignment)
			{
				unset($assignment["path"]);
				$final_assignments[] = $assignment;
			}
		}
		ilObjEvaluationManagerExportCSV::_writeCSVofMarkedAssignments($final_assignments);
	}

	/*	 * ***************************************************
	 * **** SETTINGS ************
	 * **************************************************** */

	public function settings()
	{
		global $ilTabs;
		$ilTabs->activateTab("settings");
		//Create and initialize form
		$form_obj = new ilObjEvaluationManagerForms("settings", "all", $this->session, $this->object->getRefId());
		$form = $form_obj->init($this);
		//Show both
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
	}

	public function applySettings()
	{
		//require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.v1tov2.php');
		//$script = new v1tov2();
		//$script->callScripts();

		$root_ref_id = ilUtil::stripSlashes($_REQUEST["root_ref_id"]);
		ilObjEvaluationManager::_manageRootForOverview($this->object->getRefId(), $root_ref_id);
		return $this->settings();
	}


	/* ***************************************************
	 * ****************** TREE SELECTION ******************
	 * **************************************************** */

	public function showRepositorySelection()
	{
		global $ilTabs;
		$type = ilObjEvaluationManager::_getTypeByEvalId($_GET["edit"]);
		if ($type == "lec")
		{
			$ilTabs->activateTab("lec");
		}
		elseif ($type == "mod")
		{
			$ilTabs->activateTab("mod");
		}
		else
		{
			$ilTabs->activateTab("overview");
		}

		if (!isset($_GET["search_root_expand"]))
		{
			$_GET["search_root_expand"] = $this->object->getRootRefId();
		}

		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
		$selector = new ilRepositorySelectorInputGUI();
		$selector->setClickableTypes(array('crs','grp'));
		$selector->setHeaderMessage($this->txt('select_assignment_object'));
		return $selector->showRepositorySelection();
	}

	public function reset()
	{
		$this->selectRepositoryItem();
	}

	public function selectRepositoryItem()
	{
		$ilias_obj = $_GET["root_id"];
		$eval_id = $_GET["edit"];

		if ($eval_id == "mark")
		{
			$this->addMarkToCourseOrGroup($ilias_obj);
		}
		else
		{
			$type = ilObjEvaluationManager::_getTypeByEvalId($eval_id);
			if ($type == "lec")
			{
				$this->addLectureAssignment($ilias_obj);
			}
			elseif ($type == "mod")
			{
				$this->addModuleAssignment($ilias_obj);
			}
		}
	}
}

?>