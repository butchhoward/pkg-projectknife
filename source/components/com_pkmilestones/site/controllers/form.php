<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_pkmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2015-2016 Tobias Kuhn. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;


class PKmilestonesControllerForm extends JControllerForm
{
	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 */
	protected $view_list = 'list';


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage()
    {
        $app    = JFactory::getApplication();
        $return = $app->input->get('return', null, 'default', 'base64');

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JRoute::_(PKmilestonesHelperRoute::getListRoute(), false);
        }
        else {
            return base64_decode($return);
        }
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function getModel($name = 'Form', $prefix = 'PKmilestonesModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string     $key    The name of the primary key of the URL variable.
     *
     * @return    boolean            True if access level checks pass, false otherwise.
     */
    public function cancel($key = 'id')
    {
        parent::cancel($key);

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());
    }


    /**
     * Method to edit an existing record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key
     *
     * @return    boolean               True if access level check and checkout passes, false otherwise.
     */
    public function edit($key = null, $urlVar = 'id')
    {
        $result = parent::edit($key, $urlVar);

        if (!$result) {
            $this->setRedirect($this->getReturnPage());
        }

        return $result;
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key       The name of the primary key of the URL variable.
     * @param     string     $urlVar    The name of the URL variable if different from the primary key.
     *
     * @return    boolean               True if successful, false otherwise.
     */
    public function save($key = null, $urlVar = 'id')
    {
        $result = parent::save($key, $urlVar);

        // If ok, redirect to the return page.
        if ($result) {
            $this->setRedirect($this->getReturnPage());
        }

        return $result;
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     integer    $recordId    The primary key id for the item.
     * @param     string     $urlVar      The name of the URL variable for the id.
     *
     * @return    string                  The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
    {
        $append = parent::getRedirectToItemAppend($recordId, $urlVar);
        $return = $this->input->get('return', null, 'default', 'base64');

        if (!empty($return) && JUri::isInternal(base64_decode($return))) {
            $append .= '&return=' . $return;
        }

        return $append;
    }
}