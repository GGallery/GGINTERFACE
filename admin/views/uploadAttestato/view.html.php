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


class gginterfaceViewUploadAttestato extends JViewLegacy {


    function display($tpl = null)
    {
        jimport('joomla.environment.uri');

        $host = JURI::root();

        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.framework');
        JHtml::_('jquery.ui', array('core', 'sortable'));
        $document = JFactory::getDocument();
        JToolBarHelper::title("Caricamento Attestati Residenziali ", 'gginterface');
        $document->addStyleSheet($host . 'administrator/components/com_gginterface/jupload/css/jquery.fileupload.css');
        $document->addStyleSheet($host . 'administrator/components/com_gginterface/jupload/css/jquery.fileupload-ui.css');
        $document->addScript($host . 'administrator/components/com_gginterface/jupload/js/jquery.fileupload.js');
        $document->addScript($host . 'administrator/components/com_gginterface/jupload/js/procedure.js');

        parent::display();
    }
}
