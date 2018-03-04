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

        DEBUGG::log('CRON:', 'batch_anag');

        $filename = '../batch/LABEL_ANAG.txt';
        $fp = @fopen($filename, 'r');
        $userList = [];
        $totUsers = 0;
        $newUsers = 0;
        $bodylog = '';

        if (empty($fp))
            throw new BadMethodCallException('File LABEL_ANAG.txt non leggibile', E_USER_ERROR);

        if ($fp) {
            $file = explode("\n", fread($fp, filesize($filename)));
            foreach ($file as $single){
                if($single)
                    $userList [] = explode(";" , $single);
            }
            $totUsers = sizeof($userList);
        }

        $newUsers = $this->checkExistingUser($userList);

        $bodylog .= "TotUser: $totUsers, NewUser: ".sizeof($newUsers);
        if(sizeof($newUsers)>0)
            $bodylog.= "List: ". json_encode($newUsers);

        DEBUGG::log($bodylog, 'Batch_anag',0, 1, 0);

        echo $bodylog;
//        DEBUGG::info($totUsers, 'tot');
//        DEBUGG::info(sizeof($newUsers), 'new');
//        DEBUGG::info($newUsers, 'new');
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

        #TODO CORREGGE TABELLA
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

//
//$group = array('id'=>0, 'title'=>'Group Title', 'parent_id'=>1);
//JLoader::import('joomla.application.component.model');
//JLoader::import('group', JPATH_ADMINISTRATOR.'/components/com_users/models');
//$groupModel = JModelLegacy::getInstance( 'Group', 'UsersModel' );
//
//if(! $groupModel->save($group) )
//{
//JFactory::getApplication()->enqueueMessage($groupModel->getError());
//return false;
//}



}
