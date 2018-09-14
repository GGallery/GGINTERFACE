<?php
/**
 * @package		Joomla.Tutorials
 * @subpackage	Component
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		License GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>


<form action="<?php echo JRoute::_('index.php?option=com_gginterface&view=bookcorso&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate form-horizontal">



    <div class="row-fluid">
        <div class="span12">

            <div class="span4">
                <div class="row-fluid">
                    <?php echo $this->form->renderField('id'); ?>
                </div>


                <div class="row-fluid">
                    <?php echo $this->form->renderField('titolo'); ?>
                </div>


                <div class="row-fluid">
                    <?php

                        echo $this->form->renderField('descrizione'); ?>
                </div>


                <div class="row-fluid">
                    <?php

                            echo $this->form->renderField('data_inizio');

                    ?>

                </div>


                <div class="row-fluid">
                    <?php
                    if($this->item->id)
                        echo $this->form->renderField('data_fine'); ?>
                </div>




            </fieldset>
            <div>
                <input type="hidden" name="task" value="bookcorso.edit" />
                <?php echo JHtml::_('form.token'); ?>
            </div>

        </div>
    </div>




    <input id="idelemento" type="hidden" name="idelemento" value="<?php echo $this->item->id; ?>" size = "150px">


</form>