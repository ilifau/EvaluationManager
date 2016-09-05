<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

/**
 * Access/Condition checking for Evaluation Manager object
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 * @version $Id$
 */

class ilObjEvaluationManagerAccess extends ilObjectPluginAccess
{

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "visible":
			case "read":
				if (!self::checkOnline($a_obj_id) &&
					!$ilAccess->checkAccessOfUser($a_user_id, "read", "", $a_ref_id))
				{
					return false;
				}
				break;
		}

		return true;
	}
	
	/**
	* Check online status of example object
	*/
	static function checkOnline($a_id)
	{
		return false;
	}
}

?>
