<?php
/**
 * @package      pkg_projectknife
 * @subpackage   com_pktasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2015-2017 Tobias Kuhn. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;


class PKTasksControllerForm extends JControllerForm
{
    /**
     * The URL view list variable.
     *
     * @var    string
     */
    protected $view_list = 'list';


    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        if (isset($data['project_id'])) {
            $pid = (int) $data['project_id'];
        }
        else {
            $pid = PKApplicationHelper::getProjectId();

            if (!$pid) {
                $pid = 'any';
            }
        }

        return PKUserHelper::authProject('task.create', $pid);
    }


    /**
     * Method to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key; default is id.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        $id  = (isset($data[$key])) ? intval($data[$key]) : 0;

        if (isset($data['project_id'])) {
            $pid = (int) $data['project_id'];
        }
        else {
            if ($id) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                $query->select('project_id')
                      ->from('#__pk_tasks')
                      ->where('id = ' . (int) $id);

                $db->setQuery($query);
                $pid = (int) $db->loadResult();
            }
            else {
                return false;
            }
        }

        if (!$id) {
            return PKUserHelper::authProject('task.create', $pid);
        }

        // Check "edit" permission
        if (PKUserHelper::authProject('task.edit', $pid)) {
            return true;
        }


        // Fall back to "edit.own" permission check
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('created_by')
              ->from('#__pk_tasks')
              ->where('id = ' . $id);

        $db->setQuery($query);
        $author = (int) $db->loadResult();

        $can_edit_own = PKUserHelper::authProject('task.edit.own', $pid);

        return ($can_edit_own && $user->id > 0 && $user->id == $author);
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
    public function getModel($name = 'Form', $prefix = 'PKTasksModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }


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
            return JRoute::_(PKTasksHelperRoute::getListRoute(), false);
        }
        else {
            return base64_decode($return);
        }
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
        $data = $this->input->post->get('jform', array(), 'array');
        $id   = (isset($data['id']) ? intval($data['id']) : 0);

        if ($id || !isset($data['progress']) || !isset($data['predecessors'])) {
            $result = parent::save($key, $urlVar);

            // If ok, redirect to the return page.
            if ($result && $this->getTask() != 'save2new') {
                $this->setRedirect($this->getReturnPage());
            }

            return $result;
        }


        // Check on progress
        $progress = (int) $data['progress'];

        if (!$progress) {
            return parent::save($key, $urlVar);
        }

        $predecessors = $data['predecessors'];
        JArrayHelper::toInteger($predecessors);

        if (!count($predecessors)) {
            $result = parent::save($key, $urlVar);

            // If ok, redirect to the return page.
            if ($result && $this->getTask() != 'save2new') {
                $this->setRedirect($this->getReturnPage());
            }

            return $result;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title')
              ->from('#__pk_tasks')
              ->where('id IN(' . implode(', ', $predecessors) . ')')
              ->where('progress < 100')
              ->where('published > 0')
              ->order('title ASC');

        $db->setQuery($query);
        $blocking  = $db->loadColumn();

        if (count($blocking)) {
            // Set out a message that the progress cannot be changed
            $tasks = implode(', ', $blocking);
            $app   = JFactory::getApplication();
            $app->enqueueMessage(JText::sprintf('COM_PKTASKS_TASK_PROGRESS_BLOCKED_BY_PREDECESSORS', $tasks));
            unset($data['progress']);
        }

        $result = parent::save($key, $urlVar);

        // If ok, redirect to the return page.
        if ($result && $this->getTask() != 'save2new') {
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
