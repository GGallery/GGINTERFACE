<?php
/**
 * Created by PhpStorm.
 * User: Tony
 * Date: 19/02/2018
 * Time: 11:53
 */
//echo "Backend not accessible";
defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('gginterface');

$controller->execute(JFactory::getApplication()->input->get('task'));

$controller->redirect();