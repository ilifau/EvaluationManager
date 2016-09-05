<?php

/**
 * fim
 * Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE
 */

include_once './Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Evaluation Manager object plugin.
 *
 * @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
 * @author Jesus Copado <jesus.copado@fim.uni-erlangen.de>
 * @version $Id$
 */

class ilObjEvaluationManagerListGUI extends ilObjectPluginListGUI
{
	
	/**
	* Init type
	*/
	public function initType()
	{
		$this->setType("xema");
	}
	
	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass()
	{
		return "ilObjEvaluationManagerGUI";
	}
	
	/**
	* Get commands
	*/
	public function initCommands()
	{
		return array
		(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"default" => true),
			array(
				"permission" => "read",
				"cmd" => "showLectures",
				"txt" => $this->txt("showLectures"),
				"default" => false),
			array(
				"permission" => "read",
				"cmd" => "showModules",
				"txt" => $this->txt("showModules"),
				"default" => false)
		);
	}

	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilUser;

		$props = array();
		
		$this->plugin->includeClass('class.ilObjEvaluationManagerAccess.php');

		return $props;
	}
}
?>