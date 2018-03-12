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

        $id_utente = JRequest::getVar('id_utente');
        $id_edizione = JRequest::getVar('id_edizione');

        DEBUGG::log($id_utente, 'SSO_IDUTENTE', 0, 1, 0);

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('`id`, `username`, `password`');
        $query->from('`#__users`');
        $query->where('id=' . $id_utente) ;

        $db->setQuery( $query );
        $user = $db->loadObject();

        $this->adminblock($user);

        if($user) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';

            $response['username'] = $user->username;
            $logged= $this->_japp->triggerEvent('onUserLogin', array((array)$response, $options));
            if($logged)
                $this->_japp->enqueueMessage("Accesso effettuato correttamente come utente ". $response['username'], 'success');
            else {
                $this->_japp->enqueueMessage("Problemi nell'effettuare l'accesso", 'danger');
            }
        }
        else
        {
            $this->_japp->enqueueMessage("Credenziali errate", 'danger');
        }

        $alias='corsi_fad';
        if($id_edizione && $a=$this->getAliasCorso($id_edizione))
            $this->_japp->redirect(JRoute::_("index.php?option=com_gglms&view=unita&alias=$alias"));

        $this->_japp->redirect("https://www.carigelearning.it/home/corsi_fad.html");

    }

    public function login_plain_email() {

        $email_utente = JRequest::getVar('email');
        $id_edizione = JRequest::getVar('id_edizione');

        DEBUGG::log($email_utente, 'SSO_PLAIN_EMAIL', 0, 1, 0);

        if($email_utente == ""  || !$email_utente)
            throw new RuntimeException('Parametro email non corretto', E_USER_ERROR);

        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('`id`, `username`, `password`');
        $query->from('`#__users`');
        $query->where("email='$email_utente'") ;

        $db->setQuery( $query );

        $user = $db->loadObject();
        $this->adminblock($user);

        if($user) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';

            $response['username'] = $user->username;
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

        $alias='corsi_fad';
        if($id_edizione && $a=$this->getAliasCorso($id_edizione))
            $alias = $a;

        $this->_japp->redirect(JRoute::_("index.php?option=com_gglms&view=unita&alias=$alias"));
    }

    public function login_enc_email() {

        // Encoded mail
        $data =  JRequest::getVar('enc_email');
        $email_utente = $this->aes_decrypt(base64_decode($data));

        $id_edizione = JRequest::getVar('id_edizione');

        DEBUGG::log($email_utente, 'SSO_ENC_EMAIL', 0, 1, 0);

        if($email_utente == ""  || !$email_utente)
            throw new RuntimeException('Parametro email non corretto', E_USER_ERROR);

        $query = $this->_db->getQuery(true);
        $query->select('`id`, `username`, `password`');
        $query->from('`#__users`');
        $query->where("email='$email_utente'") ;

        $this->_db->setQuery( $query );

        $user = $this->_db->loadObject();


        $this->adminblock($user);

        if($user) {
            JPluginHelper::importPlugin('user');

            $options = array();
            $options['action'] = 'core.login.site';

            $response['username'] = $user->username;
            $logged= $this->_japp->triggerEvent('onUserLogin', array((array)$response, $options));
            if($logged)
                $this->_japp->enqueueMessage("Accesso effettuato correttamente come utente ". $_REQUEST['username'], 'success');
            else {
                $this->_japp->enqueueMessage("Accesso negato per questo account", 'danger');
            }
        }
        else
        {
            $this->_japp->enqueueMessage("Accesso negato per questo account", 'danger');
        }

        $alias='corsi_fad';
        if($id_edizione && $a=$this->getAliasCorso($id_edizione))
            $alias = $a;

        $this->_japp->redirect(JRoute::_("index.php?option=com_gglms&view=unita&alias=$alias"));
    }

    private function aes_decrypt($data) {
        try {
            $encryption_key = 'po5NBCHk3mIeGiwt5DKXR5Rq9AqamWD4';
            $iv = '8947az34awl34kjq';
            $res = openssl_decrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv);
        } catch (Exception $e)
        {
            DEBUGG::log('DECRYPT' , 'ERROR', 1, 1, 1);
        }
        return $res;
    }


    public function carige_encrypt() {

        $data = JRequest::getVar('enc_email');

        $key = 'po5NBCHk3mIeGiwt5DKXR5Rq9AqamWD4';
        $iv = '8947az34awl34kjq';
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        echo "\n EncryptedData:" .  base64_encode($encrypted);

        $this->_japp->close();
    }

    private function adminblock($user){
        $groups = JAccess::getGroupsByUser($user->id);
        if(in_array(8, $groups)){
            throw new RuntimeException('Accesso negato in questa modalità per account amministratori', E_USER_ERROR);
        }

    }

    private function getAliasCorso($id_edizione) {

        try {
            $query = $this->_db->getQuery(true);

            $query->select('u.alias');
            $query->from('#__ggif_edizione_unita_gruppo as g');
            $query->join('join', '#__gg_unit as u ON u.id g.id_unita');
            $query->where('g.id_id_edizione = ' . $id_edizione);

            $this->_db->setQuery($query);
            $alias = $this->_db->loadResult();
        }catch (Exception $e) {
            DEBUGG::info($e, "Errore identificazione corso", 0,1,1);
            return false;
        }

        return $alias;
    }

}
