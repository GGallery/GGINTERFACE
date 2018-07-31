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

        $this->sendAlertScadenzaCorsi();
        //$this->sendCruscrotto();
    }

    private function sendMail($oggettomail,$testomail,$to,$cognome){

        try {
            //$to = $row->email;
            $to = 'a.petruzzella71@gmail.com';
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            /*$sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );*/
            $sender=array(
                'cal@carige.it',
                'CAL CARIGE'
            );
            $mailer->isHtml(true);
            $mailer->Encoding = 'base64';
            $mailer->setSender($sender);
            $mailer->addRecipient($to);
            $mailer->setSubject($oggettomail);
            $mailer->setBody('Gentile ' . $cognome . " " . $testomail);
            $send = $mailer->Send();
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE INVIO MAIL GENERALE', 0, 1, 0);
        }

    }
    public function sendAlertScadenzaCorsi(){//DEVI MODIFICARE QUESTA PROCEDURA, DIVIDENDO I DUE IF IN 4 IN MODO DA POTER  MANDARE UN NUOVO PARAMENTRO ALLA sendMailAlertScadenzaCorsi

        try {

            $corsi_primo_tipo = $this->elencoCorsiPerTipoScadenza(1);//prende soltanto i corsi del tipo 1, ovvero esma - ivass - etc

            foreach ($corsi_primo_tipo as $corso) {
                if ($corso->daysfromdata_fine<= 14) {

                    //echo 'PRIMO TIPO invio mail per :'.$corso->id.'<BR>';
                    $this->sendMailAlertScadenzaCorsi($corso,1);//VIENE MANDATA LA MAIL: IL CORSO SCADE TRA MENO DI 15 GIORNI
                }

                if( $corso->daysfromlastalert > 14){
                    $this->sendMailAlertScadenzaCorsi($corso,2);//VIENE MANDATA LA MAIL: SONO PASSATI PIU' DI 15 DALL'INVIO
                }
            }

            $corsi_secondo_tipo = $this->elencoCorsiPerTipoScadenza(2); //prende tutti gli altri
            foreach ($corsi_secondo_tipo as $corso_) {

                if ($corso_->daysfromdata_fine <= 30 && $corso_->daysfromlastalert > 6){
                    //echo 'SECONDO TIPO invio mail per :'.$corso_->id.'<BR>';
                    $this->sendMailAlertScadenzaCorsi($corso_,3);//VIENE MANDATA LA MAIL: IL CORSO SCADE TRA MENO DI 30 GIORNI E SONO PASSATI PIU' DI 6 DALL'INVIO;
                }

                if($corso_->daysfromdata_fine > 30 && $corso_->daysfromlastalert > 30){//SONO PASSATI PIU' 30, IL CORSO SACDE TRA PIU' DI 30 GG

                    $this->sendMailAlertScadenzaCorsi($corso_,4);
                }
            }
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE SEND_ALERT_SCADENZA_CORSI', 0, 1, 0);
        }
    }

    public function sendCruscrotto(){

        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__gg_report_users');
            $db->setQuery($query);
            $users = $db->loadObjectList();
            $i = 0;
            foreach ($users as $user) {


                $userid = $user->id_user;
                $messaggioesma=null;
                if ($this->utente_abilitato($userid)) {

                    if ($this->utente_abilitato_esma($userid)) {
                        $tot_esma = 30;
                        $ore_esma = $this->new_ore_esma($userid);
                        $percentuale_ore_esma = ($ore_esma / $tot_esma) * 100;
                        $messaggioesma=' hai totalizzato ' . $ore_esma . ' ESMA su 30 ore, scadenza 31/12/2018';

                    }
                    $ore_ivass = $this->new_ore_ivass($userid);
                    $scadenza_ivass = $this->scadenza_ivass($userid);
                    $tot_ivass = $this->totale_ivass($userid);
                    $oggettomail = 'situazione cruscotto formativo Carigelearning';
                    $to = json_decode($user->fields)->email;
                    $testomail =  $messaggioesma.' hai totalizzato '. $ore_ivass . ' su ' . $tot_ivass . ' scadenza ' . $scadenza_ivass;
                    if ($i < 8) {
                        $this->sendMail($oggettomail, $testomail, $to, $user->cognome);
                    }
                }


                $i++;
            }
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE INVIO MAIL CRUSCOTTO', 0, 1, 0);
        }
    }

    public function sendNewEdizioneMailAlert(){

        try {
            $id_corso=JRequest::getVar("id_corso");
            $testomail=JRequest::getVar("testo_mail");
            $testomail=str_replace('ggalleryBR','<br>',$testomail);
            $titolo=JRequest::getVar("titolo");
            $oggettomail = 'E\' online il corso '.$titolo.', collegati per primo!';
            $result = $this->getUtentiNuovaEdizione($id_corso);
            $i=0;
            //echo "elenco dei destinatari:<br>";
            foreach ($result as $row){
                $to = json_decode($row->fields)->email;
                if($i<1) {

                    $this->sendMail($oggettomail,$testomail,$to,$row->cognome);
                }
                $i++;
            }
//            JFactory::getApplication()->enqueueMessage(JText::_('MAIL OK'));
            //$this->setRedirect('administrator/index.php?option=com_gginterface&view=newedizionemailalert&extension=com_gginterface', JFactory::getApplication()->enqueueMessage(JText::_('MAIL OK')));
            DEBUGG::log('INVIO MAIL NUOVA EDIZIONE', 'INVIATE N°'.$i.' MAIL NUOVA EDIZIONE '.$titolo, 0, 1, 0);
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
            DEBUGG::log($ex->getMessage(), 'ERRORE GET UTENTI NUOVA EDIZIONE', 0, 1, 0);

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
                        $query->select('*');
                        $query->from('#__gg_report_users as anagrafica');
                        $query->join('inner', '#__user_usergroup_map as um on anagrafica.id_user=um.user_id');
                        $query->join('inner', '#__gg_usergroup_map as m on m.idgruppo=um.group_id');
                        $query->where('m.idunita=' . $corso->id . ' and anagrafica.id not in ( select id_anagrafica from #__gg_view_stato_user_corso where id_corso=' . $corso->id . ' and stato=1)');


                        //echo $query.'<br>';
                        //die;
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
        $query="UPDATE crg_ggif_edizione_unita_gruppo SET lastalert=now() where id_unita=".$corso->id;
        $db->setQuery($query);
        $db->execute();


    }

    private function sendMailAlertScadenzaCorsi($corso,$tipo){

        try {
            $testimail=$this->getTestiMail($tipo,$corso->titolo);
            $oggettomail = $testimail['oggetto'];
            $testomail = $testimail['testo'];
            $utentiscadenza = $this->getUtentiInScadenzaCorso($corso);
            $utentiesclusimail=$this->getUtentiEsclusiMail(1);

            $numeroutentiscadenza=count($utentiscadenza);
            $numeroutentiesclusimail=0;
            $numeroutentiesclusicruscotto=0;
            if ($utentiscadenza['rows'] != null) {
                $utenteinscadenzaindex=0;
                foreach ($utentiscadenza['rows'] as &$row) {
                    foreach ($utentiesclusimail as $utenteescluso) {
                        if ($utenteescluso->id_utente == $row->id_user) {
                            unset($utentiscadenza['rows'] [$utenteinscadenzaindex]);
                            $numeroutentiesclusimail++;
                        }
                    }

                    $utenteinscadenzaindex++;
                }
                $utentiesclusicruscotto=$this->getUtentiCorsoEsclusiCruscotto($utentiscadenza['rows'],$corso);

                $utenteinscadenzaindex=0;
                foreach ($utentiscadenza['rows'] as &$row) {
                    foreach ($utentiesclusicruscotto as $utenteesclusocruscotto) {
                        if ($utenteesclusocruscotto->id_utente == $row->id_user) {
                            unset($utentiscadenza['rows'] [$utenteinscadenzaindex]);
                            $numeroutentiesclusicruscotto++;
                        }
                    }
                    $utenteinscadenzaindex++;
                }
                $i=0;
                foreach ($utentiscadenza['rows'] as &$row) {
                    $to = json_decode($row->fields)->email;
                    if ($i < 1) {
                        $this->sendMail($oggettomail . " " . $corso->titolo, $testomail, $to, $row->cognome);
                    }
                    $i++;

                }
            }
            $this->updateLastmail($corso);
            DEBUGG::log('corso:' . $corso->titolo , 'ALERT SCADENZE INVIO MAIL N° '.$numeroutentiscadenza.' tra questi esclusi N°:'.$numeroutentiesclusimail.' per esclusione mail e N:'.$numeroutentiesclusicruscotto.' per esclusione cruscotto', 0, 1, 0);
        }catch (Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE INVIO MAIL', 0, 1, 0);
        }
    }

    function utente_abilitato($userid)
    {

        $db = JFactory::getDbo();
        $query = "select count(*) from cc_crg_ggif_utenti_non_abilitati_contatori where id_utente=". $userid;
        $db->setQuery($query);


        if ($db->loadResult() == 0) {

            return true;
        } else {

            return false;
        }

    }

    function utente_abilitato_esma($userid)
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

    private function getUtentiEsclusiMail($regola)
    {

        try {
            $utenti_blocco_mail = null;
            $db = JFactory::getDbo();
            switch ($regola) {
                case 1://NON DEVONO RICEVERE LA MAIL TUTTI GLI UTENTI ESCLUSI IN APPOSITA TABELLA
                    $query = $db->getQuery(true);
                    $query->select('id_utente');
                    $query->from('cc_crg_ggif_utenti_blocco_mail');
                    $db->setQuery($query);
                    $utenti_blocco_mail = $db->loadObjectList();
                    break;
            }
            return $utenti_blocco_mail;
        }catch(Exception $ex){

            DEBUGG::log($ex->getMessage(), 'ERRORE IN GET_UTENTI_ESCLUSI_MAIL', 0, 1, 0);

        }
    }

    private function getUtentiCorsoEsclusiCruscotto($users,$corso){

        try {
            $utentiesclusicruscotto = null;
            switch ($corso->id_tema) {

                case null:
                    return null;
                    break;
                case '1':
                    foreach ($users as $user) {

                        if ($this->new_ore_esma($user->id_user) > 29)
                            array_push($utentiesclusicruscotto, $user);
                    }
                    break;
                case '2':
                    foreach ($users as $user) {

                        if ($this->new_ore_ivass($user->id_user) > 44)
                            array_push($utentiesclusicruscotto, $users);
                    }
                    break;
                case '1,2':
                    foreach ($users as $user) {
                        if ($this->new_ore_esma($user->id_user) > 29 && $this->new_ore_ivass($user->id_user) > 44)
                            array_push($utentiesclusicruscotto, $users);
                    }
                    break;


            }
            return $utentiesclusicruscotto;
        }catch(Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE IN GET_UTENTI_ESCLUSI_CRUSCOTTO', 0, 1, 0);

        }

    }

    private function getTestiMail($tipo, $titolo){

        switch ($tipo){

            case 1:
                $testimail['oggetto']='Il corso '.$titolo.' ti aspetta su Carigelearing, cogli l\'attimo e aggiornati!';
                $testimail['testo']='Ciao!<BR>Ricordati di fruire del corso : '.$titolo.' valido per il tuo aggiornamento IVASS/ESMA.<br>Vuoi sapere quante ore mancano per essere aggiornato sulla formazione annuale obbligatoria? Nessun problema, 
                                    clicca su “home” e il cruscotto fruizioni è pronto a risponderti!<br>Buon corso!<br>Cal';
                return $testimail;
                break;
            case 2:
                $testimail['oggetto']=' Manca poco, il corso '.$titolo.' sta per scadere, non perdere l\'occasione di aggiornare le tue abilitazioni';
                $testimail['testo']='Ciao,<br>sono tornato per avvertirti che manca poco alla scadenza del WBT '.$titolo.'!<br>Il tuo cruscotto fruizioni segnala che mancano delle ore per il tuo aggiornamento IVASS/ESMA e quindi da oggi te lo ricorderò ogni giorno ...
                                     <br>Buon lavoro e buon corso!Cal';
                return $testimail;
                break;
            case 3:
                $testimail['oggetto']='Hai già visto il corso '.$titolo.'? Clicca su Carigelearning!';
                $testimail['testo']='Ciao sono Cal,<br>voglio ricordarti il corso “XXXX”, c’è ancora tempo ma una volta al mese è meglio ricordartelo o no?<br>Perché il tempo passa in fretta, si sa!<br>Non perdere l’occasione, formati subito!<br>Cal';
                return $testimail;
                break;
            case 4:
                $testimail['oggetto']='Il tempo stringe, collegati su Carigelearning il corso '.$titolo.' sta per scadere';
                $testimail['testo']='Ciao!!<br>Sono tornato per avvertirti che  il corso '.$titolo.' sta per scadere: collegati subito, perché la formazione obbligatoria è… “obbligatoria”!!<br>Buon lavoro e buon corso!<br>Cal';
                return $testimail;
                break;
        }
    }

    function scadenza_ivass($userid){

        $db = JFactory::getDbo();
        $query="select temabiennio from cc_crg_ggif_scadenza_temabiennio where id = (select id_scadenza_temabiennio from cc_crg_ggif_corrispondenza_utente_ivass where id_utente=".$userid.")";
        $db->setQuery($query);

        return $db->loadResult();
    }

    function totale_ivass($userid){

        $db = JFactory::getDbo();
        $query="select ore from cc_crg_ggif_corrispondenza_utente_ivass where id_utente=".$userid;

        $db->setQuery($query);
        return $db->loadResult();

    }

    function new_ore_ivass($userid){

        try {
            $db = JFactory::getDbo();
            //$contenuti_ivass=getContenutiTema(2);
            //$corsi_ivass=getCorsiTema(2);
            /*$query = "select e.anno as anno, sum(durata)/3600 as ore from crg_gg_report as r INNER JOIN crg_gg_contenuti as c on r.id_contenuto=c.id inner join crg_ggif_edizione_unita_gruppo as e on e.id_unita=r.id_corso
                      where r.stato=1 and e.id_tema like '%2%' and r.id_utente=".$userid." group by e.anno";*/
            $query = "select YEAR(u.data_fine) as anno, sum(durata)/3600 as ore from crg_gg_report as r INNER JOIN crg_gg_contenuti as c on r.id_contenuto=c.id inner join crg_ggif_edizione_unita_gruppo as e on e.id_unita=r.id_corso
            inner join crg_gg_unit as u on u.id=r.id_corso inner join crg_gg_view_stato_user_corso as v on r.id_corso=v.id_corso and r.id_anagrafica=v.id_anagrafica
            where v.stato=1 and r.stato=1 and e.id_tema like '%2%' and r.id_utente=" . $userid . " group by YEAR(u.data_fine)";
            $db->setQuery($query);
            $ore_fad = $db->loadAssocList('anno');

            $query = "select YEAR(data_corso) as anno, sum(res.ore) as ore from cc_crg_ggif_logres as res where id_utente=" . $userid . " and res.id_tema=2 group by YEAR(data_corso)";
            $db->setQuery($query);
            $ore_res = $db->loadAssocList('anno');
            //echo 'ore_fad_ivass:'.$ore_fad.'ore_res_ivass:'.$ore_res;
            return $this->aggiusta45($ore_fad, $ore_res);
        }catch(Exception $ex){
            DEBUGG::log($ex->getMessage(), 'ERRORE IN NEW_ORE_IVASS', 0, 1, 0);

        }
    }


    function new_ore_esma($userid)
    {
        try {

            $db = JFactory::getDbo();
            //$contenuti_esma=getContenutiTema(1);

            //$corsi_esma=getCorsiTema(1);
            $query = "select sum(durata)/3600 as ore from crg_gg_report as r INNER JOIN crg_gg_contenuti as c on r.id_contenuto=c.id inner join crg_ggif_edizione_unita_gruppo as e on e.id_unita=r.id_corso 
              inner join crg_gg_view_stato_user_corso as v on r.id_corso=v.id_corso and r.id_anagrafica=v.id_anagrafica where v.stato=1 and r.stato=1 and e.id_tema like '%1%' and r.id_utente=" . $userid;
            // echo $query;
            $db->setQuery($query);
            $ore_fad = $db->loadResult();

            $query = "select sum(res.ore) as ore from cc_crg_ggif_logres as res where id_utente=" . $userid . " and res.id_tema=1 and data_corso>STR_TO_DATE(CONCAT(YEAR(DATE_ADD(NOW(), INTERVAL -1 YEAR)),'-',MONTH('2000-09-01'),'-',DAY('2000-09-01')),'%Y-%m-%d' )";
            // echo $query;
            $db->setQuery($query);
            $ore_res = $db->loadResult();
            //echo 'ore_fad_esma:'.$ore_fad.'ore_esma_res:'.$ore_res;
            return $ore_fad + $ore_res;
        }catch(Exception $ex){
            DEBUGG::log($query, 'ERRORE IN NEW_ORE_ESMA', 0, 1, 0);

        }
    }

    function aggiusta45($ore_fad,$ore_res){

        //var_dump($ore_fad);var_dump($ore_res);die;
        //$anni=array(2017,2018,2019,2020,2021,2022);
        //var_dump(array_column($ore_fad,'anno'));var_dump(array_column($ore_res,'anno'));die;
        $anni=array_merge(array_column($ore_fad,'anno'),array_column($ore_res,'anno'));
        $totale_array=array();
        //var_dump($anni);die;
        foreach ($anni as $anno){

            //echo $anno . ' : fad:' . $ore_fad[$anno] . ' res:' . $ore_res[$anno];
            $totale_array[$anno] = ($ore_fad[$anno]['ore'] + $ore_res[$anno]['ore'] < 46 ? $ore_fad[$anno]['ore'] + $ore_res[$anno]['ore'] : 45);

        }
        $totale=0;
        foreach ($totale_array as $tot){
            $totale=$totale+$tot;
        }
        return $totale;
    }

}

