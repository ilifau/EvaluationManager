<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Filter class for the evaluation manager plugin.
 * This class should be called always to show the lectures or the modules of an evaluation manager.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjEvaluationManagerFilter {

    //Evaluation manager object ref_id
    private $evaluation_manager;
    //Type of object
    private $type;
    //Filter parameters
    private $semester;
    private $number_of_assignments;
    private $keywords;
    private $keywords_inverse;

    public function __construct($evaluation_manager, $type, $semester = null, $number_of_assignments = null, $keywords = null, $keywords_inverse = null) {

        $this->setEvaluationManagerRefId($evaluation_manager);
        $this->setType($type);
        $this->setSemester(strtolower($semester));
        $this->setNumberOfAssignments($number_of_assignments);
        if ($keywords) {
            $this->setKeywords(explode(",", strtolower($keywords)));
        }
        if ($keywords AND $keywords_inverse) {
            $this->setKeywordsInverse($keywords_inverse);
        }
    }

    /*     * ***************************************************
     * **************** GETTERS AND SETTERS ******************
     * **************************************************** */

    public function getEvaluationManagerRefId() {
        return $this->evaluation_manager;
    }

    public function setEvaluationManagerRefId($var) {
        $this->evaluation_manager = $var;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($var) {
        $this->type = $var;
    }

    public function getSemester() {
        return $this->semester;
    }

    public function setSemester($var) {
        $this->semester = $var;
    }

    public function getNumberOfAssignments() {
        return $this->number_of_assignments;
    }

    public function setNumberOfAssignments($var) {
        $this->number_of_assignments = $var;
    }

    public function getKeywords() {
        return $this->keywords;
    }

    public function setKeywords($var) {
        $this->keywords = $var;
    }

    public function getKeywordsInverse() {
        return $this->keywords_inverse;
    }

    public function setKeywordsInverse($var) {
        $this->keywords_inverse = $var;
    }

    /*     * ***************************************************
     * **************** FILTER METHODs ******************
     * **************************************************** */

    /*
     * This is the main method of this class, it calls the different filters in case of they are active
     * and return an array of entries.
     */

    public function filter() {

        //#1 Get all the entries of this type and this evaluation manager
        $entries = $this->getAllEntries();
        //#2 Filter entries by semester in case of semester filter is active
        if ($this->getSemester()) {
            $entries = $this->filterBySemester($entries);
        }
        //#3 Filter entries by number of assignments in case of number of assignments filter is active
        if ($this->getNumberOfAssignments() != null) {
            $entries = $this->filterByNumberOfAssignments($entries);
        }
        //#4 Filter entries by keywords in case of keywords filter is active
        if (!$this->getKeywordsInverse() AND sizeof($this->getKeywords())) {
            $entries = $this->filterByKeywords($entries);
        }
        //#5 Filter entries by inverse keywords in case of keywords inverse filter is active
        if ($this->getKeywordsInverse() AND sizeof($this->getKeywords())) {
            $entries = $this->filterByKeywordsInverse($entries);
        }
        //#6 Return the array of entries as the final entries.
        return $entries;
    }

    /**
     * This method gets an array of lectures or modules, with any filter, to filter it after.
     * Should be called first in filter() and throws an error if the type is not valid.
     * 
     */
    public function getAllEntries() {

        if ($this->getType() == 'lec') {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLecture.php');
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLectureAssignment.php');
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/lecture/class.ilObjLectureKeyword.php');
            ilObjLectureAssignment::_preload($this->getEvaluationManagerRefId());
            ilObjLectureKeyword::_preload($this->getEvaluationManagerRefId());
            return ilObjLecture::_readLectures($this->getEvaluationManagerRefId());
        } elseif ($this->getType() == 'mod') {
            require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/model/module/class.ilObjModule.php');
            return ilObjModule::_readModules($this->getEvaluationManagerRefId());
        } else {
            return false;
        }
    }

    /**
     * Filter by semester  the entries in case of semester filter is active. 
     * 
     * @param array 	entries Is an array of Lectures or modules.
     */
    public function filterBySemester($entries) {
        if (is_array($entries) AND sizeof($entries)) {
            foreach ($entries as $key => $entry) {
                if ($entry->getEvalSemester() != $this->getSemester()) {
                    unset($entries[$key]);
                }
            }
        }
        return $entries;
    }

    /**
     * Filter by number of assignments  the entries in case of number of assignments filter is active. 
     * 
     * @param array 	entries Is an array of Lectures or modules.
     */
    public function filterByNumberOfAssignments($entries) {
        if (is_array($entries) AND sizeof($entries)) {
            foreach ($entries as $key => $entry) {
                //The possible values of number of assignments are 0, for no assignments filer,
                //1 for one assignments filter and 2 for more assignments filter.

                if ($this->getNumberOfAssignments() == 0) {
                    if (sizeof($entry->getAssignments()) != $this->getNumberOfAssignments()) {
                        unset($entries[$key]);
                    }
                } elseif ($this->getNumberOfAssignments() == 1) {
                    if (sizeof($entry->getAssignments()) != $this->getNumberOfAssignments()) {
                        unset($entries[$key]);
                    }
                } elseif ($this->getNumberOfAssignments() == 2) {
                    if (sizeof($entry->getAssignments()) < $this->getNumberOfAssignments()) {
                        unset($entries[$key]);
                    }
                } else {
                    return false;
                }
            }
        }
        return $entries;
    }

    /**
     * Filter by keywords the entries in case of keywords filter is active. 
     * 
     * @param array 	entries Is an array of Lectures or modules.
     */
    public function filterByKeywords($entries) {
        if (is_array($entries) AND sizeof($entries)) {
            foreach ($entries as $key => $entry) {
                $keywords = $entry->getKeywords();
                if (is_array($keywords)) {
                    if (!$entry->keywordsCompare($this->getKeywords())) {
                        unset($entries[$key]);
                    }
                } else {
                    unset($entries[$key]);
                }
            }
        }
        return $entries;
    }

    /**
     * Filter by keywords inverse the entries in case of keywords inverse filter is active. 
     * 
     * @param array 	entries Is an array of Lectures or modules.
     */
    public function filterByKeywordsInverse($entries) {
        if (is_array($entries) AND sizeof($entries)) {
            foreach ($entries as $key => $entry) {
                $keywords = $entry->getKeywords();
                if (is_array($keywords)) {
                    if (!$entry->keywordsInverseCompare($this->getKeywords())) {
                        unset($entries[$key]);
                    }
                }
            }
        }
        return $entries;
    }

}

?>
