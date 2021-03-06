<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_pkmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2015-2017 Tobias Kuhn. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;


$user  = JFactory::getUser();
$app   = JFactory::getApplication();
$count = count($this->items);

$list_order       = $this->escape($this->state->get('list.ordering'));
$viewing_archived = ($this->state->get('filter.published') == 2);
$viewing_trashed  = ($this->state->get('filter.published') == -2);
$viewing_project  = ($this->state->get('filter.project_id') > 0);
$sorting_manual   = ($list_order == 'a.ordering' || $list_order == 'ordering');
$function         = $app->input->getCmd('function', 'jSelectMilestone');
$db_nulldate      = JFactory::getDbo()->getNullDate();

JHtml::_('actionsdropdown.' . ($viewing_archived ? 'unarchive' : 'archive'), '{cb}', 'milestones');
JHtml::_('actionsdropdown.' . ($viewing_trashed ? 'untrash' : 'trash'), '{cb}', 'milestones');
$html_actions = JHtml::_('actionsdropdown.render', '{title}');

$txt_edit    = JText::_('JACTION_EDIT');
$txt_project = JText::_('COM_PKPROJECTS_PROJECT');
$txt_no_cat  = JText::_('PKGLOBAL_UNCATEGORISED');
$txt_not_set = JText::_('PKGLOBAL_UNDEFINED');
$txt_datef   = JText::_('DATE_FORMAT_LC4');
$txt_author  = JText::_('JAUTHOR');
$txt_alias   = JText::_('JFIELD_ALIAS_LABEL');
$txt_order   = JHtml::tooltipText('JORDERINGDISABLED');
$txt_inht    = JHtml::tooltipText('PKGLOBAL_INHERITED_FROM_TASK');
$txt_inhp    = JHtml::tooltipText('COM_PKMILESTONES_INHERITED_FROM_PROJECT');


for ($i = 0; $i != $count; $i++)
{
    $item = $this->items[$i];

    // Title link
    $title = '<a href="index.php?option=com_pkmilestones&task=milestone.edit&id=' . intval($item->id) . '">'
           . $this->escape($item->title) . ' </a>';


    // Context info
    if (!empty($item->project_title) && !$viewing_project) {
        $context = $txt_project . ": " . $this->escape($item->project_title);
    }
    else {
        $context = '';
    }

    // Format start date
    if ($item->start_date_inherit && $item->start_date_task_id > 0) {
        $tip = '<strong>' . $txt_inht . ':</strong><br/>' . JHtmlString::truncate($this->escape($item->start_date_task_title), 32);

        $start_date = '<span class="hasTooltip" title="' . $tip . '" style="cursor:help;">'
                    . JHtml::_('date', $item->start_date, $txt_datef)
                    . ' <i class="icon-info-2"></i>'
                    . '</span>';
    }
    elseif (strcmp($item->start_date, $db_nulldate) !== 0) {
        $start_date = JHtml::_('date', $item->start_date, $txt_datef);
    }
    else {
        $start_date = $txt_not_set;
    }

    // Format due date
    if ($item->due_date_inherit && $item->due_date_task_id > 0) {
        $tip = '<strong>' . $txt_inht . ':</strong><br/>' . JHtmlString::truncate($this->escape($item->due_date_task_title), 32);

        $due_date = '<span class="hasTooltip" title="' . $tip . '" style="cursor:help;">'
                  . JHtml::_('date', $item->due_date, $txt_datef)
                  . ' <i class="icon-info-2 hidden-phone"></i>'
                  . '</span>';
    }
    elseif (strcmp($item->due_date, $db_nulldate) !== 0) {
        $due_date = JHtml::_('date', $item->due_date, $txt_datef);
    }
    else {
        $due_date = $txt_not_set;
    }

    // Format viewing access level
    if ($item->access_inherit) {
        $tip = '<strong>' . $txt_inhp . ':</strong><br/>' . JHtmlString::truncate($this->escape($item->project_title), 32);

        $access = '<span class="hasTooltip" title="' . $tip . '" style="cursor:help;">'
                . $item->access_level
                . ' <i class="icon-info-2 hidden-phone"></i>'
                . '</span>';
    }
    else {
        $access = $this->escape($item->access_level);
    }

    // Format progress bar
    if ($item->progress) {
        $progress = '<div class="progress">'
                  . '    <div class="bar" style="width: ' . $item->progress . '%">'
                  . '        <span class="label label-info pull-right">' . $item->progress . '%</span>'
                  . '   </div>'
                  . '</div>';
    }
    else {
        $progress = '<div class="progress"><span class="label">0%</span></div>';
    }
    ?>
    <tr class="row<?php echo ($i % 2); ?>">
        <td class="has-context">
            <div class="pull-left break-word">
                <a href="javascript:void(0);" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', '<?php echo $this->escape($item->project_id); ?>');">
                    <?php echo $this->escape($item->title); ?>
                </a>
                <div class="small hidden-phone muted"><?php echo $context; ?></div>
            </div>
        </td>
        <td class="nowrap hidden-phone">
            <?php echo $progress; ?>
        </td>
        <td class="nowrap">
            <?php echo $start_date; ?>
        </td>
        <td class="nowrap">
            <?php echo $due_date; ?>
        </td>
        <td class="nowrap hidden-phone">
            <?php echo $access; ?>
        </td>
        <td class="small hidden-phone">
            <?php echo $item->id; ?>
        </td>
    </tr>
    <?php
}