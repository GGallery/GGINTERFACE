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
class gginterfaceControllerBatchcarige extends JControllerLegacy
{
    private $_japp;
    public  $_params;
    protected $_db;


    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->_japp = JFactory::getApplication();
        $this->_params = $this->_japp->getParams();
        $this->_db = JFactory::getDbo();
    }



    public function batch_anag() {

        $filename = '../batch/LABEL_ANAG.txt';
        $fp = @fopen($filename, 'r');
        $userList = [];
        $bodylog = '';

        if (empty($fp))
            DEBUGG::log('ERROR:', 'File LABEL_ANAG.txt non leggibile',0,1,0);

        if ($fp) {
            $file = explode("\n", fread($fp, filesize($filename)));
            foreach ($file as $single){
                if($single)
                    $userList [] = explode(";" , $single);
            }
        }

        $newUsers = $this->checkExistingUser($userList);

        $bodylog .= "TotUser: " . sizeof($userList);
        $bodylog .= " - NewUser: " . sizeof($newUsers);

        if(sizeof($newUsers)>0)
            $bodylog.= " - List: ". json_encode($newUsers);

        DEBUGG::log($bodylog, 'Batch_anag',0, 1, 0);

        echo $bodylog;
        $this->_japp->close();
    }

    private function checkExistingUser($userList) {

        $existingUserList = $this->getExistingUserList();
        $newUser = [];

        foreach ($userList as $candidateNewUser){
            $candidateNewUserId = $candidateNewUser[0];
            if(!in_array($candidateNewUserId, $existingUserList)) {
                if($this->insertNewUser($candidateNewUser))
                    array_push($newUser, $candidateNewUser);
            }
        }

        return $newUser;
    }

    private function getExistingUserList(){

//        TODO CORREGGE TABELLA
        try {
            $query = $this->_db->getQuery(true);
            $query->select('`id`');
            $query->from('`#__users_tmp`');
            $this->_db->setQuery($query);
            $existingUserList = $this->_db->loadColumn(0);
        }catch (Exception $e) {
            return false;
        }
        return $existingUserList;
    }

    private function insertNewUser($user){

        $object = new stdClass();

        $object->id = $user[0];
        $object->name = $user[1] . " " . $user[2];
        $object->username = $user[4];
        $object->email = $user[4];
        $object->password = md5($user[4]);
        $object->block = 0;
        $object->sendEmail = 0;
        $object->registerDate = date();

        $insert = $this->_db->insertObject('#__users_tmp', $object);

        if($insert) {
            $query = "INSERT IGNORE INTO #__user_usergroup_map (user_id, group_id) VALUES ($user[0] , 2)";
            $this->_db->setQuery($query);
            if (false === ($results = $this->_db->query()))
                throw new RuntimeException($this->_db->getErrorMsg(), E_USER_ERROR);
        }
        return true;
    }


    public function batch_edizioni() {

        $filename = '../batch/LABEL_EDIZIONI.txt';
        $fp = @fopen($filename, 'r');
        $edizioniList = [];
        $bodylog = '';

        if (empty($fp))
            DEBUGG::log('ERROR:', 'File LABEL_EDIZIONI.txt non leggibile',0,1,0);


        if ($fp) {
            $file = explode("\n", fread($fp, filesize($filename)));
            foreach ($file as $single){
                if($single)
                    $edizioniList [] = explode(";" , $single);
            }
        }

        $newEdizioni = $this->checkExistingEdizioni($edizioniList);

        $bodylog .= "TotEdizioni: " .sizeof($edizioniList);
        $bodylog .= " - NewEdizioni: ".sizeof($newEdizioni);
        if(sizeof($newEdizioni)>0)
            $bodylog.= " -  List: ". json_encode($newEdizioni);

        DEBUGG::log($bodylog, 'BATCH_EDIZIONI',0, 1, 1);

        $this->_japp->close();
    }

    private function checkExistingEdizioni($edizioniList) {

        $existingEdizioniList = $this->getExistingEdizioni();
        $newEdizioni = [];

        foreach ($edizioniList as $candidateNewEdizione){
            $candidateNewEdizioniId = $candidateNewEdizione[0];
            if(!in_array($candidateNewEdizioniId , $existingEdizioniList)) {
                if($this->insertNewEdizione($candidateNewEdizione))
                    array_push($newEdizioni, $candidateNewEdizione);
            }
        }

        return $newEdizioni;
    }

    private function getExistingEdizioni(){

//        TODO CORREGGE TABELLA
        try {
            $query = $this->_db->getQuery(true);
            $query->select('`id_edizione`');
            $query->from('`#__ggif_edizione_unita_gruppo`');
            $this->_db->setQuery($query);
            $existingEditioniList = $this->_db->loadColumn(0);
        }catch (Exception $e) {
            return false;
        }
        return $existingEditioniList ;
    }

    private function insertNewEdizione($edizione){

        $id_gruppo = $this->createUserGroup($edizione);
        $id_unita  = $this->createGGlmsUnit($edizione);

        $this->create_unit_gruppo_link($id_gruppo, $id_unita);

        $object = new stdClass();

        $object->id_edizione = $edizione[0];
        $object->id_unita = $id_unita;
        $object->id_gruppo = $id_gruppo;

        $this->_db->insertObject('#__ggif_edizione_unita_gruppo', $object);

        return true;
    }

    private function createUserGroup($edizione) {

        $group = array('id'=>0, 'title'=> $edizione[1], 'parent_id'=>2);
        JLoader::import('joomla.application.component.model');
        JLoader::import('group', JPATH_ADMINISTRATOR.'/components/com_users/models');
        $groupModel = JModelLegacy::getInstance( 'Group', 'UsersModel' );

        if(! $groupModel->save($group) )
        {
            JFactory::getApplication()->enqueueMessage($groupModel->getError());
            return false;
        }
        $query = $this->_db->getQuery(true);
        $query->select('MAX(id)');
        $query->from('#__usergroups');
        $this->_db->setQuery($query);
        $last_id  = $this->_db->loadResult();

        return $last_id;

    }

    private function createGGlmsUnit($edizione) {


        $object = new stdClass();

        $object->titolo = $edizione[1];
        $object->alias = $this->setAlias($edizione[1]);
        $object->descrizione = $edizione[2];
        $object->unitapadre = 1;
        $object->pubblicato = 0;
        $object->accesso = 'gruppo';
        $object->is_corso = 1;
        $object->data_inizio = $edizione[3];
        $object->data_fine = $edizione[4];

        $this->_db->insertObject('#__gg_unit', $object);
        $unit_id = $this->_db->insertid();

        return $unit_id;

    }

    private function create_unit_gruppo_link($id_gruppo, $id_unita){


        $object = new stdClass();
        $object->idunita = $id_unita;
        $object->idgruppo = $id_gruppo;
        $this->_db->insertObject('#__gg_usergroup_map', $object);

        $query = "UPDATE crg_gg_configs SET config_value = CONCAT(config_value,',$id_gruppo') WHERE config_key = 'id_gruppi_visibili'";
        $this->_db->setQuery($query);
        $this->_db->execute();

        echo $query;

        return true;

    }

    private function setAlias($text) {

        $text = preg_replace('~[^\\pL\d]+~u', '_', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '_');

        return $text;
    }


    public function batch_iscrizioni(){

        $filename = '../batch/LABEL_ISCRIZIONI.txt';
        $fp = @fopen($filename, 'r');
        $iscrizioniList = [];

        if (empty($fp))
            DEBUGG::log('ERROR:', 'File LABEL_ISCRIZIONI.txt non leggibile',0,1,0);

        if ($fp) {
            $file = explode("\n", fread($fp, filesize($filename)));
            foreach ($file as $single){
                if($single)
                    $iscrizioniList [] = explode(";" , $single);
            }
        }

        if($this->clearUserGroupMap() && $this->storeIscrizioniTemp($iscrizioniList))
            $this->setUsergroupUserMap();
        else
            DEBUGG::log('ERRORE NEL PROCESSO', 'BATCH_ISCRIZIONI',0, 1, 1);

        DEBUGG::log('Utenti associati OK', 'BATCH_ISCRIZIONI',0, 1, 1);

        $this->_japp->close();
    }

    public function clearUserGroupMap() {

        $query = $this->_db->getQuery(true);

        $conditions = array(
            $this->_db->quoteName('group_id') . ' not in (1,2,6,7,8,9,20)'
        );

//        TODO cambiare nome tabella
        $query->delete($this->_db->quoteName('#__user_usergroup_map_tmp'));
        $query->where($conditions);

        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }

    public function storeIscrizioniTemp($iscrizioniList) {

        $query = "TRUNCATE TABLE ".$this->_db->quoteName('#__ggif_user_edizione_map');
        $this->_db->setQuery($query);
        $this->_db->execute();

        foreach ($iscrizioniList as $iscrizione){
            $object = new stdClass();
            $object->edizione_id = $iscrizione[0];
            $object->user_id = $iscrizione[1];

            if(!$this->_db->insertObject('#__ggif_user_edizione_map', $object))
                throw new BadMethodCallException('Errore nella procedura storeIscrizioniTemp', E_USER_ERROR);
        }

        return true;
    }

    public function setUsergroupUserMap(){

//        TODO Cambiare nome tabella
        try {
            $query = 'INSERT IGNORE INTO #__user_usergroup_map_tmp ';
            $query .= ' SELECT m.user_id AS user_id, g.id_gruppo AS group_id FROM  crg_ggif_user_edizione_map AS m INNER JOIN crg_ggif_edizione_unita_gruppo AS g ON g.id_edizione = m.edizione_id';

            $this->_db->setQuery($query);
            $this->_db->execute();
        } catch (Exception $e) {
            throw new BadMethodCallException('Errore nella procedura setUsergroupUserMap', E_USER_ERROR);
        }

    }

}
