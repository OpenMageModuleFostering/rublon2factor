<?php

require_once dirname(__FILE__) . '/../RublonConsumerRegistration/RublonConsumerRegistrationTemplate.php';

class RublonConsumerRegistrationMagento extends RublonConsumerRegistrationTemplate {

	const TEMPLATE_FORM_POST = '<form action="%s" method="POST" id="rublon-consumer-registration">
			%s
		</form>';

	/**
	 * Communication URL
	 *
	 * @var string
	 */
	const URL_COMMUNICATION = 'rublon/registration/callback';
	
	protected function finalSuccess() {

		parent::finalSuccess();
		
		$helper = Mage::helper('adminhtml');
		$rublonHelper = Mage::helper('rublonmodule');
		
		$this->updateRublonSettings();

		$updateMessage = __('Thank you! Your account is now protected by Rublon.');
		Mage::helper('rublonmodule')->addMessage($updateMessage);

		if ($rublonHelper->isTrackingAllowed()) {
    		$pluginMeta = $rublonHelper->prepareModuleMeta();
    		$pluginMeta['action'] = 'activation';
    		$rublonHelper->moduleHistoryRequest($pluginMeta);
		}

		$newsletter = $this->getNewsletterSignUp();
		if (!empty($newsletter)) {
			
		    $email = $rublonHelper->getAuthUserEmail();
			$rublon_req = new RublonRequests();
			$rublon_req->subscribeToNewsletter($email);
						
			$rublonHelper->deleteConfig('rublon_newsletter_signup');
		}
		
		// Save project owner information
		$rublonHelper->saveProjectOwner();
		
		// Clean Magento cache
		Mage::app()->getCache()->clean();
		Mage::app()->getCacheInstance()->flush();
		Mage::app()->cleanCache();
		
		$this->_redirect($helper->getUrl('rublon/adminhtml_settings'));

	}


	/**
	 * Update Rublon plugin settings using 'systemToken' and 'secretKey' from
	 * successfully registered project
	 */
	private function updateRublonSettings() {

		$this->_clearConfig();
		$this->_saveConfig('rublon_system_token', $this->getSystemToken());
		$this->_saveConfig('rublon_secret_key', $this->getSecretKey());

	}	 

	/**
	 * Clear any temporary config data
	 * 
	 */
	private function _clearConfig() {
	    
	    $config = Mage::getConfig();		
		$config->deleteConfig('rublon_temp_key');
		$config->deleteConfig('rublon_start_time');
	}


	protected function finalError($msg = NULL) {

		parent::finalError($msg);
		
		if (!$msg || $msg == self::DEVELOPERS_ERROR) {
			$msg = RublonHelper::uriGet('error_msg');
		}
		
		$notifierMessage = 'Consumer registration error.<br /><br />';
		$errorCode = 'API_ERROR';
		if (!empty($msg)) {
			if (stripos($msg, 'ERROR_CODE:') !== false) {
				$errorCode = str_replace('ERROR_CODE: ', '', $msg);
				$notifierMessage .= __('Rublon error code: ', 'rublon') . '<strong>' . $errorCode . '</strong>';
			} else {
				$notifierMessage .= 'Rublon error message: [' . $msg . ']';
			}
		}
		RublonHelper::setMessage($errorCode, 'error', 'CR');
		
		// send issue notify
		echo $this->_notify($notifierMessage);
		
		$this->_redirect(admin_url(RublonHelper::WP_RUBLON_PAGE));

	}


	protected function getSystemToken() {

		$data = $this->getConfig('rublon_system_token');				
		return (isset($data) ? $data : NULL);

	}


	protected function saveSystemToken($systemToken) {	    
	            		
		return $this->_saveConfig('rublon_system_token', $systemToken);

	}

	public function getAllowTracking() {
	
	    $data = $this->getConfig('rublon_allow_tracking');
	    return (isset($data) ? $data : NULL);
	
	}
	
	
	public function saveAllowTracking($allow = 0) {
	     
	    return $this->_saveConfig('rublon_allow_tracking', $allow);
	
	}
	
	public function getNewsletterSignUp() {
	
	    $data = $this->getConfig('rublon_newsletter_signup');
	    return (isset($data) ? $data : NULL);
	
	}
	
	
	public function saveNewsletterSignUp($allow = 0) {
	
	    return $this->_saveConfig('rublon_newsletter_signup', $allow);
	
	}

	protected function getSecretKey() {

		$data = $this->getConfig('rublon_secret_key');
		return (isset($data) ? $data : NULL);

	}


	protected function saveSecretKey($secretKey) {
		
		return $this->_saveConfig('rublon_secret_key', $secretKey);

	}


    /**
	 * Returns local-stored temporary key or NULL if empty.
	 * Temporary key is used to sign communication with API instead of secret key which is not given.
	 * 
	 * @return string
	 */
	protected function getTempKey() {
		return $this->getConfig('rublon_temp_key');
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
		return $this->_saveConfig('rublon_temp_key', $tempKey);
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
		return $this->saveTempKey($tempKey) && $this->saveStartTime($startTime);
	}
    
	/**
	 * Return local-stored start time of the process or NULL if empty.
	 * Start time is used to validate lifetime of the process.
	 *
	 * @return int/null
	 */
	protected function getStartTime() {
	    return (int)$this->getConfig('rublon_start_time');
	}
	
