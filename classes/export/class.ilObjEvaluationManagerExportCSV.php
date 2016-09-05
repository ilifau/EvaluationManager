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
 * Export CSV class for Evaluation Manager repository object.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 *
 * $Id$
 */
class ilObjEvaluationManagerExportCSV {

    /**
     * writeCSV Deletes the entries of a semester and their assignments.
     * 
     * @param array 	$array_of_entries array with the entries to export
     * @param string 	$type type of export.
     * @return file Returns a csv object.
     */
    public static function _writeCSVofEntries($array_of_entries, $type) {
        require_once('./Services/Link/classes/class.ilLink.php');

        if ($type == "lec") {

            $text = "Semester;Funktion;Anrede;Titel;Vorname;Nachname;E-Mail;LV-Name;LV-Kennung;Art;Fragebogen;Bemerkungen;StudOn-Objekt;Stichworte;";
            $text .= "\r\n";
            if (is_array($array_of_entries)) {
                foreach ($array_of_entries as $lecture) {
                    $assignments = $lecture->getAssignments();
                    if (is_array($assignments) AND sizeof($assignments)) {
                        foreach ($assignments as $assignment) {
                            $text .= utf8_decode($lecture->getEvalSemester()) . ";";
                            $text .= utf8_decode($lecture->getDocFunction()) . ";";
                            $text .= utf8_decode($lecture->getDocSalutation()) . ";";
                            $text .= utf8_decode($lecture->getDocTitle()) . ";";
                            $text .= utf8_decode($lecture->getDocFirstname()) . ";";
                            $text .= utf8_decode($lecture->getDocLastname()) . ";";
                            $text .= utf8_decode($lecture->getDocEmail()) . ";";
                            $text .= utf8_decode($lecture->getEvalName()) . ";";
                            $text .= utf8_decode($lecture->getEvalKey()) . ";";
                            $text .= utf8_decode($lecture->getEvalType()) . ";";
                            $text .= utf8_decode($lecture->getEvalQuestionnaire()) . ";";
                            $text .= utf8_decode($lecture->getEvalRemarks()) . ";";
                            $text .= utf8_decode(ilLink::_getStaticLink($assignment->getIliasObj())) . ";";
                            $keywords = $lecture->getKeywords();
                            if (is_array($keywords)) {
                                for ($i = 0; $i < sizeof($keywords); $i++) {
                                    $text .= utf8_decode($keywords[$i]->getKeyword());
                                    if ($i != (sizeof($keywords) - 1)) {
                                        $text .= ",";
                                    }
                                }
                            }
                            $text .= ";";
                            $text .= "\r\n";
                        }
                    } else {
                        $text .= utf8_decode($lecture->getEvalSemester()) . ";";
                        $text .= utf8_decode($lecture->getDocFunction()) . ";";
                        $text .= utf8_decode($lecture->getDocSalutation()) . ";";
                        $text .= utf8_decode($lecture->getDocTitle()) . ";";
                        $text .= utf8_decode($lecture->getDocFirstname()) . ";";
                        $text .= utf8_decode($lecture->getDocLastname()) . ";";
                        $text .= utf8_decode($lecture->getDocEmail()) . ";";
                        $text .= utf8_decode($lecture->getEvalName()) . ";";
                        $text .= utf8_decode($lecture->getEvalKey()) . ";";
                        $text .= utf8_decode($lecture->getEvalType()) . ";";
                        $text .= utf8_decode($lecture->getEvalQuestionnaire()) . ";";
                        $text .= utf8_decode($lecture->getEvalRemarks()) . ";";
                        $text .= ";";
                        $keywords = $lecture->getKeywords();
                        if (is_array($keywords)) {
                            for ($i = 0; $i < sizeof($keywords); $i++) {
                                $text .= utf8_decode($keywords[$i]->getKeyword());
                                if ($i != (sizeof($keywords) - 1)) {
                                    $text .= ",";
                                }
                            }
                        }
                        $text .= ";";
                        $text .= "\r\n";
                    }
                }
            }
        } elseif ($type == "mod") {

            $text = "Semester;Funktion;Anrede;Titel;Vorname;Nachname;E-Mail;Modul-Name;Modul-Kennung;Art;Fragebogen;Bemerkungen;StudOn-Objekt;Stichworte;LV-Name;LV-Dozentin";
            $text .= "\r\n";
            if (is_array($array_of_entries)) {
                foreach ($array_of_entries as $module) {
                    $assignments = $module->getAssignments();
                    if (is_array($assignments) AND sizeof($assignments)) {
                        foreach ($assignments as $assignment) {
                            $text .= utf8_decode($module->getEvalSemester()) . ";";
                            $text .= utf8_decode($module->getDocFunction()) . ";";
                            $text .= utf8_decode($module->getDocSalutation()) . ";";
                            $text .= utf8_decode($module->getDocTitle()) . ";";
                            $text .= utf8_decode($module->getDocFirstname()) . ";";
                            $text .= utf8_decode($module->getDocLastname()) . ";";
                            $text .= utf8_decode($module->getDocEmail()) . ";";
                            $text .= utf8_decode($module->getEvalName()) . ";";
                            $text .= utf8_decode($module->getEvalKey()) . ";";
                            $text .= utf8_decode($module->getEvalType()) . ";";
                            $text .= utf8_decode($module->getEvalQuestionnaire()) . ";";
                            $text .= utf8_decode($module->getEvalRemarks()) . ";";
                            $text .= utf8_decode(ilLink::_getStaticLink($assignment->getIliasObj())) . ";";
                            $keywords = $module->getKeywords();
                            if (is_array($keywords)) {
                                for ($i = 0; $i < sizeof($keywords); $i++) {
                                    $text .= utf8_decode($keywords[$i]->getKeyword());
                                    if ($i != (sizeof($keywords) - 1)) {
                                        $text .= ",";
                                    }
                                }
                            }
                            $text .= ";";
                            $text .= utf8_decode($assignment->getLectureName()) . ";";
                            $text .= utf8_decode($assignment->getLecturerName()) . ";";
                            $text .= "\r\n";
                        }
                    } else {
                        $text .= utf8_decode($module->getEvalSemester()) . ";";
                            $text .= utf8_decode($module->getDocFunction()) . ";";
                            $text .= utf8_decode($module->getDocSalutation()) . ";";
                            $text .= utf8_decode($module->getDocTitle()) . ";";
                            $text .= utf8_decode($module->getDocFirstname()) . ";";
                            $text .= utf8_decode($module->getDocLastname()) . ";";
                            $text .= utf8_decode($module->getDocEmail()) . ";";
                            $text .= utf8_decode($module->getEvalName()) . ";";
                            $text .= utf8_decode($module->getEvalKey()) . ";";
                            $text .= utf8_decode($module->getEvalType()) . ";";
                            $text .= utf8_decode($module->getEvalQuestionnaire()) . ";";
                            $text .= utf8_decode($module->getEvalRemarks()) . ";";
                            $text .= ";";
                            $keywords = $module->getKeywords();
                            if (is_array($keywords)) {
                                for ($i = 0; $i < sizeof($keywords); $i++) {
                                    $text .= utf8_decode($keywords[$i]->getKeyword());
                                    if ($i != (sizeof($keywords) - 1)) {
                                        $text .= ",";
                                    }
                                }
                            }
                            $text .= ";";
                            $text .= ";";
                            $text .= ";";
                            $text .= "\r\n";
                    }
                }
            }
        } else {
            return null;
        }
        ilUtil::deliverData($text, "export.csv", "text/csv");
    }

