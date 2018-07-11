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

        try {

            $corsi_primo_tipo = $this->elencoCorsiPerTipoScadenza(1);//prende soltanto i corsi del tipo 1, ovvero esma - ivass - etc

            foreach ($corsi_primo_tipo as $corso) {

                $intervaldafinecorso = date_diff(date(), $corso->data_fine);
                $intervaldalastalert = date_diff(date(), $corso->lastalert);
                if ($intervaldafinecorso < 14 || $intervaldalastalert > 14) {
                    $this->sendMail($corso);//VIENE MANDATA LA MAIL: SE IL CORSO SCADE TRA MENO DI 15 GIORNI O SE SONO PASSATI PIU' DI 15 DALL'INVIO
                }
            }

            $corsi_secondo_tipo = $this->elencoCorsiPerTipoScadenza(2); //prende tutti gli altri
            foreach ($corsi_secondo_tipo as $corso) {

                $intervaldafinecorso = date_diff(date(), $corso->data_fine);
                $intervaldalastalert = date_diff(date(), $corso->lastalert);
                if (($intervaldafinecorso < 30 && $intervaldalastalert > 6) || $intervaldalastalert > 30) {
                    $this->sendMail($corso);//VIENE MANDATA LA MAIL: SE IL CORSO SCADE TRA MENO DI 30 GIORNI E SE SONO PASSATI PIU' DI 6 DALL'INVIO; OPPURE NE SONO PASSATI PIU' 30 COMUNQUE
                }
            }
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE SEND_ALERT_SCADENZA_CORSI', 0, 1, 0);

        }
    }

    public function elencoCorsiPerTipoScadenza($tipo){

        try {

            $query = $this->_db->getQuery(true);
            $query->select('* from crg_ggif_view_alert_unita_edizioni');
            $query->where('id_categoria_alert=' . $tipo. ' and pubblicato=1');
            $this->_db->setQuery($query);
            $item = $this->_db->loadObjectlist();
            if (count($item) > 0) {
                return $item;
            } else {

                return null;
            }
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE ELENCO_CORSI_PER_TIPO_SCADENZA', 0, 1, 0);

        }
    }

    private function sendMail($corso){

        try {
            $oggettomail = 'avviso scadenza corso: ';
            $testomail = " ricevi questa mail per l'approssimarsi della scadenza del corso in oggetto, che non hai ancora completato. Ti invitiamo a completare lo stesso al fine di raggiungere i tuoi obiettivi formativi";
            $result = $this->getUtentiInScadenzaCorso($corso);

            if ($result['rows'] != null) {


                foreach ($result['rows'] as $row) {

                    //$to = $row->email;
                    $to = ['a.petruzzella71@gmail.com'];
                    $mailer = JFactory::getMailer();
                    $config = JFactory::getConfig();
                    $sender = array(
                        $config->get('mailfrom'),
                        $config->get('fromname')
                    );

                    $mailer->setSender($sender);
                    $mailer->addRecipient($to);
                    $mailer->setSubject($oggettomail . " " . $corso->titolo);
                    $mailer->setBody('Gentile ' . $row->cognome . " " . $testomail);

                    //
                    //
                    //$send = $mailer->Send();
                    //
                    //

                    DEBUGG::log('corso:' . $result['titolo'] . ' a:' . json_decode($row->fields)->email . ' cognome:' . $row->cognome, 'INVIO MAIL', 0, 1, 0);

                }

            }

            $this->updateLastmail($corso);
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE INVIO MAIL', 0, 1, 0);

        }

    }

    private function getUtentiInScadenzaCorso($corso){

        $db = JFactory::getDbo();

        try {

            if($corso) {


                switch ($corso->accesso) {

                    case 'iscrizioneeb':
                        $query = $db->getQuery(true);
                        $query->select('*');
                        $query->from('#__gg_report_users');
                        $query->where('id_event_booking=' . $corso->id_event_booking . ' and id not in (select id_anagrafica from #__gg_view_stato_user_corso where id_corso=' . $corso->id . ' and stato=1)');

                        $db->setQuery($query);

                        $result['rows'] = $db->loadObjectList();

                        break;
                    case 'gruppo':
                        $query = $db->getQuery(true);
                        $query->select('anagrafica.*');
                        $query->from('#__gg_report_users as anagrafica');
                        $query->join('inner', '#__user_usergroup_map as um on anagrafica.id_user=um.user_id');
                        $query->join('inner', '#__gg_usergroup_map as m on m.idgruppo=um.group_id');
                        $query->where('m.idunita=' . $corso->id . ' and anagrafica.id not in ( select id_anagrafica from #__gg_view_stato_user_corso where id_corso=' . $corso->id . ' and stato=1)');

                        $db->setQuery($query);

                        $result['rows'] = $db->loadObjectList();



                        break;
                }

            }
            return $result;
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE GET UTENTI SCADENZA CORSI', 0, 1, 0);

        }
    }

    private function updateLastmail($corso){

        $db = JFactory::getDbo();
        $query="UPDATE crg_ggif_edizione_unita_gruppo SET lastalert=now()";
        $db->setQuery($query);
        $db->execute();


    }

}
