<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
include_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/export/class.ilObjEvaluationManagerExportEvaSys.php');

/**
 * Table class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 *
 */
class ilEvaluationManagerTableEvaSysExportGUI extends ilTable2GUI {

    private $type;
    private $ref_id;
    //Attribute for export table
    protected $custom_columns = array();

    public function __construct($a_parent_obj, $a_parent_cmd, $type, $object) {

		$this->setId('xema_eva_export');

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTableType($type);
        $this->setTableRefId($object->getRefId());
        $this->obj = $object;
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
        if ($this->getTableType() == "all") {
            $this->initExportTable($parent_obj);
        } else {
            return null;
        }
    }

    /*
     * Table for exported files
     */

    public function initExportTable($parent_obj) {
        global $ilCtrl, $lng;
        $this->setTitle($lng->txt("rep_robj_xema_exp_export_files"));

        $this->addColumn($this->lng->txt(""), "", "1");
        $this->addColumn($this->lng->txt("type"));
        $this->addColumn($this->lng->txt("file"));
        $this->addColumn($this->lng->txt("size"));
        $this->addColumn($this->lng->txt("date"));


        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.export_table_row.html", "Services/Export");

        $this->addMultiCommand("downloadEvasysFile", $lng->txt("download"));
        $this->addMultiCommand("deleteEvasysFile", $lng->txt("delete"));

        $this->setData($this->getDataToTable());
    }

    /**
     * Add custom column
     *
     * @param
     * @return
     */
    function addCustomColumn($a_txt, $a_obj, $a_func) {
        $this->addColumn($a_txt);
        $this->custom_columns[] = array("txt" => $a_txt,
            "obj" => $a_obj,
            "func" => $a_func);
    }

    /**
     * Add custom multi command
     *
     * @param
     * @return
     */
    function addCustomMultiCommand($a_txt, $a_cmd) {
        $this->addMultiCommand($a_cmd, $a_txt);
    }

    /**
     * Get custom columns
     *
     * @param
     * @return
     */
    function getCustomColumns() {
        return $this->custom_columns;
    }

    /**
     * Get export files
     */
    function getExportFiles() {
        $types = array();
        $types[] = "csv";
        include_once("./Services/Export/classes/class.ilExport.php");
        $files = ilExport::_getExportFiles($this->obj->getId(), $types, $this->obj->getType());
        return $files;
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set) {
        global $lng;

        foreach ($this->getCustomColumns() as $c) {
            $this->tpl->setCurrentBlock("custom");
            $this->tpl->setVariable("VAL_CUSTOM", $c["obj"]->$c["func"]($a_set["type"], $a_set["file"]) . " ");
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("VAL_ID", $a_set["type"] . ":" . $a_set["file"]);
        $this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
        $this->tpl->setVariable("VAL_FILE", $a_set["file"]);
        $this->tpl->setVariable("VAL_SIZE", $a_set["size"]);
        $this->tpl->setVariable("VAL_DATE", $a_set["timestamp"]);
    }

    function getDataToTable() {
        $evasys = new ilObjEvaluationManagerExportEvaSys($this->obj->getId());
        return $evasys->getDataFromDirectory();
    }

}

?>
