<?php
/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id: view.html.php 2979 2011-11-10 13:50:14Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C) 2008-2009 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class for the component frontend
 *
 * @static
 */
include_once(JEV_ADMINPATH . "/views/icalevent/view.html.php");

class ICalEventViewIcalevent extends AdminIcaleventViewIcalevent
{

	var $jevlayout = null;

	function __construct($config = array())
	{
		include_once(JPATH_ADMINISTRATOR . '/' . "includes" . '/' . "toolbar.php");
		parent::__construct($config);

		// used only for helper functions
		$this->jevlayout = "default";
		$this->addHelperPath(realpath(dirname(__FILE__) . "/../default/helpers"));
		$this->addHelperPath(JPATH_BASE . '/' . 'templates' . '/' . JFactory::getApplication()->getTemplate() . '/' . 'html' . '/' . JEV_COM_COMPONENT . '/' . "helpers");

	}

	function edit($tpl = null)
	{
		$document = & JFactory::getDocument();
		include(JEV_ADMINLIBS . "/editStrings.php");
		$document->addScriptDeclaration($editStrings);

		JEVHelper::script('editical.js', 'components/' . JEV_COM_COMPONENT . '/assets/js/');
                                    JEVHelper::script('JevStdRequiredFields.js', 'components/' . JEV_COM_COMPONENT . '/assets/js/');
		//JEVHelper::script('toolbarfix.js','components/'.JEV_COM_COMPONENT.'/assets/js/');

		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('EDIT_ICAL_EVENT'), 'jevents');
		
		// reset the title because JToolbar messes is up in Joomla 3.0 in the frontend !!
		$document->setTitle(JText::_('EDIT_ICAL_EVENT'));

		$bar = & JToolBar::getInstance('toolbar');
		if ($this->id > 0)
		{
			if ($this->editCopy)
			{
				$this->toolbarConfirmButton("icalevent.save", JText::_("save_copy_warning"), 'save', 'save', 'Save', false);
				if (JEVHelper::isEventEditor())
					$this->toolbarConfirmButton("icalevent.apply", JText::_("save_copy_warning"), 'apply', 'apply', 'jev_Apply', false);
				//$this->toolbarConfirmButton("icalevent.savenew", JText::_("save_copy_warning"), 'save', 'save', 'JEV_Save_New', false);
			}
			else
			{
				$this->toolbarConfirmButton("icalevent.save", JText::_("save_icalevent_warning"), 'save', 'save', 'Save', false);
				if (JEVHelper::isEventEditor())
					$this->toolbarConfirmButton("icalevent.apply", JText::_("save_icalevent_warning"), 'apply', 'apply', 'jev_Apply', false);
				//$this->toolbarConfirmButton("icalevent.savenew", JText::_("save_icalevent_warning"), 'save', 'save', 'JEV_Save_New', false);
			}
		}
		else
		{
			$this->toolbarButton("icalevent.save", 'save', 'save', 'Save', false);
			if (JEVHelper::isEventEditor())
				$this->toolbarButton("icalevent.apply", 'apply', 'apply', 'Apply', false);
			//JToolBarHelper::save('icalevent.savenew', "JEV_Save_New");
		}

		$params = JComponentHelper::getParams(JEV_COM_COMPONENT);
		if ($params->get("editpopup", 0))
		{
			$document->addStyleDeclaration("div#toolbar-box{margin:10px 10px 0px 10px;} div#jevents {margin:0px 10px 10px 10px;} ");
			$this->toolbarButton("icalevent.close", 'cancel', 'cancel', 'Cancel', false);
			JRequest::setVar('tmpl', 'component'); //force the component template
		}
		else
		{
			if ($this->id > 0)
			{
				$this->toolbarButton("icalevent.detail", 'cancel', 'cancel', 'Cancel', false);
			}
			else
			{
				$this->toolbarButton("day.listevents", 'cancel', 'cancel', 'Cancel', false);
			}
		}

		JHTML::_('behavior.tooltip');

		// I pass in the rp_id so that I can return to the repeat I was viewing before editing
		$this->assign("rp_id", JRequest::getInt("rp_id", 0));

		$this->setCreatorLookup();

		$this->_adminStart();

		if (JVersion::isCompatible("3.0"))
		{
			$this->setLayout("edit");
		}
		else
		{
			$this->setLayout("edit16");
		}

		JEVHelper::componentStylesheet($this, "editextra.css");		

		$this->setupEditForm();
		
		parent::displaytemplate($tpl);

