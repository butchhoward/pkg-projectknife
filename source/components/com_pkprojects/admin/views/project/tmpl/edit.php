<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_pkprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2015-2017 Tobias Kuhn. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;


JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app    = JFactory::getApplication();
$input  = $app->input;
$params = JComponentHelper::getParams('com_pkprojects');

JFactory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task)
    {
        if (task == "project.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
        {
            ' . $this->form->getField('description')->save() . '
            Joomla.submitform(task, document.getElementById("item-form"));
        }
    };
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_pkprojects&view=project&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="form-inline form-inline-header">
    	<?php
    	echo $this->form->renderField('title');
        echo $this->form->renderField('alias');
    	?>
    </div>
    <p></p>
    <div class="form-horizontal">
        <?php
            echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'description'));
            echo JHtml::_('bootstrap.addTab', 'myTab', 'description', JText::_('JGLOBAL_DESCRIPTION', true));
            ?>
            <div class="row-fluid">
                <div class="12">
                    <fieldset class="adminform">
                        <?php echo $this->form->getInput('description'); ?>
                    </fieldset>
                </div>
            </div>
        <?php
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('PKGLOBAL_PUBLISHING', true));
            ?>
            <div class="row-fluid form-horizontal">
                <div class="span4">
                    <?php
                    $fields = $this->form->getFieldset('publishing-left-col');

                    foreach ($fields as $field)
                    {
                        echo $field->renderField();
                    }
                    ?>
                </div>
                <div class="span4">
                    <?php
                    $fields = $this->form->getFieldset('publishing-middle-col');

                    foreach ($fields as $field)
                    {
                        echo $field->renderField();
                    }
                    ?>
                </div>
                <div class="span4">
                    <?php
                    $fields = $this->form->getFieldset('publishing-right-col');

                    foreach ($fields as $field)
                    {
                        echo $field->renderField();
                    }
                    ?>
                </div>
            </div>
        <?php
            echo JHtml::_('bootstrap.endTab');

            $fieldsets = $this->form->getFieldsets();
            $ignore    = array('publishing-left-col', 'publishing-middle-col', 'publishing-right-col');
            $fields    = array();

            $fieldset_title = "";

            foreach ($fieldsets AS $fieldset)
            {
                if (in_array($fieldset->name, $ignore)) {
                    continue;
                }

                if (!empty($fieldset->label)) {
                    $fieldset_title = JText::_($fieldset->label);
                }
                else {
                    $fieldset_title = JText::_('COM_PKPROJECTS_PROJECT_TAB_' . strtoupper($fieldset->name));
                }

                echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, $fieldset_title);

                if (isset($fieldset->description) && trim($fieldset->description)) {
        			echo '<p class="alert alert-info">' . $this->escape(JText::_($fieldset->description)) . '</p>';
        		}
                ?>
                <div class="row-fluid form-horizontal-desktop">
                    <div class="span12">
                        <?php
                        $fields = $this->form->getFieldset($fieldset->name);

                        foreach ($fields as $field)
                        {
                            echo $field->renderField();
                        }
                        ?>
                    </div>
                </div>
                <?php
                echo JHtml::_('bootstrap.endTab');
            }

            echo JHtml::_('bootstrap.addTab', 'myTab', 'group-permissions', JText::_('COM_PKPROJECTS_PROJECT_TAB_GROUP_PERMISSIONS', true));
            ?>
            <div class="row-fluid form-horizontal-desktop">
                    <div class="span12">
                    <?php
                    echo $this->form->renderField('access');
                    echo $this->form->getInput('rules');
                    ?>
                    </div>
            </div>
            <?php
            echo JHtml::_('bootstrap.endTab');

            echo JHtml::_('bootstrap.addTab', 'myTab', 'user-permissions', JText::_('COM_PKPROJECTS_PROJECT_TAB_USER_PERMISSIONS', true));
            ?>
            <p class="alert alert-info"><?php echo JText::_('COM_PKPROJECTS_PROJECT_TAB_USER_PERMISSIONS_DESC'); ?></p>
            <?php
            echo $this->form->getInput('userrules');
            echo JHtml::_('bootstrap.endTab');

            echo JHtml::_('bootstrap.endTabSet');
        ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
