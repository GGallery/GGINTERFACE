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
class gginterfaceControllerAlertcarige extends JControllerLegacy
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


    public function sendAlertScadenzaCorsi(){

        $corsi_primo_tipo=$this->elencoCorsiPerTipoScadenza(1);//prende soltanto i corsi del tipo 1, ovvero esma - ivass - etc

        foreach ($corsi_primo_tipo as $corso){

            $intervaldafinecorso=date_diff(date(),$corso['data_fine']);
            $intervaldalastalert=date_diff(date(),$corso['lastalert']);
            if($intervaldafinecorso<14 || $intervaldalastalert>14){
                $this->sendMail($corso);//VIENE MANDATA LA MAIL: SE IL CORSO SCADE TRA MENO DI 15 GIORNI O SE SONO PASSATI PIU' DI 15 DALL'INVIO
            }
        }

        $corsi_secondo_tipo=$this->elencoCorsiPerTipoScadenza(2); //prende tutti gli altri
        foreach ($corsi_secondo_tipo as $corso){

            $intervaldafinecorso=date_diff(date(),$corso['data_fine']);
            $intervaldalastalert=date_diff(date(),$corso['lastalert']);
            if(($intervaldafinecorso<30 && $intervaldalastalert>6) || $intervaldalastalert>30){
                $this->sendMail($corso);//VIENE MANDATA LA MAIL: SE IL CORSO SCADE TRA MENO DI 30 GIORNI E SE SONO PASSATI PIU' DI 6 DALL'INVIO; OPPURE NE SONO PASSATI PIU' 30 COMUNQUE
            }
        }

    }

    public function elencoCorsiPerTipoScadenza($tipo){

        $query = $this->_db->getQuery(true);
        $query->select('* from crg_ggif_view_unita_edizioni');
        $query->where('id_categoria_alert='.$tipo);
        $this->_db->setQuery($query);
        $item = $this->_db->loadAssoclist();
        if(count($item)>0){
            return $item;
        }else{

            return null;
        }

    }

    private function sendMail($idcorso){


    }


}
