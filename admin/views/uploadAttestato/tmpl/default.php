<style>
    .progress-bar {
        background-color: green;

    }

    .override-inputbox{

        width: 700px;
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
    <div class="row-fluid">
        <div class="span6">


            <input id="path" type="hidden" name="path" value="<?php echo $_SERVER['DOCUMENT_ROOT']; ?>/mediagg/attestati/" size = "50px">
            <input id="subpath" type="hidden" name="subpath" value="" size = "50px">
            <input id="url" type="hidden" name="url" value="<?php echo $_SERVER['SERVER_NAME']; ?>/mediagg/attestati/" size = "50px">
            <input id="tipologia" type="hidden" name="tipologia" value="" size = "50px">

            <!-- QUI INIZIA IL FILE UPLOAD     -->


                <div class="containerUpload">Scegli i file da caricare, puoi effettuare una scelta multipla<br><br>
                    <?php //echo JHtml::_('sliders.panel', JText::_('Caricamento file multimediali'), 'Upload'); ?>
                    <!-- The fileinput-button span is used to style the file input field as button -->
                    <span class="btn btn-success fileinput-button">
                        <i class="glyphicon glyphicon-plus"></i>
                        <span><?php echo JText::_('Carica File'); ?></span>
                        <!-- The file input field used as target for the file upload widget -->
                        <input id="fileuploadattestati" type="file" name="files[]" multiple>
                    </span>
                    <br>
                    <br>
                    <!-- The global progress bar -->
                    <div id="progress" class="progress">
                        <div class="progress-bar progress-bar-success"></div>
                    </div>
                    <!-- The container for the uploaded files -->
                    <div id="files" class="files"></div>
                    <br>

                </div>
               
            <!-- QUI FINISCE IL FILE UPLOAD -->
        </div>
    </div>

</form>