	/**
	 * Save temporary start time to the local storage
	 *
	 * Returns true/false on success/failure.
	 *
	 * @param string $tempKey
	 * @return bool
	 */
	protected function saveStartTime($startTime) {
	    return $this->_saveConfig('rublon_start_time', $startTime);
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
		$url = Mage::getUrl(self::URL_COMMUNICATION);
		$url = rtrim($url, '/\\');
		return $url;
	}


    /**
	 * Get project's public webroot URL address
	 * 
	 * Returns the main project URL address needful for registration consumer in API.
	 * 
	 * @return string
	 */
	protected function getProjectUrl() {
		return Mage::getBaseUrl();		
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

	    return $this->getProjectUrl() . 'rublon/callback/index/state/%state%/token/%token%/windowType/%windowType%';
// 		return $this->_getRublonActionUrl('callback');

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


	public function getAPIDomain() {

		return Mage::helper('rublonmodule')->getApiRegDomain();

	}


    /**
	 * Get name of the project
	 *
	 * Returns name of the project that will be set in Rublon Developers Dashboard.
	 *
	 * @return string
	 */
	protected function getProjectName() {
		return Mage::app()->getStore()->getFrontendName();
	}


    /**
	 * Get project's technology
	 *
	 * Returns technology, module or library name to set in project.
	 *
	 * @return string
	*/
	protected function getProjectTechnology() {

		return Mage::helper('rublonmodule')->getTechnology();

	}


	protected function getUserId() {

		return Mage::helper('rublonmodule')->getAuthUserId();

	}


	protected function getUserEmail() {

		return Mage::helper('rublonmodule')->getAuthUserEmail();

	}


	protected function getRublon() {

		if (empty($this->rublon)) {
			$this->rublon = new Rublon2FactorMagento(null, $this->getTempKey());
		}
		return $this->rublon;

	}


	/**
	 * Redirect to the given URL
	 *
	 * @param string $url
	 * @return void
	 */
	protected function _redirect($url) {
		
        Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        Mage::app()->getResponse()->sendResponse();
        exit;

	}


	/**
	 * Send an error notifier request to Rublon (use a workaround if cURL not present)
	 *
	 * @param string $msg
	 * @return string
	 */
	private function _notify($msg) {
	
		$data = array();
		$data['msg'] = $msg;
		$data['request_uri'] = $_SERVER['REQUEST_URI'];
	
		try {
			RublonHelper::notify($data, array('message-type' => RublonHelper::RUBLON_NOTIFY_TYPE_ERROR));
		} catch (Exception $e) {
			// Do nothing.
		}
		return '';
	
	}


	/**
	 * Save a given data in a local-stored configuration.
	 * 
	 * @param array $data
	 * @return bool
	 */
	protected function _saveConfig($path, $data) {
		$config = Mage::getConfig();
		$config->saveConfig($path, $data);
		Mage::getConfig()->cleanCache();
		$configValue = $this->getConfig($path);
		return (isset($configValue));
	}

	private function getConfig($path = '')
	{
		$data = Mage::getStoreConfig($path);
		return (isset($data) ? $data : NULL);
	}


	/**
	 * Returns the consumer registration URL
	 *
	 * @return string
	 */
	public function getConsumerActionURL() {

		return self::URL_PATH_ACTION;

	}


	protected function getProjectData() {

		$projectData = parent::getProjectData();
		$projectData['project-description'] = '';
		$projectData['plugin-version'] = Mage::helper('rublonmodule')->getModuleVersion();
		$projectData['lang'] = Mage::helper('rublonmodule')->getLang();
		$email = Mage::helper('rublonmodule')->getAuthUserEmail();
		$projectData['project-owner-email'] = $email;
		$projectData['project-owner-email-hash'] = self::hash($email);
		return $projectData;

	}


	public function retrieveRegistrationForm() {

		$temp_key = RublonSignatureWrapper::generateRandomString(RublonConsumerRegistrationCommon::SECRET_KEY_LENGTH);
		$this->saveInitialParameters($temp_key, time());
		$reg_form = $this->getRegistrationForm();
		return $reg_form;

	}


	/**
	 * Get the registration form.
	 *
	 * @return string
	 */
	protected function getRegistrationForm() {
	
		$action = $this->getAPIDomain() . self::URL_PATH_ACTION . '/' . self::ACTION_INITIALIZE;
		$action = htmlspecialchars($action);
	
		$content = $this->getInputHidden(self::FIELD_PROJECT_URL, $this->getProjectUrl())
		. $this->getInputHidden(self::FIELD_PROJECT_CALLBACK_URL, $this->getCallbackUrl())
		. $this->getInputHidden(self::FIELD_PROJECT_DATA, json_encode($this->getProjectData()))
		. $this->getInputHidden(self::FIELD_COMMUNICATION_URL, $this->getCommunicationUrl())
		. $this->getInputHidden(self::FIELD_TEMP_KEY, $this->getTempKey());
	
		return sprintf(self::TEMPLATE_FORM_POST, $action, $content);
	
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
	 * Sets the API domain for testing
	 *
	 * @param string $domain API domain
	 */
	public function setDomain($domain) {
	    $this->apiDomain = $domain;
	}
}
