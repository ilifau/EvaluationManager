<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/abstract/class.ilObjKeyword.php');

/**
 * Lecture obj class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */

class ilObjLectureKeyword extends ilObjKeyword {
    
    /**
     * Constructor
     * 
     * @param array 	data
     * The constructor should receives the eval_id of the lecture and the keyword.
     */
    function __construct($a_eval_id, $a_keyword) {

        $this->setEvalId($a_eval_id);
        $this->setKeyword(strtolower($a_keyword));
        $this->setKeywordType("lec");
        
    }
    
    /*     * ***************************************************
     * **** INDIVIDUAL DATABASE MANAGEMENT OF LECTURE KEYWORDS*****
     * **************************************************** */

    /**
     * Insert lecture keyword
     * 
     * It's neccessary to create a Lecture keyword object before calling this method
     */
    public function insertLectureKeyword() {
        global $ilDB;
        $ilDB->insert("rep_robj_xema_key", array(
            "eval_id" => array("integer", $this->getEvalId()),
            "keyword" => array("text", strtolower($this->getKeyword())),
            "type" => array("text", $this->getKeywordType())));

        //returns the object of the lecture keyword inserted
        return $this;
    }

    /**
     * Update lecture keyword
     * 
     * @param integer 	eval_id of the lecture_keyword to update
     * @param object 	lecture_keyword should be the lecture_keyword object to update
     */
    public static function _updateLectureKeyword($eval_id, $lecture_keyword) {
        global $ilDB;

        $ilDB->replace("rep_robj_xema_key", array(
            "eval_id" => array("integer", $eval_id),
            "keyword" => array("text", strtolower($lecture_keyword->getKeyword())),
            "type" => array("text", $lecture_keyword->getKeywordType())));

        $lecture_keyword->setEvalId($eval_id);

        return $lecture_keyword;
    }

    /**
     * Delete lecture keyword
     * 
     * @param integer 	eval_id should be the eval_id of the object to delete
     * @param text 	keyword should be the keyword to delete
     */
    public static function _deleteLectureKeyword($eval_id, $keyword) {
        global $ilDB;

        $query = "DELETE FROM rep_robj_xema_key "
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND keyword = " . $ilDB->quote($keyword, 'text');

        $ilDB->query($query);

        return true;
    }

    /**
     * Read lecture keyword
     * 
     * @param integer 	eval_id should be the eval_id of the object to read
     * @param integer 	keyword should be the keyword to read
     */
    public static function _readLectureKeyword($eval_id, $keyword) {
        global $ilDB;

        $query = "SELECT * FROM rep_robj_xema_key"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer')
                . " AND keyword = " . $ilDB->quote($keyword, 'text');

        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);

        $lecture_keyword = new ilObjLectureKeyword($data["eval_id"], $data["keyword"]);

        return $lecture_keyword;
    }

    /*     * ***************************************************
     * **** MULTIPLE DATABASE MANAGEMENT OF LECTURE KEYWORDS*****
     * **************************************************** */

    /**
     * Read lecture keywords
     * 
     * @param integer 	eval_id should be the eval_id of the lecture to read the keywords
     */
    public static function _readLectureKeywords($eval_id) {
        global $ilDB;
        $lecture_keywords = array();

        $query = "SELECT * FROM rep_robj_xema_key"
                . " WHERE eval_id = " . $ilDB->quote($eval_id, 'integer');

        $result = $ilDB->query($query);
        while ($data = $ilDB->fetchAssoc($result)) {
            $lecture_keyword = new ilObjLectureKeyword($data["eval_id"], $data["keyword"]);
            $lecture_keywords[] = $lecture_keyword;
        }

        return $lecture_keywords;
    }
    
    /**
     * Delete all lecture keywords
     * 
     * @param integer 	eval_id should be the eval_id of the lecture to delete the keywords
     */
    public static function _deleteLectureKeywords($eval_id) {
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
     * This method checks the existence of a lecture keyword to decide if a new lecture keyword should be inserted
     * or a previous lecture keyword must be updated.
     * 
     * Returns a boolean that will be true if lecture keyword already exists and false if not.
     * 
     * @param integer 	$eval_id of the lecture keyword to check their existence
     * @param text 	$keyword is the keyword of the lecture to check their existence
     */
    public static function _checkExistenceOfLectureKeyword($eval_id, $keyword) {
        global $ilDB;
        $query = "SELECT eval_id FROM rep_robj_xema_key WHERE eval_id = " . $ilDB->quote($eval_id, 'integer') .
                " AND keyword = " . $ilDB->quote($keyword, 'text') .
                " AND type = 'lec'";
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
     * @param array 	$lecture_keyqords is an array of lecture keywords objects
     * @param object 	$updated_keyword is the new lecture keyqord to insert into the array
     */
    public static function _addUpdatedLectureKeywordToList($lecture_keywords, $updated_keyword) {

        if (is_array($lecture_keywords)) {
            foreach ($lecture_keywords as $key => $lecture) {
                if($lecture->getEvalId() == $updated_keyword->getEvalId()) {
                    unset($lecture_keywords[$key]);
                    $lecture_keywords[$key] = $updated_keyword;
                }
            }
        }
        return $lecture_keywords;
    }
    

    
}
?>
