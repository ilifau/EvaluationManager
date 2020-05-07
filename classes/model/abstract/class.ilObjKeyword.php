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

    protected static $cache;

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

    public static function _preload($em_ref_id) {

        static::$cache = [];

        global $DIC;
        $ilDB = $DIC->database();

        $query = "SELECT k.* FROM rep_robj_xema_key k INNER JOIN rep_robj_xema_eval e ON e.eval_id = k.eval_id"
            . " WHERE e.em_ref_id = " . $ilDB->quote($em_ref_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            static::$cache[$row['eval_id']][] = $row;
        }
    }
}
?>