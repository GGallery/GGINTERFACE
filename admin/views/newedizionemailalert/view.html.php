<?php

/**
 * @version		1
 * @package		webtv
 * @author 		antonio
 * @author mail	tony@bslt.it
 * @link
 * @copyright	Copyright (C) 2011 antonio - All rights reserved.
 * @license		GNU/GPL
 */
// no direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');


//jimport('joomla.application.component.helper');


class gginterfaceViewNewedizionemailalert extends JViewLegacy {


    function display($tpl = null)
    {
        jimport('joomla.environment.uri');

        $host = JURI::root();
        $form = $this->get('Form');
        $this->form = $form;
        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.framework');
        JHtml::_('jquery.ui', array('core', 'sortable'));
        $document = JFactory::getDocument();
        JToolBarHelper::title("Invio mail ", 'gginterface');

        parent::display();
    }
}
