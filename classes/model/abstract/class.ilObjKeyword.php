<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Abstract class Assignment, lectures and modules assignments extend this class.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */

abstract class ilObjKeyword {
    
    private $eval_id; //id of the evaluation
    private $keyword; //Keyword assigned to the evaluation
    private $type; //Keyword type

    /*
     * Getters and setters for atributes
     */
    public function getEvalId() {
        return $this->eval_id;
    }

    public function setEvalId($var) {
        $this->eval_id = $var;
    }

    public function getKeyword() {
        return $this->keyword;
    }

    public function setKeyword($var) {
        $this->keyword = $var;
    }
    
    public function getKeywordType() {
        return $this->type;
    }

    public function setKeywordType($var) {
        $this->type = $var;
    }

}
?>