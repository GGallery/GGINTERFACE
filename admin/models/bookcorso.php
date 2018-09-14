<?php

/**
 * @package		Joomla.Tutorials
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

require_once JPATH_COMPONENT . '/models/libs/debugg/debugg.php';

class gginterfaceModelbookcorso extends JModelAdmin {



    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_gginterface.bookcorso', 'bookcorso', array('control' => 'jform', 'load_data' => $loadData));

        return $form;
    }

    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_gginterface.edit.bookcorso.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }


    public function getTable($name = '', $prefix = 'gginterfaceTable', $options = array()) {

        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get a single record.
     *
     * @param	integer	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($pk = null) {
        //debug::msg('model->getItem');

        $item = parent::getItem($pk);




        return $item;
    }




}