    /**
     * Create and deliver a CSV with the marked courses or groups.
     * @global type $lng
     * @param type $array_of_assignments
     * @return file
     */
    public static function _writeCSVofMarkedAssignments($array_of_assignments) {
        global $lng;
        $text = "ILIAS ref_id;Titel;Link;Kontakt-Name;Kontakt-E-Mail;Evaluation;";
        $text .= "\r\n";
        if (is_array($array_of_assignments)) {
            foreach ($array_of_assignments as $assignment) {
                $evaluations = $assignment["evaluation"];
                if (sizeof($evaluations)) {
                    foreach ($evaluations as $evaluation) {
                        $text .= utf8_decode($assignment["ref_id"]) . ";";
                        $text .= utf8_decode($assignment["title"]) . ";";
                        $text .= utf8_decode($assignment["link"]) . ";";
                        $text .= utf8_decode($assignment["contact_name"]) . ";";
                        $text .= utf8_decode($assignment["contact_email"]) . ";";
                        $text .= utf8_decode(str_replace(array($lng->txt("rep_robj_xema_lecture_short_label"), $lng->txt("module_short_label")), "", $evaluation)) . ";";
                        $text .= "\r\n";
                    }
                } else {
                    $text .= utf8_decode($assignment["ref_id"]) . ";";
                    $text .= utf8_decode($assignment["title"]) . ";";
                    $text .= utf8_decode($assignment["link"]) . ";";
                    $text .= utf8_decode($assignment["contact_name"]) . ";";
                    $text .= utf8_decode($assignment["contact_email"]) . ";";
                    $text .= ";";
                    $text .= "\r\n";
                }
            }
        } else {
            return null;
        }
        ilUtil::deliverData($text, "export.csv", "text/csv");
    }

}

?>
