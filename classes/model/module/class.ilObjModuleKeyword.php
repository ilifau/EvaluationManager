<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/abstract/class.ilObjKeyword.php');

/**
 * Module obj class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjModuleKeyword extends ilObjKeyword {

    /**
     * Constructor
     * 
     * @param array 	data
     * The constructor should receives the eval_id of the module and the keyword.
     */
    function __construct($a_eval_id, $a_keyword) {

        $this->setEvalId($a_eval_id);
        $this->setKeyword(strtolower($a_keyword));
        $this->setKeywordType("mod");
    }

    /*     * ***************************************************
     * **** INDIVIDUAL DATABASE MANAGEMENT OF MODULE KEYWORDS*****
     * **************************************************** */

    /**
     * Insert module keyword
     * 
     * It's neccessary to create a Module keyword object before calling this method
     */
    public function insertModuleKeyword() {
        global $ilDB;
        
        $ilDB->insert("rep_robj_xema_key", array(
            "eval_id" => array("integer", $this->getEvalId()),
            "keyword" => array("text", strtolower($this->getKeyword())),
            "type" => array("text", $this->getKeywordType())));

        //returns the object of the module keyword inserted
        return $this;
    }

    /**
     * Update module keyword
     * 
     * @param integer 	eval_id of the module_keyword to update
     * @param object 	module_keyword should be the module_keyword object to update
     */
    public static function _updateModuleKeyword($eval_id, $module_keyword) {
        global $ilDB;

        $ilDB->replace("rep_robj_xema_key", array(
            "eval_id" => array("integer", $eval_id),
            "keyword" => array("text", strtolower($module_keyword->getKeyword())),
            "type" => array("text", $module_keyword->getKeywordType())));

        $module_keyword->setEvalId($eval_id);

        return $module_keyword;
    }

    /**
     * Delete module keyword
     * 
     * @param integer 	eval_id should be the eval_id of the object to delete
     * @param text 	keyword should be the keyword to delete
     */
    public static function _deleteModuleKeyword($eval_id, $keyword) {
        global $ilDB;

        $query = "DELETE FROM rep_robj_xema_key "
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND keyword = " . $ilDB->quote($keyword, 'text');

        $ilDB->query($query);

        return true;
    }

    /**
     * Read module keyword
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     * @param integer 	keyword should be the keyword to read
     */
    public static function _readModuleKeyword($eval_id, $keyword) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_key"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND keyword = " . $ilDB->quote($keyword, 'text');

        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);

        $module_keyword = new ilObjModuleKeyword($data["eval_id"], $data["keyword"]);

        return $module_keyword;
    }

    /*     * ***************************************************
     * **** MULTIPLE DATABASE MANAGEMENT OF MODULES KEYWORDS*****
     * **************************************************** */

    /**
     * Read module keywords
     * 
     * @param integer 	eval_id should be the eval_id of the module to read the keywords
     */
    public static function _readModuleKeywords($eval_id) {
        global $ilDB;
        $module_keywords = array();

        $query = "SELECT * FROM rep_robj_xema_key"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $module_keyword = new ilObjModuleKeyword($data["eval_id"], $data["keyword"]);
            $module_keywords[] = $module_keyword;
        }
        return $module_keywords;
    }

    /**
     * Delete all module keywords
     * 
     * @param integer 	eval_id should be the eval_id of the module to delete the keywords
     */
    public static function _deleteModuleKeywords($eval_id) {
        global $ilDB;

        $query = "DELETE FROM rep_robj_xema_key"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $ilDB->query($query);
        return true;
    }

    /*     * ***************************************************
     * **** CHECK DATABASE MANAGEMENT OF LECTURE KEYWORDS*****
     * **************************************************** */

    /**
     * This method checks the existence of a module keyword to decide if a new module keyword should be inserted
     * or a previous module keyword must be updated.
     * 
     * Returns a boolean that will be true if module keyword already exists and false if not.
     * 
     * @param integer 	$eval_id of the module keyword to check their existence
     * @param text 	$keyword is the keyword of the module to check their existence
     */
    public static function _checkExistenceOfModuleKeyword($eval_id, $keyword) {
        global $ilDB;
        $query = "SELECT eval_id FROM rep_robj_xema_key WHERE eval_id = " . $ilDB->quote($eval_id, 'integer') .
                " AND keyword = " . $ilDB->quote($keyword, 'text') .
                " AND type = 'mod'";
        $result = $ilDB->query($query);
        if ($ilDB->fetchAssoc($result)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method updates an keyword from an array of keyword without change anything into the database
     * 
     * Returns the new array of keywords
     * 
     * @param array 	$module_keyqords is an array of module keywords objects
     * @param object 	$updated_keyword is the new module keyqord to insert into the array
     */
    public static function _addUpdatedModuleKeywordToList($module_keywords, $updated_keyword) {

        if (is_array($module_keywords)) {
            foreach ($module_keywords as $key => $lecture) {
                if ($lecture->getEvalId() == $updated_keyword->getEvalId()) {
                    unset($module_keywords[$key]);
                    $module_keywords[$key] = $updated_keyword;
                }
            }
        }
        return $module_keywords;
    }

}

?>
