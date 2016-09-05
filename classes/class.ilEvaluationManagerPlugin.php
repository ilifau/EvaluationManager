<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * Evaluation Manager repository object plugin
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 * @version $Id$
 *
 */
class ilEvaluationManagerPlugin extends ilRepositoryObjectPlugin {

    function getPluginName() {
        return "EvaluationManager";
    }

    protected function uninstallCustom()
    {
        global $ilDB;

        $ilDB->dropTable('rep_robj_xema_eval');
        $ilDB->dropTable('rep_robj_xema_assign');
        $ilDB->dropTable('rep_robj_xema_key');
        $ilDB->dropTable('rep_robj_xema_settings');
    }

}
?>