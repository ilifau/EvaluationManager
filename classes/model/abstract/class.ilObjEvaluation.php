<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Abstract class Evaluation, Lectures and modules extend this class.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */

abstract class ilObjEvaluation {
    
    private $eval_id; //PK of the eval table
    private $eval_semester; //Semester of the evaluation #1
    private $eval_key; //Key of the evaluation #1
    private $eval_type; // type of the evaluation, "lec" for lectures and "mod" for modules.
    private $em_ref_id; //ref_id of the Evaluation manager object which created the evaluation
    
    //#1 eval_semester and eval_key are indentifiers of the evaluation, there is no evaluation with the same eval_semester and eval_key.
    
    //Keywords
    private $keywords = array();
    
    //Assignments
    private $assignments = array();
    
    /*
     * Getters and setters for atributes
     */
    public function getEvalId() {
        return $this->eval_id;
    }

    public function setEvalId($var) {
        $this->eval_id = $var;
    }

    public function getEvalSemester() {
        return $this->eval_semester;
    }

    public function setEvalSemester($var) {
        $this->eval_semester = $var;
    }

    public function getEvalKey() {
        return $this->eval_key;
    }

    public function setEvalKey($var) {
        $this->eval_key = $var;
    }

    public function getEvalType() {
        return $this->eval_type;
    }

    public function setEvalType($var) {
        $this->eval_type = $var;
    }
    
    public function getEMRefId() {
        return $this->em_ref_id;
    }

    public function setEMRefId($var) {
        $this->em_ref_id = $var;
    }

    public function getKeywords() {
        return $this->keywords;
    }

    public function setKeywords($var) {
        $this->keywords = $var;
    }
    
    public function getAssignments() {
        return $this->assignments;
    }

    public function setAssignments($var) {
        $this->assignments = $var;
    }
    
    
}
?>