		$this->_adminEnd();

	}

	function _adminStart()
	{

		$dispatcher = & JDispatcher::getInstance();
		list($this->year, $this->month, $this->day) = JEVHelper::getYMD();
		$this->Itemid = JEVHelper::getItemid();
		$this->datamodel = new JEventsDataModel();
		$dispatcher->trigger('onJEventsHeader', array($this));
		?>
		<div style="clear:both"  
				<?php
				$mainframe = JFactory::getApplication();
				$params = JComponentHelper::getParams(JEV_COM_COMPONENT);
				echo (!JFactory::getApplication()->isAdmin() && $params->get("darktemplate", 0)) ? "class='jeventsdark'" : "class='jeventslight'";
				?>>
			<div id="toolbar-box" >
			<?php
			$bar = & JToolBar::getInstance('toolbar');
			$barhtml = $bar->render();
			//$barhtml = str_replace('href="#"','href="javascript void();"',$barhtml);
			//$barhtml = str_replace('submitbutton','return submitbutton',$barhtml);
			echo $barhtml;
			if (JVersion::isCompatible("3.0"))
			{
				$title ="";// JFactory::getApplication()->JComponentTitle;
			}
			else
			{
				$title = JFactory::getApplication()->get('JComponentTitle');
			}
			echo $title;
			?>
			</div>
		<?php

	}

	function _adminEnd()
	{
		?>
		</div>
		<?php
		$dispatcher = & JDispatcher::getInstance();
		$dispatcher->trigger('onJEventsFooter', array($this));

	}

	function toolbarButton($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = & JToolBar::getInstance('toolbar');

		// Add a standard button
		$bar->appendButton('Jev', $icon, $alt, $task, $listSelect);

	}

	function toolbarLinkButton($task = '', $icon = '', $iconOver = '', $alt = '')
	{
		$bar = & JToolBar::getInstance('toolbar');

		// Add a standard button
		$bar->appendButton('Jevlink', $icon, $alt, $task, false);

	}

	function toolbarConfirmButton($task = '', $msg = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		$bar = & JToolBar::getInstance('toolbar');

		// Add a standard button
		$bar->appendButton('Jevconfirm', $msg, $icon, $alt, $task, $listSelect, false, "document.adminForm.updaterepeats.value");

	}

	// This handles all methods where the view is passed as the first argument
	function __call($name, $arguments)
	{
		if (strpos($name, "_") === 0)
		{
			$name = "ViewHelper" . ucfirst(substr($name, 1));
		}
		$helper = ucfirst($this->jevlayout) . ucfirst($name);
		if (!$this->loadHelper($helper))
		{
			$helper = "Default" . ucfirst($name);
			if (!$this->loadHelper($helper))
			{
				return;
			}
		}
		$args = array_unshift($arguments, $this);
		if (class_exists($helper))
		{
			if (class_exists("ReflectionClass"))
			{
				$reflectionObj = new ReflectionClass($helper);
				if (method_exists($reflectionObj, "newInstanceArgs"))
				{
					$var = $reflectionObj->newInstanceArgs($arguments);
				}
				else
				{
					$var = $this->CreateClass($helper, $arguments);
				}
			}
			else
			{
				$var = $this->CreateClass($helper, $arguments);
			}
			return;
		}
		else if (is_callable($helper))
		{
			return call_user_func_array($helper, $arguments);
		}

	}

	protected function CreateClass($className, $params)
	{
		switch (count($params)) {
			case 0:
				return new $className();
				break;
			case 1:
				return new $className($params[0]);
				break;
			case 2:
				return new $className($params[0], $params[1]);
				break;
			case 3:
				return new $className($params[0], $params[1], $params[2]);
				break;
			case 4:
				return new $className($params[0], $params[1], $params[2], $params[3]);
				break;
			case 5:
				return new $className($params[0], $params[1], $params[2], $params[3], $params[4]);
				break;
			case 6:
				return new $className($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
				break;
			case 7:
				return new $className($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]);
				break;
			case 8:
				return new $className($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7]);
				break;
			case 9:
				return new $className($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8]);
				break;
			case 10:
				return new $className($params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9]);
				break;
			default:
				echo "Too many arguments";
				return null;
				break;
		}

	}

	function loadHelper($file = null)
	{
		if (function_exists($file) || class_exists($file))
			return true;

		// load the template script
		jimport('joomla.filesystem.path');
		$helper = JPath::find($this->_path['helper'], $this->_createFileName('helper', array('name' => $file)));

		if ($helper != false)
		{
			// include the requested template filename in the local scope
			include_once $helper;
		}
		return $helper;

	}

}
