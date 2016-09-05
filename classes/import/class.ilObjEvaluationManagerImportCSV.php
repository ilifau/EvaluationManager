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
 * Import CSV class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjEvaluationManagerImportCSV {

    private $type;
    private $csv_delimiter;
    private $csv_keyword_delimiter;

    /**
     * Constructor
     *
     * @param file 	$csv_file is the file to get the data.
     * @param text 	$type is the type of entries to create.
     * @param text 	$csv_delimiter is the delimiter of the csv file.
     * @access	public
     */
    public function __construct($csv_file, $type, $csv_delimiter, $csv_keyword_delimiter) {
        if (is_file($csv_file) AND $csv_delimiter != null) {
            $this->setImportType($type);
            $this->setCSVDelimiter($csv_delimiter);
            $this->setCSVKeywordDelimiter($csv_keyword_delimiter);
            return true;
        } else {
            return false;
        }
    }

    /*     * ***************************************************
     * **************** GETTERS AND SETTERS ******************
     * **************************************************** */

    public function getImportType() {
        return $this->type;
    }

    public function setImportType($var) {
        $this->type = $var;
    }

    public function getCSVDelimiter() {
        return $this->csv_delimiter;
    }

    public function setCSVDelimiter($var) {
        $this->csv_delimiter = $var;
    }

    public function getCSVKeywordDelimiter() {
        return $this->csv_keyword_delimiter;
    }

    public function setCSVKeywordDelimiter($var) {
        $this->csv_keyword_delimiter = $var;
    }

    /**
     * Read CSV
     * 
     * This function read a CSV file and present the data as array, each row is a lecture or a module and the special field type
     * determines which kind of entries are into the csv, if the type of entry doesn't match with the type required,
     * returns false
     * 
     * @param file 	$csv_file is the file to get the data.
     * @param text 	$type is the type of entries to create.
     * @return	array	$input is an array with the data extracted
     * @return	boolean	false if the type of entry doesn't match with the type required, 
     * @access	public
     * 
     */
    public function readCSV($csv_file, $type) {

        //This array contain  the data of the cvs as number rows and an extra field "type" with the type of the input.
        $input = array();

        if (is_file($csv_file)) {
            $fp = fopen($csv_file, "r");

            //Getting titles
            $titles = fgetcsv($fp, 0, $this->getCSVDelimiter());
            $titles_array = array();
            $is_module = false;

            for ($i = 0; $i < sizeof($titles); $i++) {
                if (strtolower($titles[$i]) == "semester") {
                    $titles_array[$i] = "eval_semester";
                    $semester = $i;
                }
                if (strtolower($titles[$i]) == "funktion") {
                    $titles_array[$i] = "doc_function";
                }
                if (strtolower($titles[$i]) == "anrede") {
                    $titles_array[$i] = "doc_salutation";
                }
                if (strtolower($titles[$i]) == "titel") {
                    $titles_array[$i] = "doc_title";
                }
                if (strtolower($titles[$i]) == "vorname") {
                    $titles_array[$i] = "doc_firstname";
                }
                if (strtolower($titles[$i]) == "nachname") {
                    $titles_array[$i] = "doc_lastname";
                }
                if (strtolower($titles[$i]) == "e-mail") {
                    $titles_array[$i] = "doc_email";
                }
                if (strtolower($titles[$i]) == "lv-kennung") {
                    $titles_array[$i] = "eval_key";
                    $input["type"] = "lec";
                    $key = $i;
                } elseif (strtolower($titles[$i]) == "modul-kennung") {
                    $titles_array[$i] = "eval_key";
                    $input["type"] = "mod";
                    $key = $i;
                    $is_module = true;
                    for ($j = 0; $j < sizeof($titles); $j++) {
                        if (strtolower($titles[$j]) == "lv-name") {
                            $titles_array[$j] = "lecture_name";
                        }
                        if (strtolower($titles[$j]) == "lv-dozentin") {
                            $titles_array[$j] = "lecturer_name";
                        }
                    }
                }
                if (strtolower($titles[$i]) == "modul-name") {
                    $titles_array[$i] = "eval_name";
                    $eval_name = $i;
                    $is_module = true;
                } elseif (strtolower($titles[$i]) == "lv-name" and !$is_module) {
                    $titles_array[$i] = "eval_name";
                    $eval_name = $i;
                }
                if (strtolower($titles[$i]) == "art") {
                    $titles_array[$i] = $input["type"];
                }
                if (strtolower($titles[$i]) == "fragebogen") {
                    $titles_array[$i] = "eval_questionnaire";
                }
                if (strtolower($titles[$i]) == "bemerkungen") {
                    $titles_array[$i] = "eval_remarks";
                }
                if (strtolower($titles[$i]) == "studon-objekt") {
                    $titles_array[$i] = "ilias_obj";
                    $studOn_obj = $i;
                }
                if (strtolower($titles[$i]) == "stichwort" or strtolower($titles[$i]) == "stichworte") {
                    $titles_array[$i] = "keywords";
                    $keywords = $i;
                }
            }

            /*
             * Comprobation of matching types.
             */
            if ($type != $input["type"]) {
                return "ERROR_WRONG_TYPE";
            }

            $row = 0;
            while ($data = fgetcsv($fp, 0, $this->getCSVDelimiter())) {
                
                //Constraint for blank rows if Lecture id field is blank
                if ($data[$semester] !== "" and $data[$key] !== "") {
                    

                    for ($i = 0; $i < sizeof($data); $i++) {
                        $input[$row][$titles_array[$i]] = utf8_encode($data[$i]);
                    }
                    //Debug ilias_obj
                    $ilias_obj_not_debugged = utf8_encode($data[$studOn_obj]);
                    $ilias_obj_debugged = $this->extractReference($ilias_obj_not_debugged);
                    //Show warning because the extract function cannot extract a reference
                    if ($ilias_obj_debugged == false) {
                        $input["ERROR"] = "ERROR_EXTRACTING_REFERENCE";
                    }
                    $input[$row]["ilias_obj"] = $ilias_obj_debugged;

                    //ref_id of the evaluation manager
                    $ref_id = (int) $_GET["ref_id"];
                    $input[$row]["em_ref_id"] = $ref_id;

                    //Keyword
                    if ($data[$keywords]) {
                        $input[$row]["keywords"] = $this->getKeywords(utf8_encode($data[$keywords]));
                    } else {
                        $input[$row]["keywords"] = array();
                    }

                    //eval name
                    if ($data[$eval_name]) {
                        $input[$row]["eval_name"] = utf8_encode($data[$eval_name]);
                    }

                    $row++;
                } else {
                    return "ERROR_SEMESTER_OR_KEY_NOT_FOUND";
                }
            }
            fclose($fp);
        } else {
            
        }
        return $input;
    }

    /**
     * Get Keywords
     * 
     * This function separates the keywords by the csv_keyword_delimiter and creates an array
     * with them.
     * 
     * @param text  $data is a string with the keywords separated by the csv_keyword_delimiter.
     * @return	array	$array of keywords
     * @access	public
     * 
     */
    public function getKeywords($data) {
        $array = explode(",", $data);
        for ($i = 0; $i < count($array); $i++) {
            $array[$i] = trim($array[$i]);
        }
        sort($array);
        return $array;
    }

    /**
     * This function extracts the object reference from a string
     * 
     * The string must be formed like "...crs1234.html" or
     * The string must be formed like "crs1234" or
     * The string must be formed like "crs_1234" or
     * The string must be formed like "1234" or
     * The string must be formed like "ref_id=1234.html" 
     * 
     * @param 	text    $field content
     * @return	int	ref_id	or false
     * @access	public

     */
    function extractReference($field) {
        if (preg_match('/^.*([a-z]+)([0-9]+)\.html$/', $field, $matches)) {//crs?ref_id.html
            return (int) $matches[2];
        } elseif (preg_match('/^[0-9]*$/', $field, $matches)) {//just the ref_id
            return (int) $matches[0];
        } elseif (preg_match("#ref_id=([0-9]+)#is", $field, $matches)) {//ref_id=234545
            return (int) $matches[1];
        } elseif (preg_match("#crs_([0-9]+)#is", $field, $matches)) {//crs254345435
            return (int) $matches[1];
        } elseif (preg_match("#crs([0-9]+)#is", $field, $matches)) {//crs254345435
            return (int) $matches[1];
        } elseif (preg_match("#grp_([0-9]+)#is", $field, $matches)) {//grp254345435
            return (int) $matches[1];
        } elseif (preg_match("#grp([0-9]+)#is", $field, $matches)) {//grp254345435
            return (int) $matches[1];
        } elseif (preg_match("#_([0-9]+)#is", $field, $matches)) {//grp254345435
            return (int) $matches[1];
        } else {
            return false;
        }
    }

}

?>
