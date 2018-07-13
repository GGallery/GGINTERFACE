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
        <?php  echo $this->form->renderField('categoria'); ?>
    </div>

</form>


