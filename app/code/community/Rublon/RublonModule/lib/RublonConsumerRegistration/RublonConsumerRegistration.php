<?php
/**
 * 2013 Rublon
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Rublon to newer
 * versions in the future. If you wish to customize Rublon for your
 * needs please contact us at support@rublon.com for more information.
 *
 *  @author    Rublon <support@rublon.com>
 *  @copyright 2013 Adips
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  Property of Adips Sp. z o. o., Poland
 */

require_once 'RublonConsumerRegistrationTemplate.php';

class RublonConsumerRegistration extends RublonConsumerRegistrationTemplate {	
		
	
	/**
	 * Final method when registration process was successful
	 *
	 * Clean some variables.
	 * Update Rublon using 'systemToken' and 'secretKey' from
	 * successfully registered project and to Rublon setting
	 * page
	 *
	 * @return void
	 */
	protected function finalSuccess() {

		parent::finalSuccess();
		
		$adminProfileId = $this->getAdminProfileId();
		$this->updateRublonSettings();

		$currentUser = Context::getContext()->employee->id;;
		if (!empty($adminProfileId) && !RublonHelper::isUserProtected($currentUser))
			$success = RublonHelper::connectRublon2Factor($currentUser, $adminProfileId);

		if ($success) {
			$updateMessage = 'PLUGIN_REGISTERED';
			RublonHelper::setMessage($updateMessage, 'updated', 'CR');
		} else {
			$errorCode = 'PLUGIN_REGISTERED_NO_PROTECTION';
			RublonHelper::setMessage($errorCode, 'error', 'CR');
		}		

		// send module's meta info
		$moduleMeta = RublonHelper::prepareModuleMeta();
		$moduleMeta['action'] = 'activation';
		RublonHelper::moduleHistoryRequest($moduleMeta);
				
		$this->_redirect(RublonHelper::getCRReturnPage());
	}
	
	/**
	 * Final method when registration process was failed
	 *
	 * Clean some variables.
	 * Set an error message, an redirect to Rublon setting
	 * page
	 *
	 * @param string $msg
	 * @return void
	 */
	protected function finalError($msg = NULL) {

		parent::finalError($msg);

		if (!$msg)
			$msg = $this->_get('error');

		$errorCode = 'API_ERROR';
		if (!empty($msg)) {
			if (stripos($msg, 'ERROR_CODE:') !== false) {
				$errorCode = str_replace('ERROR_CODE: ', '', $msg);
			}
		}
		RublonHelper::setMessage($errorCode, 'error', 'CR');
		
		$this->_redirect(RublonHelper::getRublonModuleLink());

	}


	/**
	 * Check whether user authenticated in current session can 
	 * perform administrative operations such as registering 
	 * the Rublon module.
	 *
	 * @return bool
	 */
	protected function isUserAuthorized() {		
		return Context::getContext()->employee->isLoggedBack();
	}

	/**
	 * Returns local-stored system token or NULL if empty.
	 * 
	 * @return string/null
	 */
	protected function getSystemToken() {
		$config = $this->getConfig();		
		return (isset($config['system_token']) ? $config['system_token'] : NULL);
	}
	
	/**
	 * Save system token to the local storage
	 * 
	 * Returns true/false on success/failure.
	 *
	 * @param string $systemToken
	 * @return bool
	 */
	protected function saveSystemToken($systemToken) {
		$config = $this->getConfig();
		$config['system_token'] = $systemToken;		
		
		return $this->_saveConfig($config);
	}


	/**
	 * Save profileId of the admin who activated the plugin
	 * 
	 * @param int $profileId
	 */
	protected function handleProfileId($profileId) {

		$config = $this->getConfig();
		$config['admin_profile_id'] = $profileId;
		return $this->_saveConfig($config);

	}


	/**
	 * Return profileId of the admin who activated the plugin, if it was received
	 * 
	 * @return string
	 */
	protected function getAdminProfileId() {

		$config = $this->getConfig();
		return (isset($config['admin_profile_id'])) ? $config['admin_profile_id'] : NULL;

	}


	/**
	 * Return local-stored secret key or NULL if empty.
	 * 
	 * @return string/null
	 */
	protected function getSecretKey() {
		$config = $this->getConfig();
		return (isset($config['secret_key']) ? $config['secret_key'] : NULL);
	}
	
	/**
	 * Save secret key to the local storage
	 *
	 * Returns true/false on success/failure.
	 *
	 * @param string $secretKey
	 * @return bool
	*/
	protected function saveSecretKey($secretKey) {
		$config = $this->getConfig();
		$config['secret_key'] = $secretKey;
		return $this->_saveConfig($config);
	}
	
	/**
	 * Returns local-stored temporary key or NULL if empty.
	 * Temporary key is used to sign communication with API instead of secret key which is not given.
	 * 
	 * @return string
	 */
	protected function getTempKey() {
		$config = $this->getConfig();
		return (isset($config['temp_key']) ? $config['temp_key'] : NULL);
	}

	/**
	 * Save temporary key to the local storage
	 *
	 * Returns true/false on success/failure.
	 *
	 * @param string $tempKey
	 * @return bool
	*/
	protected function saveTempKey($tempKey) {
		$config = $this->getConfig();
		$config['temp_key'] = $tempKey;
		return $this->_saveConfig($config['rublon']);
	}	
	
