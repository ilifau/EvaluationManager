<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/abstract/class.ilObjEvaluation.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModuleAssignment.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModuleKeyword.php');

/**
 * Module obj class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjModule extends ilObjEvaluation {

    private $doc_function;
    private $doc_salutation;
    private $doc_title;
    private $doc_firstname;
    private $doc_lastname;
    private $doc_email;
    private $eval_name;
    private $eval_questionnaire;
    private $eval_remarks;

    /**
     * Constructor
     * 
     * @param array 	data is an array with  
     * The constructor should receive a data array.
     */
    function __construct($data = "") {

        $this->setEvalId($data["eval_id"]);

        $this->setEvalSemester(strtolower($data["eval_semester"]));

        $this->setDocFunction($data["doc_function"]);
        $this->setDocSalutation($data["doc_salutation"]);
        $this->setDocTitle($data["doc_title"]);
        $this->setDocFirstname($data["doc_firstname"]);
        $this->setDocLastname($data["doc_lastname"]);
        $this->setDocEmail($data["doc_email"]);

        $this->setEvalKey($data["eval_key"]);
        //establish eval_type as "lec"
        $this->setEvalType("mod");

        $this->setEvalName($data["eval_name"]);
        $this->setEvalQuestionnaire($data["eval_questionnaire"]);
        $this->setEvalRemarks($data["eval_remarks"]);

        $this->setEMRefId($data["em_ref_id"]);

        if ($this->getEvalId()) {
            $this->setKeywords(ilObjModuleKeyword::_readModuleKeywords($this->getEvalId()));
            $this->setAssignments(ilObjModuleAssignment::_readModuleAssignments($this->getEvalId()));
        } else {
            $this->setKeywords(array());
            $this->setAssignments(array());
        }
    }

    /*     * ***************************************************
     * **************** GETTERS AND SETTERS ******************
     * **************************************************** */

    public function getDocFunction() {
        return $this->doc_function;
    }

    public function setDocFunction($var) {
        $this->doc_function = $var;
    }

    public function getDocSalutation() {
        return $this->doc_salutation;
    }

    public function setDocSalutation($var) {
        $this->doc_salutation = $var;
    }

    public function getDocTitle() {
        return $this->doc_title;
    }

    public function setDocTitle($var) {
        $this->doc_title = $var;
    }

    public function getDocFirstname() {
        return $this->doc_firstname;
    }

    public function setDocFirstname($var) {
        $this->doc_firstname = $var;
    }

    public function getDocLastname() {
        return $this->doc_lastname;
    }

    public function setDocLastname($var) {
        $this->doc_lastname = $var;
    }

    public function getDocEmail() {
        return $this->doc_email;
    }

    public function setDocEmail($var) {
        $this->doc_email = $var;
    }

    public function getEvalName() {
        return $this->eval_name;
    }

    public function setEvalName($var) {
        $this->eval_name = $var;
    }

    public function getEvalQuestionnaire() {
        return $this->eval_questionnaire;
    }

    public function setEvalQuestionnaire($var) {
        $this->eval_questionnaire = $var;
    }

    public function getEvalRemarks() {
        return $this->eval_remarks;
    }

    public function setEvalRemarks($var) {
        $this->eval_remarks = $var;
    }

    /*     * ***************************************************
     * **** INDIVIDUAL DATABASE MANAGEMENT OF MODULES *****
     * **************************************************** */

    /**
     * Insert module
     * 
     * It's neccessary to create a Module object before calling this method.
     */
    public function insertModule() {
        global $ilDB;

        //Get next Id for the module and set as current eval_id
        $this->setEvalId($ilDB->nextId('rep_robj_xema_eval'));

        $ilDB->insert("rep_robj_xema_eval", array(
            "eval_id" => array("integer", $this->getEvalId()),
            "eval_semester" => array("text", strtolower($this->getEvalSemester())),
            "doc_function" => array("text", $this->getDocFunction()),
            "doc_salutation" => array("text", $this->getDocSalutation()),
            "doc_title" => array("text", $this->getDocTitle()),
            "doc_firstname" => array("text", $this->getDocFirstname()),
            "doc_lastname" => array("text", $this->getDocLastname()),
            "doc_email" => array("text", $this->getDocEmail()),
            "eval_name" => array("text", $this->getEvalName()),
            "eval_key" => array("text", $this->getEvalKey()),
            "eval_type" => array("text", $this->getEvalType()),
            "eval_questionnaire" => array("text", $this->getEvalQuestionnaire()),
            "eval_remarks" => array("text", $this->getEvalRemarks()),
            "em_ref_id" => array("integer", $this->getEMRefId())
        ));

        //returns the object of the module inserted
        return $this;
    }

    /**
     * Update module
     * 
     * @param integer 	eval_id should be the eval_id of the object to update
     * @param object    module should be a ilObjModule object. 
     */
    public function updateModule($eval_id, $module) {
        global $ilDB;

        $ilDB->replace("rep_robj_xema_eval", array(
            "eval_id" => array("integer", $eval_id)), array(
            "eval_semester" => array("text", strtolower($module->getEvalSemester())),
            "doc_function" => array("text", $module->getDocFunction()),
            "doc_salutation" => array("text", $module->getDocSalutation()),
            "doc_title" => array("text", $module->getDocTitle()),
            "doc_firstname" => array("text", $module->getDocFirstname()),
            "doc_lastname" => array("text", $module->getDocLastname()),
            "doc_email" => array("text", $module->getDocEmail()),
            "eval_name" => array("text", $module->getEvalName()),
            "eval_key" => array("text", $module->getEvalKey()),
            "eval_type" => array("text", $module->getEvalType()),
            "eval_questionnaire" => array("text", $module->getEvalQuestionnaire()),
            "eval_remarks" => array("text", $module->getEvalRemarks()),
            "em_ref_id" => array("integer", $module->getEMRefId())
        ));

        $this->setEvalId($eval_id);

        $this->setEvalSemester($module->getEvalSemester());

        $this->setDocFunction($module->getDocFunction());
        $this->setDocSalutation($module->getDocSalutation());
        $this->setDocTitle($module->getDocTitle());
        $this->setDocFirstname($module->getDocFirstname());
        $this->setDocLastname($module->getDocLastname());
        $this->setDocEmail($module->getDocEmail());

        $this->setEvalKey($module->getEvalKey());
        $this->setEvalType($module->getEvalType());
        $this->setEvalName($module->getEvalName());
        $this->setEvalQuestionnaire($module->getEvalQuestionnaire());
        $this->setEvalRemarks($module->getEvalRemarks());

        $this->setEMRefId($module->getEMRefId());
    }

    /**
     * Delete module
     * 
     * @param integer 	eval_id should be the eval_id of the object to delete
     */
    public static function _deleteModule($eval_id) {
        global $ilDB;

        $query = "DELETE FROM rep_robj_xema_eval"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $ilDB->query($query);

        return true;
    }

    /**
     * Read module
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     */
    public static function _readModule($eval_id) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_eval"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);

        $module = new ilObjModule($data);

        return $module;
    }

    /*     * ***************************************************
     * **** MULTIPLE DATABASE MANAGEMENT OF MODULES *****
     * **************************************************** */

    /**
     * Read modules
     * 
     * Return all modules of the current evaluation manager
     */
    public static function _readModules($em_ref_id) {
        global $ilDB;
        $modules = array();

        $query = "SELECT * FROM rep_robj_xema_eval"
                . " WHERE em_ref_id = " . $ilDB->quote($em_ref_id, 'integer') . " "
                . "AND eval_type = 'mod'";

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $module = new ilObjModule($data);
            $modules[] = $module;
        }

        return $modules;
    }

    /*     * ***************************************************
     * ******* CHECK DATABASE MANAGEMENT OF MODULES ********
     * **************************************************** */

    /**
     * This method checks the existence of a module to decide if a new module should be inserted
     * or a previous module must be updated.
     * 
     * Returns a boolean that will be true if module already exists and false if not.
     * 
     * @param text 	semester is the semester of the module to check their existence
     * @param text 	eval_key is the eval_key of the module to check their existence
     * @param integer 	em_ref_id is the ref_id of the evaluation manager object
     */
    public static function _checkExistenceOfModule($semester, $eval_key, $em_ref_id) {
        global $ilDB;
        $query = "SELECT eval_id FROM rep_robj_xema_eval WHERE eval_semester = " . $ilDB->quote($semester, 'text') .
                " AND eval_key = " . $ilDB->quote($eval_key, 'text') .
                " AND em_ref_id = " . $ilDB->quote($em_ref_id, 'integer') .
                " AND eval_type = 'mod'";
        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);
        if ($data) {
            return $data["eval_id"];
        } else {
            return false;
        }
    }

    /*     * ***************************************************
     * ***** MANAGEMENT OF KEYWORDS AND ASSIGNMENTS ****
     * ***************************************************** */

    /**
     * Manage creation of keywords 
     * 
     * @param array     $keywords_array should contains the keywords of the module.
     * @return array    $array_of_keywords is an array with the keywords object created.
     */
    public function createKeywords($keywords_array) {
        $array_of_keywords = array();

        if (is_array($keywords_array)) {
            foreach ($keywords_array as $keyword) {
                $keyword = new ilObjModuleKeyword($this->getEvalId(), $keyword);
                $array_of_keywords[] = $keyword;
            }
        }

        return $array_of_keywords;
    }

    /**
     * Manage creation of assignments
     * 
     * @param integer     $ilias_obj is the ref if of the assignment object.
     * @param text     $lecture_name is name of the lecture asigned to the module.
     * @param text     $lecturer_name is name of the lecturer asigned to the module.
     * @return obj    $assignments is the assignments object of an evaluation.
     */
    public function createAssignment($ilias_obj, $lecture_name, $lecturer_name) {
        $assignment = new ilObjModuleAssignment($this->getEvalId(), $ilias_obj, $lecture_name, $lecturer_name);
        return $assignment;
    }

    /**
     * Comparison of keywords arrays, return true in case of the keyword of the $keywords_array are
     * the keywords of the module.
     * 
     * @param array 	$keywords_array  contains the keywords of the module.
     * 
     */
    public function keywordsCompare($keywords_array) {
        $counter = 0;
        $this_keywords = $this->getKeywords();
        if (is_array($this_keywords)) {
            //The entry has keywords
            $size_of_filter_keywords = sizeof($keywords_array);
            foreach ($this_keywords as $entry_keyword) {
                foreach ($keywords_array as $filtered_keyword) {
                    if ($filtered_keyword == $entry_keyword->getKeyword()) {
                        $counter++;
                    }
                    if ($counter == $size_of_filter_keywords) {
                        return true;
                    }
                }
            }
        } else {
            //The entry hasn't keywords
            return false;
        }
    }

    /**
     * Comparison of keywords arrays, return true in case of any keyword of the $keywords_array
     * are a keyword of the module
     * 
     * @param array 	$keywords_array  contains the keywords of the module.
     * 
     */
    public function keywordsInverseCompare($keywords_array) {
        $counter = 0;
        $this_keywords = $this->getKeywords();
        if (is_array($this_keywords)) {
            //The entry has keywords
            foreach ($this_keywords as $entry_keyword) {
                foreach ($keywords_array as $filtered_keyword) {
                    if ($filtered_keyword == $entry_keyword->getKeyword()) {
                        return false;
                    }
                }
            }
            if ($counter == 0) {
                return true;
            }
        } else {
            //The entry hasn't keywords
            return true;
        }
    }

}

?>