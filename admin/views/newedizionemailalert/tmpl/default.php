<style>
    .progress-bar {
        background-color: green;

    }

    .override-inputbox{

        width: 700px;
    }

    .controls{

        margin-left:0px;
    }

</style>
<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<form action="<?php echo JRoute::_('index.php?option=com_gginterface'); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate form-horizontal">

    <div class="row-fluid"><?php  echo $this->form->renderField('categoria'); ?></div>
    <div class="row-fluid"><?php echo $this->form->renderField('alert_mail_text'); ?></div>
    <div class="row-fluid"><a id="sendButton" class="btn active btn-success" onclick="inviaMail()"><?php echo JText::_('Invia Mail'); ?></a></div>
</form>


<script type="text/javascript">

    var testo_invio;
    var id_corso_invio;
    var testo_corso_invio;

    jQuery("#jform_categoria").change(function() {


        //console.log(jQuery('#jform_categoria option:selected').text());
        testo_corso_invio=jQuery('#jform_categoria option:selected').text().replace(/\-/g," ");
        id_corso_invio=jQuery('#jform_categoria option:selected').val();
        testo_invio='Ciao!\n' +
            '\n' +
            'Oggi è uscito il corso '+testo_corso_invio+' , non restare indietro formati subito!\n' +
            '\n' +
            'Buon lavoro\n' +
            '\n' +
            'Cal'
        jQuery('#jform_alert_mail_text').val(testo_invio);
    });

    function inviaMail() {


        var ok=confirm('Attenzione: stai inviando una mail di avviso a tutti gli appartenenti al gruppo collegato al nuovo corso.');
        if(ok==true){

            testo_invio=document.getElementById('jform_alert_mail_text').value;
            console.log(testo_invio);
            testo_invio=testo_invio.replace(new RegExp('\r?\n','g'),'ggalleryBR')
            console.log(testo_invio);

//        location.href='../index.php?option=com_gginterface&task=Alertcarige.sendNewEdizioneMailAlert&id_corso='+id_corso_invio+'&testo_mail='+testo_invio;
            jQuery.when(jQuery.get('../index.php?option=com_gginterface&task=Alertcarige.sendNewEdizioneMailAlert&id_corso='+id_corso_invio+'&titolo='+testo_corso_invio+'&testo_mail='+testo_invio))

                .done(function(data){
                    //console.log(data);
                    if(data=='true'){
                        //Joomla.renderMessages({"success":["invio email avvenuto con successo!!"]});
                        alert ('invio avvenuto con successo, puoi procedere eventualmente con un altro invio');
                    }else{
                        alert(data.toString());
                    }
                }).fail(function(data){
                alert(data.toString());
            });
        }

    }

</script>