<?php

/**
 * fim
 * Institut für Lern-Innovation
 * Friedrich-Alexander-Universität
 * Erlangen-Nürnberg
 * Germany
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */
include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Evasys export class for the Evaluation Manager plugin for ILIAS.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjEvaluationManagerExportEvaSys {

    public $obj_id;
    private $type;
    private $eval_ids;
    private $document_name;

    /**
     * Constructor
     * 
     * @param integer 	$obj_id is the object id of the evaluation manager  
     * @param string 	$type is the type of entries to export
     * @param array 	$eval_ids is an array with the eval_id of the entries to export
     * @param string    $document_name is the name of the document to be created.
     */
    public function __construct($obj_id, $type = "", $eval_ids = "", $document_name = "") {
        $this->obj_id = $obj_id;
        $this->setExportType($type);
        $this->setEvalIds($eval_ids);
        $this->setDocumentName($document_name);
    }

    /*     * ***************************************************
     * **************** GETTERS, AND SETTERS ******************
     * **************************************************** */

    public function getExportType() {
        return $this->type;
    }

    public function setExportType($var) {
        $this->type = $var;
    }

    public function getEvalIds() {
        return $this->eval_ids;
    }

    public function setEvalIds($var) {
        $this->eval_ids = $var;
    }

    public function getDocumentName() {
        return $this->document_name;
    }

    public function setDocumentName($var) {
        $this->document_name = $var;
    }

    /*     * ***************************************************
     * **************** CREATION OF FILES ******************
     * **************************************************** */

    /**
     * This class creates two csv documents with the data to export it to evaSys
     * and stores it in the server to be downloaded from exportEvasys()
     */
    public function init() {
        global $ilErr, $lng;
        //Structure file
        $structure = $this->getStructure($this->getEvalIds(), $this->getExportType(), $this->obj_id);
        if (is_array($structure)) {
			ilUtil::sendFailure(sprintf($lng->txt($structure[0]),$structure[1]), false);
            return;
        }

        //Participants file
        $participants = $this->getParticipants($this->getEvalIds(), $this->getExportType(), $this->obj_id);

        //Create exporter
        require_once("./Services/Export/classes/class.ilExport.php");
        ilExport::_createExportDirectory($this->obj_id);
        $export_dir = ilExport::_getExportDirectory($this->obj_id);

        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/EvaluationManager/classes/class.ilEvaluationManagerExporter.php');
        $exporter = new ilEvaluationManagerExporter();
        $exporter->setExportDirectories($export_dir, $export_dir);

        //Name of the file
        if ($this->getExportType() == "lec") {
            $type_doc = "Lehrveranstaltungen";
        } elseif ($this->getExportType() == "mod") {
            $type_doc = "Module";
        }

        $time = getDate();
        if (!$this->getDocumentName()) {
			$filename1 = sprintf('%04d-%02d-%02d_%02d-%02d-%02d_'.$type_doc.'-Daten.csv',
				$time['year'], $time['mon'], $time['mday'], $time['hours'], $time['minutes'], $time['seconds']);
			$filename2 = sprintf('%04d-%02d-%02d_%02d-%02d-%02d_'.$type_doc.'-Teilnehmer.csv',
				$time['year'], $time['mon'], $time['mday'], $time['hours'], $time['minutes'], $time['seconds']);
	    } else {
			$filename1 = sprintf('%04d-%02d-%02d_'.$this->getDocumentName().'_'.$type_doc.'-Daten.csv',
				$time['year'], $time['mon'], $time['mday'], $time['hours'], $time['minutes'], $time['seconds']);
			$filename2 = sprintf('%04d-%02d-%02d_'.$this->getDocumentName().'_'.$type_doc.'-Teilnehmer.csv',
				$time['year'], $time['mon'], $time['mday'], $time['hours'], $time['minutes'], $time['seconds']);
        }

        //Create structure file
        $file1 = fopen($export_dir . "/" . $filename1, "w");
        fwrite($file1, $structure);
        fclose($file1);
        //Create participants file
        $file2 = fopen($export_dir . "/" . $filename2, "w");
        fwrite($file2, $participants);
        fclose($file2);
    }

    /**
     * Get the text for the structure file to export to evaSys
     * @param array $ids is an array with the eval_id of the entries to export
     * @param string $type  Is the type of the entries
     * @return string|null the text for the structure file.
     */
    public function getStructure($ids, $type) {
        $text = "";
        if ($type == "lec") {
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $lecture = $this->getLectureDataFromEvalId($id);
                    //array of assignments of a lecture
                    $lecture_assignments = ilObjLectureAssignment::_readLectureAssignments($id);

					//merged emails of all assigned objects
					$email_merge = array();
                    foreach ($lecture_assignments as $ref_id) {
                        if (ilObjEvaluationManagerExportEvaSys::_isCourseOrGroup($ref_id->getIliasObj())) {
							if (is_array($emails = $this->getEmailsOfParticipants($ref_id)))
							{
								$email_merge = array_merge($email_merge, $emails);
							}
                        }
                    }
					$email_merge = array_unique($email_merge);

                    $text .= utf8_decode("Dozent") . ";";
                    $text .= utf8_decode($lecture->getDocSalutation()) . ";";
                    $text .= utf8_decode($lecture->getDocTitle()) . ";";
                    $text .= utf8_decode($lecture->getDocFirstname()) . ";";
                    $text .= utf8_decode($lecture->getDocLastname()) . ";";
                    $text .= utf8_decode($lecture->getDocEmail()) . ";";
                    $text .= utf8_decode($lecture->getEvalName()) . ";";
                    $text .= utf8_decode($lecture->getEvalKey()) . ";";
                    $text .= ";"; // empty field for location
                    $text .= ";"; // empty field for study course
                    $text .= utf8_decode("LV") . ";";
                    $text .= utf8_decode(count($email_merge)) . ";";
                    $text .= "\r\n";
                    if (!$lecture->getDocLastname()) {
                        return array("rep_robj_xema_msg_no_lastname_in_lecture_or_module",$lecture->getEvalName());
                    }
                }
            }
        } elseif ($type == "mod") {
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $module = $this->getModuleDataFromEvalId($id);
                    //array of assignments of a lecture
                    $module_assignments = ilObjModuleAssignment::_readModuleAssignments($id);

					//merged emails of all assigned objects
					$email_merge = array();
                    foreach ($module_assignments as $ref_id) {
                        if (ilObjEvaluationManagerExportEvaSys::_isCourseOrGroup($ref_id->getIliasObj())) {
							if (is_array($emails = $this->getEmailsOfParticipants($ref_id)))
							{
								$email_merge = array_merge($email_merge, $emails);
							}
                        }
                    }
					$email_merge = array_unique($email_merge);

                    $text .= utf8_decode("Dozent") . ";";
                    $text .= utf8_decode($module->getDocSalutation()) . ";";
                    $text .= utf8_decode($module->getDocTitle()) . ";";
                    $text .= utf8_decode($module->getDocFirstname()) . ";";
                    $text .= utf8_decode($module->getDocLastname()) . ";";
                    $text .= utf8_decode($module->getDocEmail()) . ";";
                    $text .= utf8_decode($module->getEvalName()) . ";";
                    $text .= utf8_decode($module->getEvalKey()) . ";";
                    $text .= ";"; // empty field for location
                    $text .= ";"; // empty field for study course
                    $text .= utf8_decode("Modul") . ";";
                    $text .= utf8_decode(count($email_merge)) . ";";
                    $text .= "\r\n";
                    if (!$module->getDocLastname()) {
						return array("msg_no_lastname_in_lecture_or_module",$module->getEvalName());
					}
                }
            }
        } else {
            return null;
        }
        return $text;
    }

    /**
     * Get the participants file for the evasys export
     * @param array $ids is an array with the eval_id of the entries to export
     * @param string $type  Is the type of the entries
     * @return string|null the text for the participants file.
     */
    public function getParticipants($ids, $a_type) {
        $text = "";
        if ($a_type == "lec") {
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $lecture = $this->getLectureDataFromEvalId($id);
                    //array of assignments of a lecture
                    $lecture_assignments = ilObjLectureAssignment::_readLectureAssignments($id);
					//merged emails of all assigned objects
					$email_merge = array();
                    if (is_array($lecture_assignments)) {
                        foreach ($lecture_assignments as $ref_id) {
							if (is_array($emails = $this->getEmailsOfParticipants($ref_id)))
							{
								$email_merge = array_merge($email_merge, $emails);
							}
                        }
                    }
					foreach ($email_merge as $email) {
						$text .= utf8_decode($lecture->getEvalKey()) . ";";
						$text .= utf8_decode($email) . ";";
						$text .= "\r\n";
					}
                }
            }
        } elseif ($a_type == "mod") {
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $module = $this->getModuleDataFromEvalId($id);
                    //array of assignments of a lecture
                    $module_assignments = ilObjModuleAssignment::_readModuleAssignments($id);
					//merged emails of all assigned objects
					$email_merge = array();
                    if (is_array($module_assignments)) {
                        foreach ($module_assignments as $ref_id) {
							if (is_array($emails = $this->getEmailsOfParticipants($ref_id)))
							{
								$email_merge = array_merge($email_merge, $emails);
							}
                        }
                    }
					$email_merge = array_unique($email_merge);
					foreach ($email_merge as $email) {
						$text .= utf8_decode($module->getEvalKey()) . ";";
						$text .= utf8_decode($email) . ";";
						$text .= "\r\n";
					}
                }
            }
        } else {
            return null;
        }
        return $text;
    }

    /*     * ***************************************************
     * **************** SUPPORT METHODS ******************
     * **************************************************** */

    /**
     * Gets the data of the lecture from the DB and creates a lecture object
     * @param integer $a_eval_id eval_id of the lecture to get from DB
     * @return ilObjLecture The lecture with the $a_eval_id
     */
    public function getLectureDataFromEvalId($a_eval_id) {
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_eval WHERE eval_id = " . $ilDB->quote($a_eval_id, 'integer');
        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);
        $lecture = new ilObjLecture($data);
        return $lecture;
    }

    /**
     * Gets the data of the module from the DB and creates a module object
     * @param integer $a_eval_id eval_id of the module to get from DB
     * @return ilObjModule The module with the $a_eval_id
     */
    public function getModuleDataFromEvalId($a_eval_id) {
        global $ilDB;
        $query = "SELECT * FROM rep_robj_xema_eval WHERE eval_id = " . $ilDB->quote($a_eval_id, 'integer');
        $result = $ilDB->query($query);
        $data = $ilDB->fetchAssoc($result);
        $module = new ilObjModule($data);
        return $module;
    }

    /**
     * Determines if assigment is a course, a group or false if not.
     * @param integer $ref_id The ref_id of the assignment
     * @return integer|boolean return the obj_id of the assignment or false if doesn't exist.
     */
    public static function _isCourseOrGroup($ref_id) {
        global $ilDB;

		$type = ilObject::_lookupType($ref_id, true);

		if ($type == 'crs' or $type == 'grp')
		{
			return ilObject::_lookupObjId($ref_id);
		}
		else
		{
			return false;
		}

        //Course comprobation
        $query = "SELECT c.obj_id FROM crs_settings c, object_reference o WHERE o.ref_id = " . $ilDB->quote($ref_id, 'integer') . " AND o.obj_id = c.obj_id";
        $resultc = $ilDB->query($query);
        $datac = $ilDB->fetchAssoc($resultc);
        if ($datac) {
            return $datac["obj_id"];
        }
        //Group comprobation
        $query = "SELECT g.obj_id FROM grp_settings g, object_reference o WHERE o.ref_id = " . $ilDB->quote($ref_id, 'integer') . " AND o.obj_id = g.obj_id";
        $resultg = $ilDB->query($query);
        $datag = $ilDB->fetchAssoc($resultg);
        if ($datag) {
            return $datag["obj_id"];
        }
        return false;
    }

    /**
     * Look for the emails of the participants in a course or group and return an array with them.
     * @param integer $ref_id The reference id of the assignment
     * @return array Array with the emails of the participants in a course or group
     */
    public function getEmailsOfParticipants($ref_id) {
        include_once('./Services/Membership/classes/class.ilParticipants.php');
         $participants = array();
        //Determines if is course or group
        $obj_id = ilObjEvaluationManagerExportEvaSys::_isCourseOrGroup($ref_id->getIliasObj());
        if (!$obj_id) {
            return 0;
        }
        $part_obj = ilParticipants::getInstanceByObjId($obj_id);

        //Get members
        $members = $part_obj->getMembers();
        //Get member emails
        foreach ($members as $usr_id) {
            $participants[] = ilObjUser::_lookupEmail($usr_id);
        }
        return array_unique($participants);
    }

    /**
     * Get the data from directory to show it in the evasys export table.
     * @return array    array of files data
     */
    public function getDataFromDirectory() {
        require_once("./Services/Export/classes/class.ilExport.php");
        ilExport::_createExportDirectory($this->obj_id);
        $export_dir = ilExport::_getExportDirectory($this->obj_id);
        // quit if import dir not available
        if (@is_dir($export_dir) and is_writeable($export_dir)) {
            // open directory
            $h_dir = dir($export_dir);
            // get files and save it in the array
			$file = array();
            while ($entry = $h_dir->read()) {
                if ($entry != "." and $entry != "..") {
                    $ts = substr($entry, 0, 10);
					$file[$entry] = array("type" => "csv", "file" => $entry,
                        "size" => filesize($export_dir . "/" . $entry),
                        "timestamp" => $ts);
                }
            }
			ksort($file);
			$return = array();
			foreach (array_reverse($file) as $data) {
				$return[] = $data;
			}

			// close import directory
            $h_dir->close();
        }
        // sort files
        return $return;
    }

}

?>