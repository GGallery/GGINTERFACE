<?php

/**
 * @package		Joomla.Tutorials
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.database.table');

class gginterfaceTablebookcorso extends JTable {

    function __construct(&$db) {
        parent::__construct('#__ggif_bookcorsi', 'id', $db);
    }

    /**
     * Overloaded bind function
     *
     * @param	array		$hash named array
     * @return	null|string	null is operation was satisfactory, otherwise returns an error
     * @see JTable:bind
     * @since 1.5
     */
   // public function bind($array, $ignore = '') {


   // }

    /*
     * Verifico la durata del contenuto
     */



    /**
     * Overloaded check function
     *
     * @return	boolean
     * @see		JTable::check
     * @since	1.5
     */


}

