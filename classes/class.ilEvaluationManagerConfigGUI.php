<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * Flashcards configuration user interface class
 *
 * @author Fred Neumann <fred.neumann@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilEvaluationManagerConfigGUI: ilPropertyFormGUI, ilPageObjectGUI
 */
class ilEvaluationManagerConfigGUI extends ilPluginConfigGUI {

    /**
     * Handles all commmands, default is "configure"
     */
    function performCommand($cmd) {

        switch ($cmd) {
            case "configure":
                 $this->$cmd();
                break;
        }
    }

    /**
     * Configure screen
     */
    function configure() {
		global $tpl, $lng;

		ilUtil::sendInfo($lng->txt('rep_robj_xema_nothing_to_configure'));
        $tpl->setContent("");
    }

}

?>
