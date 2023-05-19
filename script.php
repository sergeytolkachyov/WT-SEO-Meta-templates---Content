<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;

/**
 * Script file of HelloWorld component.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_helloWorldInstallerScript).
 *
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 *
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class plgSystemWt_seo_meta_templates_contentInstallerScript
{
	/**
	 * Runs just before any installation action is performed on the component.
	 * Verifications and pre-requisites should run in this function.
	 *
	 * @param   string     $type    - Type of PreFlight action. Possible values are:
	 *                              - * install
	 *                              - * update
	 *                              - * discover_install
	 * @param   \stdClass  $parent  - Parent object calling object.
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
        if ((new Version())->isCompatible('4.0') === false)
        {
            Factory::getApplication()->enqueueMessage('&#128546; <strong>WT SEO Meta templates - content</strong> plugin doesn\'t support Joomla versions <span class="alert-link">lower 4</span>. Your Joomla version is <span class="badge badge-important">'.(new Version())->getShortVersion().'</span>','error');
            return false;
        }
        if(!file_exists(JPATH_SITE.'/plugins/system/wt_seo_meta_templates/wt_seo_meta_templates.xml')){
            Factory::getApplication()->enqueueMessage('<strong>WT SEO Meta templates - content:</strong> Please, install WT SEO Meta templates plugin first', 'warning');
        }
	}
}