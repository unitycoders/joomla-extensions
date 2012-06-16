<?php
 /**
  * @version        
  * @copyright  Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
  * @license  GNU General Public License version 2 or later; see LICENSE.txt
  */
 
 defined('JPATH_BASE') or die;
 
  /**
   * Unity Coders Profile Data.
   *
   * @package       Joomla.Plugins
   * @subpackage    user.profile
   * @version       1.6
   */
  class plgUserUCProfile extends JPlugin
  {

    /**
     * @param   string  The context for the data
     * @param   int     The user id
     * @param   object
     * @return  boolean
     * @since   1.6
     */
    function onContentPrepareData($context, $data)
    {
        // Check we are manipulating a valid form.
        if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile'))){
            return true;
        }
 
        $userId = isset($data->id) ? $data->id : 0;
 
        // Load the profile data from the database.
        $db = &JFactory::getDbo();
        $db->setQuery(
            'SELECT profile_key, profile_value FROM #__user_profiles' .
            ' WHERE user_id = '.(int) $userId .
            ' AND profile_key LIKE \'ucprofile.%\'' .
            ' ORDER BY ordering'
        );
        $results = $db->loadRowList();
 
        // Check for a database error.
        if ($db->getErrorNum()) {
            $this->_subject->setError($db->getErrorMsg());
            return false;
        }
 
        // Merge the profile data.
        $data->ucprofile = array();
        foreach ($results as $v) {
            $k = str_replace('ucprofile.', '', $v[0]);
            $data->ucprofile[$k] = $v[1];
        }
 
        return true;
    }
 
    /**
     * @param   JForm   The form to be altered.
     * @param   array   The associated data for the form.
     * @return  boolean
     * @since   1.6
     */
    function onContentPrepareForm($form, $data)
    {
        // Load user_profile plugin language
        $lang = JFactory::getLanguage();
        $lang->load('plg_user_ucprofile', JPATH_ADMINISTRATOR);
 
        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }
        // Check we are manipulating a valid form.
        if (!in_array($form->getName(), array('com_users.profile','com_users.registration','com_users.user','com_admin.profile'))) {
            return true;
        }
        if ($form->getName()=='com_users.profile')
        {
            // Add the profile fields to the form.
            JForm::addFormPath(dirname(__FILE__).'/profiles');
            $form->loadFile('profile', false);
 
            // Toggle whether the something field is required.
            if ($this->params->get('profile-require_something', 1) > 0) {
                $form->setFieldAttribute('something', 'required', $this->params->get('profile-require_something') == 2, 'ucprofile');
            } else {
                $form->removeField('something', 'ucprofile');
            }
        }
 
        //In this example, we treat the frontend registration and the back end user create or edit as the same. 
        elseif ($form->getName()=='com_users.registration' || $form->getName()=='com_users.user' )
        {       
            // Add the registration fields to the form.
            JForm::addFormPath(dirname(__FILE__).'/profiles');
            $form->loadFile('profile', false);
 
            // Toggle whether the something field is required.
            if ($this->params->get('register-require_something', 1) > 0) {
                $form->setFieldAttribute('something', 'required', $this->params->get('register-require_something') == 2, 'ucprofile');
            } else {
                $form->removeField('something', 'ucprofile');
            }
        }           
    }
 
    function onUserAfterSave($data, $isNew, $result, $error)
    {
        $userId = JArrayHelper::getValue($data, 'id', 0, 'int');
 
        if ($userId && $result && isset($data['ucprofile']) && (count($data['ucprofile'])))
        {
            try
            {
                $db = &JFactory::getDbo();
                $db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId.' AND profile_key LIKE \'ucprofile.%\'');
                if (!$db->query()) {
                    throw new Exception($db->getErrorMsg());
                }
 
                $tuples = array();
                $order  = 1;
                foreach ($data['ucprofile'] as $k => $v) {
                    $tuples[] = '('.$userId.', '.$db->quote('ucprofile.'.$k).','.$db->quote($v).', '.$order++.')';
                }
 
                $db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(',', $tuples));
                if (!$db->query()) {
                    throw new Exception($db->getErrorMsg());
                }
            }
            catch (JException $e) {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }
 
        return true;
    }
 
    /**
     * Remove all user profile information for the given user ID
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array       $user       Holds the user data
     * @param   boolean     $success    True if user was succesfully stored in the database
     * @param   string      $msg        Message
     */
    function onUserAfterDelete($user, $success, $msg)
    {
        if (!$success) {
            return false;
        }
 
        $userId = JArrayHelper::getValue($user, 'id', 0, 'int');
 
        if ($userId)
        {
            try
            {
                $db = JFactory::getDbo();
                $db->setQuery(
                    'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
                    " AND profile_key LIKE 'ucprofile.%'"
                );
 
                if (!$db->query()) {
                    throw new Exception($db->getErrorMsg());
                }
            }
            catch (JException $e)
            {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }
 
        return true;
    }
 
 
 }
?>
