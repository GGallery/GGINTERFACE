<?php

/**
 * @package		Joomla.Tutorials
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class gginterfaceModelNewedizionemailalert extends JModelList {

    //Add this handy array with database fields to search in
    protected $searchInFields = array( 'a.id','a.titolo', 'descrizione');
    private $unitas=array();
    private $contenuti=array();

//Override construct to allow filtering and ordering on our fields
    public function __construct($config = array()) {
        $config['filter_fields'] = array_merge($this->searchInFields, array('a.id'));
        //$config['filter_fields'] = array_merge($this->searchInFields, array('u.id'));
        parent::__construct($config);
    }

    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_gginterface.newedizionemailalert', 'newedizionemailalert', array('control' => 'jform', 'load_data' => $loadData));

        return $form;
    }


}
