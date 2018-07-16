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

    public function start(){

        //$this->sendAlertScadenzaCorsi();
        $this->sendCruscrotto();
    }

    private function sendMail($oggettomail,$testomail,$to,$cognome){

        //$to = $row->email;
        $to = 'a.petruzzella71@gmail.com';
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );
        $mailer->setSender($sender);
        $mailer->addRecipient($to);
        $mailer->setSubject($oggettomail);
        $mailer->setBody('Gentile ' . $cognome . " " . $testomail);
        //$send = $mailer->Send();

    }
    public function sendAlertScadenzaCorsi(){

        try {

            $corsi_primo_tipo = $this->elencoCorsiPerTipoScadenza(1);//prende soltanto i corsi del tipo 1, ovvero esma - ivass - etc

            foreach ($corsi_primo_tipo as $corso) {
                if ($corso->daysfromdata_fine< 14 || $corso->daysfromlastalert > 14 || $corso->daysfromlastalert==null) {

                    //echo 'PRIMO TIPO invio mail per :'.$corso->id.'<BR>';
                    $this->sendMailAlertScadenzaCorsi($corso);//VIENE MANDATA LA MAIL: SE IL CORSO SCADE TRA MENO DI 15 GIORNI O SE SONO PASSATI PIU' DI 15 DALL'INVIO
                }
            }

            $corsi_secondo_tipo = $this->elencoCorsiPerTipoScadenza(2); //prende tutti gli altri
            foreach ($corsi_secondo_tipo as $corso_) {

                if (($corso_->daysfromdata_fine < 30 && $corso_->daysfromlastalert > 6) || ($corso_->daysfromlastalert > 30 || $corso_->daysfromlastalert==null)) {
                    //echo 'SECONDO TIPO invio mail per :'.$corso_->id.'<BR>';
                    $this->sendMailAlertScadenzaCorsi($corso_);//VIENE MANDATA LA MAIL: SE IL CORSO SCADE TRA MENO DI 30 GIORNI E SE SONO PASSATI PIU' DI 6 DALL'INVIO; OPPURE NE SONO PASSATI PIU' 30 COMUNQUE
                }
            }
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE SEND_ALERT_SCADENZA_CORSI', 0, 1, 0);
        }
    }

    public function sendCruscrotto(){


        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__gg_report_users');
        $db->setQuery($query);
        $users=$db->loadObjectList();
        $i=0;
        foreach ($users as $user){
            if ($i<5) {
                $userid = $user->id_user;
                if ($this->utente_abilitato($userid)) {

                    if ($this->utente_abilitato_esma($userid)) {
                        $tot_esma = 30;
                        $ore_esma = $this->ore_esma($userid);
                        $percentuale_ore_esma = ($ore_esma / $tot_esma) * 100;
                        $display_state_esma = null;
                    }
                    $ore_ivass = $this->ore_ivass($userid);
                    $scadenza_ivass = $this->scadenza_ivass($userid);
                    $tot_ivass = $this->totale_ivass($userid);
                    $oggettomail = 'situazione cruscotto formativo Carigelearning';
                    $to = json_decode($user->fields)->email;
                    $testomail=' hai totalizzato '.$ore_esma.' su 30 ore, scadenza 31/12/2018 e '.$ore_ivass.' su '.$tot_ivass.' scadenza '.$scadenza_ivass;
                    $this->sendMail($oggettomail,$testomail,$to,$user->cognome);
                }

            }
            $i++;
        }
    }

    public function sendNewEdizioneMailAlert(){

        try {
        $id_corso=JRequest::getVar("id_corso");
        $testomail=JRequest::getVar("testo_mail");
        $oggettomail = 'avviso partenza nuovo corso';
        $result = $this->getUtentiNuovaEdizione($id_corso);
        $i=0;
        //echo "elenco dei destinatari:<br>";
        foreach ($result as $row){
            $to = json_decode($row->fields)->email;
            if($i<4) {
                $this->sendMail($oggettomail,$testomail,$to,$row->cognome);
            }
            $i++;
        }
//            JFactory::getApplication()->enqueueMessage(JText::_('MAIL OK'));
         //$this->setRedirect('administrator/index.php?option=com_gginterface&view=newedizionemailalert&extension=com_gginterface', JFactory::getApplication()->enqueueMessage(JText::_('MAIL OK')));
            echo 'true';
            $this->_japp->close();

        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE INVIO MAIL NUOVA EDIZIONE', 0, 1, 0);

            echo $ex->getMessage();
            ///$this->setRedirect('administrator/index.php?option=com_gginterface&view=newedizionemailalert',JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error'));
        }
    }

    private function getUtentiNuovaEdizione($id_corso){

        $db = JFactory::getDbo();

        try {

            if($id_corso) {

                $query = $db->getQuery(true);
                $query->select('anagrafica.*');
                $query->from('crg_gg_report_users as anagrafica');
                $query->join('inner','crg_user_usergroup_map ON  crg_user_usergroup_map.user_id = anagrafica.id_user');
                $query->join('inner','crg_gg_usergroup_map ON crg_user_usergroup_map.group_id = crg_gg_usergroup_map.idgruppo');
                $query->where(' crg_gg_usergroup_map.idunita ='.$id_corso);
                $db->setQuery($query);
                $result= $db->loadObjectList();
                return $result;

            }
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE GET UTENTI SCADENZA CORSI', 0, 1, 0);

        }



    }

    private function elencoCorsiPerTipoScadenza($tipo){

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
                        //$query->select('anagrafica.*');
                        $query->select('anagrafica.cognome,(select percentuale_completamento from crg_gg_view_carige_learning_batch where id_user=anagrafica.id_user and id_corso='.$corso->id.') as completamento');
                        $query->from('#__gg_report_users as anagrafica');
                        $query->join('inner', '#__user_usergroup_map as um on anagrafica.id_user=um.user_id');
                        $query->join('inner', '#__gg_usergroup_map as m on m.idgruppo=um.group_id');
                        $query->where('m.idunita=' . $corso->id . ' and anagrafica.id not in ( select id_anagrafica from #__gg_view_stato_user_corso where id_corso=' . $corso->id . ' and stato=1)');


                        //echo $query.'<br>';
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

    private function sendMailAlertScadenzaCorsi($corso){

        try {
            $oggettomail = 'avviso scadenza corso: ';
            $testomail = " ricevi questa mail per l'approssimarsi della scadenza del corso in oggetto, che non hai ancora completato. Ti invitiamo a completare lo stesso al fine di raggiungere i tuoi obiettivi formativi";
            $result = $this->getUtentiInScadenzaCorso($corso);
            if ($result['rows'] != null) {
                $i=0;
                foreach ($result['rows'] as $row) {
                    $to = json_decode($row->fields)->email;
                    if($i<4) {
                        $this->sendMail($oggettomail. " " . $corso->titolo,$testomail,$to,$row->cognome);
                    }
                       $i++;
                }
            }
            $this->updateLastmail($corso);
            DEBUGG::log('corso:' . $corso->titolo , 'INVIO MAIL', 0, 1, 0);
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE INVIO MAIL', 0, 1, 0);
        }
    }

    private function utente_abilitato($userid)
    {

        try {
            $db = JFactory::getDbo();
            $query = "select count(*) from cc_crg_ggif_utenti_non_abilitati_contatori where id_utente=" . $userid;
            $db->setQuery($query);


            if ($db->loadResult() == 0) {

                return true;
            } else {

                return false;
            }
        }catch (Exception $e){

            echo $e->getMessage();
        }
    }

    private function utente_abilitato_esma($userid)
    {

        $db = JFactory::getDbo();
        $query = "select count(*) from cc_crg_ggif_utenti_non_abilitati_esma where id_utente=". $userid;
        $db->setQuery($query);


        if ($db->loadResult() == 0) {

            return true;
        } else {

            return false;
        }

    }

    private function ore_esma($userid)
    {
        $db = JFactory::getDbo();
        //$contenuti_esma=getContenutiTema(1);

        //$corsi_esma=getCorsiTema(1);
        $query = "select sum(durata)/3600 from crg_gg_report as r INNER JOIN crg_gg_contenuti as c on r.id_contenuto=c.id inner join crg_ggif_edizione_unita_gruppo as e on e.id_unita=r.id_corso 
              where r.stato=1 and e.id_tema like '%1%' and r.id_utente=".$userid;
        // echo $query;
        $db->setQuery($query);
        $ore_fad=$db->loadResult();

        $query = "select sum(res.ore) from cc_crg_ggif_logres as res where id_utente=" . $userid . " and res.id_tema=1";
        // echo $query;
        $db->setQuery($query);
        $ore_res=$db->loadResult();
        //echo 'ore_fad_esma:'.$ore_fad.'ore_esma_res:'.$ore_res;
        return $ore_fad+$ore_res;
    }

    private function ore_ivass($userid){
        $db = JFactory::getDbo();
        //$contenuti_ivass=getContenutiTema(2);
        //$corsi_ivass=getCorsiTema(2);
        $query = "select sum(durata)/3600 from crg_gg_report as r INNER JOIN crg_gg_contenuti as c on r.id_contenuto=c.id inner join crg_ggif_edizione_unita_gruppo as e on e.id_unita=r.id_corso 
              where r.stato=1 and e.id_tema like '%2%' and r.id_utente=".$userid;
        $db->setQuery($query);
        $ore_fad=$db->loadResult();

        $query="select sum(res.ore) from cc_crg_ggif_logres as res where id_utente=".$userid." and res.id_tema=2";
        $db->setQuery($query);
        $ore_res=$db->loadResult();
        //echo 'ore_fad_ivass:'.$ore_fad.'ore_res_ivass:'.$ore_res;
        return $ore_res+$ore_fad;
    }

    private function scadenza_ivass($userid){

        $db = JFactory::getDbo();
        $query="select temabiennio from cc_crg_ggif_scadenza_temabiennio where id = (select id_scadenza_temabiennio from cc_crg_ggif_corrispondenza_utente_ivass where id_utente=".$userid.")";
        $db->setQuery($query);

        return $db->loadResult();
    }

    private function totale_ivass($userid){

        $db = JFactory::getDbo();
        $query="select ore from cc_crg_ggif_corrispondenza_utente_ivass where id_utente=".$userid;

        $db->setQuery($query);
        return $db->loadResult();

    }


    private function sendMailCruscotto($ore_esma, $ore_ivass, $tot_ivass, $scadenza_ivass,$user){


    }



}

