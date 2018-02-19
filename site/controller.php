<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once 'administrator/components/com_gginterface/models/libs/debugg/debugg.php';

jimport('joomla.application.component.controller');
jimport('joomla.access.access');


class gginterfaceController extends JControllerLegacy {

    private $_user;
    private $_japp;
    public  $_params;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->_japp = JFactory::getApplication();

    }


}