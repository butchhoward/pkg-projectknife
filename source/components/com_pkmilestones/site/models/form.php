<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_pkmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2015-2016 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


use Joomla\Registry\Registry;

JLoader::register('PKmilestonesModelMilestone', JPATH_ADMINISTRATOR . '/components/com_pkmilestones/models/milestone.php');

class PKmilestonesModelForm extends PKmilestonesModelMilestone
{

}