	/**
	 * Save given temporary key and process start time into local storage.
	 * 
	 * Returns true/false on success/failure.
	 * 
	 * @param string $tempKey
	 * @param int $startTime
	 * @return bool
	 */	
	protected function saveInitialParameters($tempKey, $startTime) {
		$config = $this->getConfig();
		$config['temp_key'] = $tempKey;
		$config['start_time'] = $startTime;				
		
		return $this->_saveConfig($config);
	}
	
	/**
	 * Return local-stored start time of the process or NULL if empty.
	 * Start time is used to validate lifetime of the process.
	 * 
	 * @return int/null
	 */
	protected function getStartTime() {
		$config = $this->getConfig();
		return (isset($config['start_time']) ? $config['start_time'] : NULL);
	}

	/**
	 * Get the communication URL of this Rublon module
	 * 
	 * Returns public URL address of the communication script.
	 * API server calls the communication URL to communicate with local system by REST or browser redirections.
	 * The communication URL is supplied to the API during initialization.
	 * 
	 * @return string
	 */
	protected function getCommunicationUrl() {

		return $this->_getRublonActionUrl('register');

	}
	
	/**
	 * Get project's public webroot URL address
	 * 
	 * Returns the main project URL address needful for registration consumer in API.
	 * 
	 * @return string
	 */
	protected function getProjectUrl() {		
		return RublonHelper::getProjectUrl();

	}
	
	/**
	 * Get the callback URL of this Rublon module
	 * 
	 * Returns public URL address of the Rublon consumer's callback script.
	 * API server calls the callback URL after valid authentication.
	 * The callback URL is needful for registration consumer in API.
	 * 
	 * @return string
	 */
	protected function getCallbackUrl() {

		return $this->_getRublonActionUrl('callback');

	}


	/**
	 * Prepare the URL for executing Rublon actions
	 * 
	 * @param string $action Action to be passed in the URL via GET
	 */
	private function _getRublonActionUrl($action) {

		$rublonActionUrl = RublonHelper::getProjectUrl();
		if (strpos($rublonActionUrl, '?') !== false)
			$rublonActionUrl .= 'controller=' . RublonHelper::getControllerName() . '&';
		else
			$rublonActionUrl .= '?controller=' . RublonHelper::getControllerName() . '&';
		$rublonActionUrl .= 'rublon='.$action;
		return $rublonActionUrl;		

	}
	
	/**
	 * Save a given data in a local-stored configuration.
	 * 
	 * @param array $data
	 * @return bool
	 */
	protected function _saveConfig($data) {	
		if (!is_array($data)) $data = array();				
										
		Configuration::updateValue('RUBLON_CONF', serialize($data));				
											
		return !empty($data)?true:false;
	}

	/**
	 * Sets the API domain for testing
	 * 
	 * @param string $domain API domain
	 */
	public function setDomain($domain) {
		$this->apiDomain = $domain;
	}

	/**
	 * Returns the API domain
	 * 
	 * @return string
	 */
	public function getDomain() {
		return $this->apiDomain;
	}
	
	/**
	 * Returns the consumer registration URL
	 * 
	 * @return string
	 */
	public function getActionUrl() {
		return $this->actionUrl;
	}

	/**
	 * Get a local-stored configuration.
	 * 
	 * @return array
	 */
	private function getConfig() {
		$config = unserialize(Configuration::get('RUBLON_CONF'));		
		return (isset($config)) ? $config : array();
	}


	/**
	 * Clear any temporary config data
	 * 
	 */
	private function _clearConfig() {
		$config = $this->getConfig();
		unset($config['temp_key']);
		unset($config['start_time']);
		$this->_saveConfig($config);		
	}

	
	/**
	 * Update Rublon plugin settings using 'systemToken' and 'secretKey' from
	 * successfully registered project
	 */
	private function updateRublonSettings() {

		$settings = $this->getConfig();
		$settings['system_token'] = $this->getSystemToken();
		$settings['secret_key'] = $this->getSecretKey();
		$this->_clearConfig();

		$this->_saveConfig($settings);

	}

	/**
	 * Get project's additional data
	 *
	 * Adds additional project data related to WordPress
	 * blog's description, plugin's current version and
	 * blog's language.
	 *
	 * @return array
	 */
	protected function getProjectData() {

		$projectData = parent::getProjectData();
		$projectData['project-description'] = Configuration::get('PS_SHOP_NAME');
		$projectData['plugin-version'] = Mage::helper('rublonhelper')->getModuleVersion();
		$projectData['lang-code'] = Mage::helper('rublonhelper')->getLang();
		return $projectData;

	}


	/**
	 * Get name of the project
	 *
	 * Returns name of the project that will be set in Rublon Developers Dashboard.
	 *
	 * @return string
	 */
	protected function getProjectName() {

		return Configuration::get('PS_SHOP_NAME');

	}


	/**
	 * Get project's technology
	 *
	 * Returns technology, module or library name to set in project.
	 *
	 * @return string
	*/
	protected function getProjectTechnology() {

		return RublonHelper::getTechnology();

	}


	/**
	 * Redirect to the given URL
	 *
	 * @param string $url
	 * @return void
	 */
	protected function _redirect($url) {

		Tools::redirectAdmin($url);

	}


}