<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * Controller for single contact view
 *
 * @since  1.5.19
 */
class gginterfaceControllerSsocarige extends JControllerLegacy
{
    private $_japp;
    public  $_params;
    protected $_db;
    private $_filterparam;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_japp = JFactory::getApplication();
        $this->_params = $this->_japp->getParams();
        $this->_db = JFactory::getDbo();


    }

    public function login() {

        //        http://www.carigelearning.test/home/index.php?option=com_gginterface&task=ssocarige.loginext&id_utente=652

        $id_utente = JRequest::getVar('id_utente');

        DEBUGG::log($id_utente, 'LOGINSI', 0, 1, 0);

        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('`id`, `username`, `password`');
        $query->from('`#__users`');
        $query->where('id=' . $id_utente) ;

            $db->setQuery( $query );
        $result = $db->loadObject();

        if($result) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';

            $response['username'] = $result->username;
            $logged= $app->triggerEvent('onUserLogin', array((array)$response, $options));
            if($logged)
                $app->enqueueMessage("Accesso effettuato correttamente come utente ". $response['username'], 'success');
            else {
                $app->enqueueMessage("Problemi nell'effettuare l'accesso", 'danger');
            }
        }
        else
        {
            $app->enqueueMessage("Credenziali errate", 'danger');
        }
        $app->redirect(JRoute::_('index.php?option=com_gglms&view=unita&alias=corsi'));
    }

    public function loginext() {

//        http://www.carigelearning.test/home/index.php?option=com_gginterface&task=ssocarige.loginext&email_utente=antonio@ggallery.it

        $email_utente = JRequest::getVar('email_utente');

        DEBUGG::log($email_utente, 'LOGINEXT', 0, 1, 0);

        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('`id`, `username`, `password`');
        $query->from('`#__users`');
        $query->where("email='$email_utente'") ;

        $db->setQuery( $query );

        $result = $db->loadObject();


        if($result) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';

            $response['username'] = $result->username;
            $logged= $app->triggerEvent('onUserLogin', array((array)$response, $options));
            if($logged)
                $app->enqueueMessage("Accesso effettuato correttamente come utente ". $_REQUEST['username'], 'success');
            else {
                $app->enqueueMessage("Problemi nell'effettuare l'accesso", 'danger');
            }
        }
        else
        {
            $app->enqueueMessage("Credenziali errate", 'danger');
        }
        $app->redirect(JRoute::_('index.php?option=com_gglms&view=unita&alias=corsi'));
    }

    public function crontest() {

    //        https://www.carigelearning.test/home/index.php?option=com_gginterface&task=ssocarige.crontest
        DEBUGG::log('TEST', 'CRON',1,1,0  );

}

}
