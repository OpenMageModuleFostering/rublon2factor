<?php
/**
 * Rublon helper
 *
 * @package   rublon/rublon2factor
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

// Include required Rublon modules
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'RublonImplemented' . DS . 'Rublon2FactorMagento.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'RublonIssueNotifier' . DS . 'RublonMagentoIssueNotifier.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'RublonImplemented' . DS . 'RublonConsumerRegistrationMagento.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'RublonImplemented' . DS . 'RublonRequests.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'Rublon' . DS . 'core' . DS . 'API' . DS . 'RublonAPIGetAvailableFeatures.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'RublonImplemented' . DS . 'RublonFeature.php');

/**
 * Magento modules constants wrapper class
 *
 */
class RublonMagentoModule {
    
	const FRONT = 'front';
	
	const ADMIN = 'admin';
	
	const CONNECT = 'connect';
	
	const YES = 'yes';
	
	const NO = 'no';	
	
	const PROTECTION_TYPE_MOBILE = 'mobile';
	
	const PROTECTION_TYPE_EMAIL = 'email';
	
	const RUBLON_NOTIFY_TYPE_ERROR = 'error';
	
	const RUBLON_NOTIFY_TYPE_STATS = 'statistics';
	
	const RUBLON_SUPPORT_EMAIL = 'support@rublon.com';
	
	const FLUSHED_CACHE = 'rublon_flushed_cache';
	
	/**
	 * Module name
	 * @var string
	 */
	const MODULE_NAME = 'RublonModule';
}



/**
 * Main RublonModule for Magento helper class
 */
class Rublon_RublonModule_Helper_Data extends Mage_Core_Helper_Abstract {    
    
	/**
	 * Version of the plugin.
	 */
	const PLUGIN_VERSION = '2.0.0';
	
	/**
	 * Rublon API domain
	 * 
	 * @var string
	 */
	const RUBLON_DOMAIN = 'https://code.rublon.com';
	
	/**
	 * Rublon module registration API domain
	 * 
	 * @var string
	 */
	const RUBLON_REGISTER_DOMAIN = 'https://developers.rublon.com';
	
	/**
	 * Rublon email sales
	 * @var string
	 */
	const RUBLON_EMAIL_SALES = 'sales@rublon.com';
	
	/**
	 * Technology tag
	 * 
	 * @var string
	 */
	const TECHNOLOGY = 'magento';	
	
	/**
	 * ACL path for Rublon configuration permissions
	 * 
	 * @var string
	 */
	const ACL_RUBLON_CONFIG = 'admin/system/config/rublon2factor_options';		
	
	/**
	 * Registration initialize URL
	 * 
	 * @var string
	 */
	const URL_REGISTRATION_INIT = 'rublon/registration/initialize';
	
	/**
	 * Administrator role name
	 * 
	 * @var string
	 */
	const ADMIN_ROLE_NAME = 'Administrators';
	
	/**
	 * Setting key name for project owner email
	 * @var string
	 */
	const SETTING_PROJECT_OWNER_EMAIL = 'rublon_project_owner_email';
	
	/**
	 * Module name to authenticate for
	 *
	 * @var string
	 */
	public $module;
	
	/**
	 * Rublon instance
	 * 
	 * @var Rublon2Factor
	 */
	private $service = null;
	
	/**
	 * Is Rublon enabled and configured
	 * 
	 * @var bool
	 */
	private $isEnabled;
	
	/**
	 * Magento user ID to authenticate
	 * 
	 * @var int
	 */
	private $authUserId = null;
	
	/**
	 * Magento user Email to authenticate
	 *
	 * @var int
	 */
	private $authUserEmail = null;
	
	/**
	 * Instance of the Rublon Issue Notifier
	 * 
	 * @var RublonMagentoIssueNotifier
	 */
	private $issueNotifier;
	
	
	
	/**
	 * Initialize object
	 */
	public function __construct() {			    
		
 		$this->issueNotifier = new RublonMagentoIssueNotifier($this);
		
		$this->isEnabled = ($this->getSystemToken() AND $this->getSecretKey());
		
		if ($this->isRublonConfigured()) {
		  $this->service = new Rublon2FactorMagento($this->getSystemToken(), $this->getSecretKey());
		}		
	}
	
	public function getIssueNotifier() {
	    return $this->issueNotifier;
	}
	
	/**
	 * Returns Rublon2Factor instance.
	 * 
	 * @return object <Rublon2Factor>
	 */
	public function getRublon() {
	    if ( $this->service instanceof Rublon2FactorMagento && $this->isRublonConfigured()) {
	        $rublon = $this->service;
	    } else {	        
	        $rublon = $this->service = new Rublon2FactorMagento($this->getSystemToken(), $this->getSecretKey());	        	        
	    }
	    
	    return $rublon;
	}
	
	/**
	 * Get module's technology tag
	 * 
	 * @return string
	 */
	public function getTechnology() {
		return self::TECHNOLOGY;
	}

	
	/**
	 * Initializes Rublon registration.
	 */
	public function initRegistration() {	    
		$this->registration = new RublonConsumerRegistrationMagento();		
		$this->registration->setDomain(self::RUBLON_REGISTER_DOMAIN);		
	}
	
	public function getApiRegDomain() {
	    return self::RUBLON_REGISTER_DOMAIN;
	}
	
	/**
	 * Start Rublon registration process.
	 * 
	 * @param string $back Back to the My Account or Configuration page
	 */
	public function runRublonRegistration($back = null) {
		if (!empty($back)) {
			Mage::getSingleton('core/session')->setReturnBack($back);
		}		
		$this->consumerRegistrationAction(RublonConsumerRegistration::ACTION_INITIALIZE);
	}
	
	/**
	 * Handle action of the Rublon registration process.
	 * 
	 * @param string $action
	 * @return void
	 */
	public function consumerRegistrationAction($action) {
		$this->initRegistration();
        if (! $this->isRublonEnabled()) {
            try {
                $this->registration->action($action);
            } catch (RublonException $e) {
                $this->addError($e->getMessage(), array(
                    'method' => __METHOD__,
                    'file' => __FILE__
                ));
                $this->returnToPage($this->getSettingsPageUrl());
            }
        } else {
            $this->addError('Error', array(
                'method' => __METHOD__,
                'file' => __FILE__
            ));
            $this->returnToPage($this->getSettingsPageUrl());
        }
	}
	
	/**
	 * Wrap JS into DOMContentLoaded event
	 *
	 * @param string $content
	 * @return string
	 */
	public function getScriptOnload($content) {
		return '<script type="text/javascript">if (document.addEventListener) {
	    		document.addEventListener("DOMContentLoaded", function() {
	    			'. $content .'
	    		});
			}
			</script>';
	}
	
	/**
	 * Checks whether Rublon is enabled and configured.
	 *
	 * @return boolean
	 */
	public function isRublonEnabled() {
		return $this->isEnabled;
	}

	/**
	 * Checks whether Rublon is configured.
	 *
	 * @return boolean
	 */	
	public function isRublonConfigured() {	    	    
		return ($this->getSystemToken() AND $this->getSecretKey());
	}


	/**
	 * Get URL called before authentication
	 * 
	 * @return string
	 */
	public function getBeforeAuthUrl() {
		switch ($this->getModule()) {
			case RublonMagentoModule::FRONT:
				$session = Mage::getSingleton('customer/session');
				$helper = Mage::helper('customer');
				break;
			default:
				$session = Mage::getSingleton('admin/session');
				$helper = Mage::helper('admin');
		}
		if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
			return $this->getAfterLoginUrl();
		} else {
			return $session->getBeforeAuthUrl();
		}
	}
	
	
	/**
	 * Get Rublon Settings page URL
	 * 
	 * @return string
	 */
	public function getSettingsPageUrl() {	    
		switch ($this->getModule()) {
			case RublonMagentoModule::ADMIN:
				return Mage::helper("adminhtml")->getUrl('rublon/adminhtml_settings');
			case RublonMagentoModule::CONNECT:
				return $this->getMagentoConnectUrl();
// 			default:
// 				return Mage::getUrl('rublon/customer/settings');
		}
	}
	
	
	/**
	 * Get URL that should be called after login
	 * 
	 * @return string
	 */
	public function getAfterLoginUrl() {
		switch ($this->getModule()) {
			case RublonMagentoModule::ADMIN:
				return Mage::helper("adminhtml")->getUrl('adminhtml');
			case RublonMagentoModule::CONNECT:
				return $this->getMagentoConnectUrl();
			default:
				return Mage::getUrl('customer/account');
		}
	}
	
	
	
	/**
	 * Login an user with given id, as a current user.
	 * 
	 * @return void
	 */
	public function loginUser($userId) {
		if ($this->isAdmin()) {
			$this->loginAdmin($userId);
		} else {
			$this->loginCustomer($userId);
		}
	}
	
	/**
	 * Login an user with given id, as a current administrator.
	 * 
	 * @return void
	 */
	private function loginAdmin($userId) {
		$session = $this->getUserSession();
		$model = $this->getUserModel();
		$session->setUser($model->load($userId));
	}

	/**
	 * Login an user with given id, as a current customer.
	 * 
	 * @return void
	 */
	private function loginCustomer($userId) {
		$session = $this->getUserSession();
		$model = $this->getUserModel();
		$session->setCustomer($model->load($userId));
	}

	/**
	 * Perform second factor authentication for an administrator
	 * login action.
	 * 
	 * @return void
	 */
	public function authenticateAdmin($observer) {
	    
		$this->authUserId = $observer->getUser()->getId();
		$this->authUserEmail = $observer->getUser()->getEmail();
		
		$this->authenticateSecondFactor();
	}

	/**
	 * Perform second factor authentication for a customer
	 * login action.
	 * 
	 * @return void
	 */	
	public function authenticateCustomer($observer) {
		$this->authUserId = $observer->getCustomer()->getId();
		$this->authenticateSecondFactor();
	}
	
	/**
	 * Start authenticating an user (administrator or customer) using
	 * second factor service.
	 * 
	 * @return void
	 */
	public function authenticateSecondFactor() {	    	    		        
	    
	    $module = $this->getModule();
	    
		$authParams = array(
			'module' => $module,
		);
		
		if ($module == RublonMagentoModule::CONNECT) {
		    $callbackUrl = $this->getCallbackUrl($connect = true);
		} else {
		    $callbackUrl = $this->getCallbackUrl();
		}
				
		$userId = $this->getAuthUserId();
		$email = $this->getAuthUserEmail(); 
		
		$this->clearLoggedUser();
        
        try {
                        
            $authUrl = $this->service->auth($callbackUrl, $userId, $email, $authParams);                        
            if (!empty($authUrl)) {
                
                // Retrive cached rublon features
                RublonFeature::getFeatures();
                
                $this->redirect($authUrl);
            } else {
                // do nothing
            }
            
        } catch (RublonException $e) {
            
            $notifierOptions = array(
                'Method' => __METHOD__,
                'Line' => __LINE__,
                'Trace' => $e->getTraceAsString()
            );
                        
            $this->addError($e, $notifierOptions);
        }
	}


	/**
	 * Return proper session, according to the login process 
	 * side (administrator or customer);
	 * 
	 * @return Mage_Admin_Model_Session/Mage_Customer_Model_Session
	 */	
	public function getUserSession() {
		return ($this->isAdmin()) ? Mage::getSingleton('admin/session') : Mage::getSingleton('customer/session');
	}

	/**
	 * Return proper user model, according to the login process 
	 * side (administrator or customer);
	 * 
	 * @return Mage_Admin_Model_User/Mage_Customer_Model_Customer
	 */
	public function getUserModel() {
		return ($this->isAdmin()) ? Mage::getSingleton('admin/user') : Mage::getSingleton('customer/customer');
	}

	/**
	 * Unset currently logged user after authenticating with
	 * username/password, to allow second factor authentication.
	 */
	private function clearLoggedUser() {
		$session = $this->getUserSession();
		if ($this->isAdmin()) {
			$session->setUser($this->getUserModel());
		} else {
			$session->setCustomer($this->getUserModel());
		}
	}

	/**
	 * Answer whether it is on administrator or customer login action.
	 * 
	 * @return boolean
	 */
	public function isAdmin() {
		return ($this->getModule() != RublonMagentoModule::FRONT);
	}

	
	/**
	 * Get Magento Connect URL
	 * 
	 * @return string
	 */
	public function getMagentoConnectUrl() {
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'downloader/';
	}

	
	/**
	 * Return an id of user, which need to be authenticated by Rublon second factor
	 * 
	 * @return void
	 */
	public function getAuthUserId() {
		return (!empty($this->authUserId)?$this->authUserId:Mage::getSingleton('admin/session')->getUser()->getId());
	}
	
	public function getAuthUserEmail() {
	    return (!empty($this->authUserEmail)?$this->authUserEmail:Mage::getSingleton('admin/session')->getUser()->getEmail());
	}

	/**
	 * Returns the html code of Rublon script
	 *
	 * @return string
	 */
	public function getRublonScript() {
		if ($this->isRublonEnabled()) {
			require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . 'Rublon2Factor' . DS . 'lib' . DS . 'Rublon' . DS . 'HTML' . DS . 'RublonConsumerScript.php');
			return new RublonConsumerScript($this->service);
		}
	}	
	
	/**
	 * Get current URL
	 *
	 * @return string
	 */
	public function getCurrentURL() {
		return Mage::helper('core/url')->getCurrentUrl();
	}
	
	
	/**
	 * Convert local relative URL into absolute
	 * 
	 * @param string $url
	 * @return string
	 */
	public function getAbsoluteURL($url) {
		if (!parse_url($url, PHP_URL_HOST)) {
			$request = Mage::app()->getRequest();
			$port = $request->getServer('SERVER_PORT');
			if ($port) {
				$defaultPorts = array(
					Mage_Core_Controller_Request_Http::DEFAULT_HTTP_PORT,
					Mage_Core_Controller_Request_Http::DEFAULT_HTTPS_PORT
				);
				$port = (in_array($port, $defaultPorts)) ? '' : ':' . $port;
			}
			return $request->getScheme() . '://' . $request->getHttpHost() . $port . $url;
		}
	}
	
	
	/**
	 * Add error message and notify Rublon team if needed
	 * 
	 * @param mixed $error
	 * @param string $notifierOptions
	 * @return void
	 */
	public function addError($error, $notifierOptions = array()) {
		
		// Prepare error message
		if (is_object($error) AND $error instanceof Exception) {		    		    
			//$message = $error->getMessage();
			$errorClassName = get_class($error);
			$errorMessage = $error->getMessage();
			$errorCode = $error->getCode();
			
			$personalEditionLimitationError = ($error instanceof PersonalEditionLimited_RublonApiException);
			
			if ($personalEditionLimitationError) {			    
			    $messages[] = array('type' => 'notice', 'msg' => $error->getMessage());
			} else {
    			$messages[] = sprintf($this->__('An error accured during authentication process. Please try again or contact us at <a href="mailto:%s">%s</a>.'), RublonMagentoModule::RUBLON_SUPPORT_EMAIL, RublonMagentoModule::RUBLON_SUPPORT_EMAIL);
    			if ($errorMessage) {
    			    $messages[] = $this->__('Rublon error message') .':' . $error->getMessage();
    			}                						          			
    			$messages[] = $this->__('Rublon error code') .': ' . $errorClassName;
			}
		} else {
			$message = $this->__((string)$error);
			if (!strlen($message)) {
				$message = $this->__('An error has occurred.');
			}
			$messages[] = $message;
		}
		
		// Add flash message
		if ($messages) {		  
		  foreach ($messages as $message) {
		      if (isset($message['type']) && $message['type'] == 'notice') {
		          self::addMessage($message['msg'], 'notice');
		      } else {
		          self::addMessage($message, 'error');		          
		      }
		  }    
		}		
		
		// Send notify
		if (!empty($notifierOptions) && !$personalEditionLimitationError) {
		    if ($this->isTrackingAllowed()) {
			    $this->issueNotifier->notify($error, $notifierOptions);
		    }
		}
		
	}

    public static function addMessage($content, $type = 'success') {
        switch($type) {
            case 'notice':
                Mage::getSingleton('core/session')->addNotice($content);
                break;
            case 'error':
                Mage::getSingleton('core/session')->addError($content);
                break;
            default:
                Mage::getSingleton('core/session')->addSuccess($content);
        }        
    }
	
	/**
	 * Return currently logged user.
	 * 
	 * @return object
	 */
	public function getLoggedUser() {
		$session = $this->getUserSession();
		return ($this->isAdmin()) ? $session->getUser() : $session->getCustomer();
	}

	/**
	 * Checks whether currently logged user account is secured by Rublon
	 * second factor.
	 *
	 * @return boolean
	 */
	public function isRublonSecuredAccount() {
		return $this->isRublonConfigured() && ($this->isProjectOwner() || RublonFeature::isBusinessEdition());
	}
	
	
	/**
	 * Get current user's ID
	 * 
	 * @return int
	 */
	public function getUserId() {
		$currentUser = $this->getLoggedUser();
		if ($currentUser) {
			return $currentUser->getId();
		}
	}

	/**
	 * Checks whether an user with given id is secured by Rublon
	 * second factor.
	 *
	 * @param int $userId
	 * @return boolean
	 */
	public function isRublonSecuredUser($userId = null) {
		return ($this->isRublonConfigured()
			AND $this->isRublonEnabled()
		    );
	}

	
	/**
	 * Redirect to a page with given url.
	 * 
	 * @param string $returnUrl
	 */
	public function returnToPage($returnUrl) {
		header('location: '. $returnUrl);
		exit;
	}
	
	public function redirect($url) {
	    $this->returnToPage($url);
	}

	/**
	 * Check whether currently logged user is at administrator side.
	 * 
	 * @return boolean
	 */
	public function checkIsAdmin() {
		return Mage::getSingleton('admin/session')->isLoggedIn();
	}
	
	
	
	
	/**
	 * Get system token
	 * 
	 * @return string|NULL
	 */
	public function getSystemToken() {	    
		return Mage::getStoreConfig('rublon_system_token', Mage::app()->getStore());
	}
	
	/**
	 * Get secret key
	 * 
	 * @return string|NULL
	 */
	public function getSecretKey() {
		return Mage::getStoreConfig('rublon_secret_key', Mage::app()->getStore());
	}
	
	/**
	 * Get current language code
	 * 
	 * @return string
	 */
	public function getLang() {
		return substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
	}
	
	
	/**
	 * Get Magento module name
	 * 
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}
	
	
	/**
	 * Set Magento module name
	 * 
	 * @param string $module
	 * @return Rublon_Rublon2Factor_Helper_Data
	 */
	public function setModule($module) {
		$this->module = $module;
		return $this;
	}
	
	
	/**
	 * Check whether this is Magento Connect
	 * 
	 * @return boolean
	 */
	public function isMagentoConnect() {
		return ($this->getModule() == RublonMagentoModule::CONNECT);
	}
	
	
	
	
	
	/**
	 * Returns registration initialize URL
	 * 
	 * @return string
	 */
	public function getRegistrationInitURL() {
		return Mage::helper("adminhtml")->getUrl(self::URL_REGISTRATION_INIT);
	}	
	
	
	/**
	 * Get HTML code with Rublon icon for welcome message
	 * 
	 * @return string
	 */
	public function getRublonIcon() {
	    $rublonIconImg = Mage::getDesign()->getSkinBaseUrl() . 'Rublon/img/rublon_logo_16x16.png';
		return sprintf('<span title="%s"
			style="width:16px;height:16px;background:url(%s) 0px 0px no-repeat;vertical-align:middle;display:inline-block;"
			class="rublon-protected-icon"></span>',
			htmlspecialchars($this->__('Your account is protected by Rublon')),
			$rublonIconImg
		);
	}
	
	
	/**
	 * Returns Rublon API domain
	 * 
	 * @return string
	 */
	public function getRublonDomain() {
		return self::RUBLON_DOMAIN;
	}	
	
	/**
	 * Check if a logged user owns an administration role.
	 * 
	 * @return boolean
	 */
	public function isAdministrator() {
	    $adminuserId = Mage::getSingleton('admin/session')->getUser()->getUserId();	    
	    $role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
	    
	    return in_array(self::ADMIN_ROLE_NAME, $role_data);
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isLogoutListenerEnabled() {
	    return true;
	}
    
	/**
	 * Check wether user is protected by Rublon.
	 * 
	 * @return string
	 */
	public function isUserProtected() {
	    $mobileStatus = $this->getMobileUserStatus($this->getAuthUserId(), $this->getAuthUserEmail());
	    return ($mobileStatus == RublonMagentoModule::YES ? RublonMagentoModule::PROTECTION_TYPE_MOBILE : RublonMagentoModule::PROTECTION_TYPE_EMAIL); 	        
	}
	
	/**
	 * Check if a user has installed Rublon Mobile App.
	 *  
	 * @param int $userId
	 * @param string $userEmail
	 * @param string $refresh
	 * @return Ambigous <mixed, string>
	 */
	public function getMobileUserStatus($userId, $userEmail, $refresh = false) {

	    $status_name = 'rublon_mobile_status_' . $this->getAuthUserId();
	    $mobile_user_status = Mage::getStoreConfig($status_name);
	    
	    if ($refresh || empty($mobile_user_status)) {
	        $rublon_req = new RublonRequests();
	        $mobile_user_status = $rublon_req->checkMobileStatus($userId, $userEmail);
	        $config = Mage::getConfig();
	        $config->saveConfig($status_name, $mobile_user_status);	        
	    }
	    return $mobile_user_status;
	
	}
	
	/**
	 * Returns callback URL.
	 * 
	 * @param string $connect
	 * @return string
	 */
	public function getCallbackUrl($connect = false) {
	    
	    $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'rublon/callback/index/state/%state%/token/%token%/windowType/%windowType%';
	    
	    if ($connect) {
	        $url = self::getMagentoConnectUrl().'?rublon=callback&state=%state%&token=%token%&windowType=%windowType%';
	    }
	    
	    return $url;
	}
	
	/**
	 * Check if error trucking is allowed.
	 * 
	 * @return boolean
	 */
	public function isTrackingAllowed() {
	    $data = Mage::getStoreConfig('rublon_allow_tracking');
	    		
	    return !empty($data)?true:false;
	}
	
	/**
	 * Returns version of the plugin
	 * @return string
	 */
	public function getModuleVersion() {
	    return self::PLUGIN_VERSION;
	}
	
	/**
	 * Prepare plugin meta data to be reported
	 *
	 * @return array
	 */
	public function prepareModuleMeta() {	
	    
	    $pluginVersion = $this->getModuleVersion();
	    
	    // Other info
	    $moduleMeta = array(
	        'magento-version' => Mage::getVersion(),
	        'plugin-version' => $pluginVersion
	    );
	    
	    // Backend users
        $roles = Mage::getModel('admin/roles')->getCollection();
        
	    if (!empty($roles)) {
            foreach($roles as $role) {
                $moduleMeta['registered-'.strtolower($role->getRoleName()).'-users'] = count(Mage::getModel('admin/roles')->load($role->getId())->getRoleUsers());
            }
        }	
	    
        // Frontend users 
        $users = Mage::getModel('customer/customer')->getCollection();	       
	    
	    $moduleMeta['registered-customers'] = count($users);
	
	    $metaHeader = array(
	        'meta' => $moduleMeta,
	    );
	    return $metaHeader;
	}
	
	/**
	 * Send a request with module's history to Rublon servers
	 *
	 * @param array $data Module's history data
	 */
	public function moduleHistoryRequest($data) {
	     	    
	    require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . RublonMagentoModule::MODULE_NAME . DS . 'lib' . DS . 'RublonImplemented' . DS . 'RublonAPIPluginHistory.php');
	
        $rublon = $this->getRublon();	
        $data['systemToken'] = $this->getSystemToken();    
	    $request = new RublonAPIPluginHistory($rublon, $data);
	
	    try {
	        $response = $request->perform();
	    } catch (Exception $e) {
	        print_r($e); die('test');	        
	        $this->addError('History request faild: '.$e->getMessage());
	    }
	
	    if (!empty($response) && $response->historyHasBeenAdded()) {
	        return true;
	    }
	    return false;	
	}
	
	/**
	 * Prepare url pieces needed for the plugin history request
	 *
	 * @return array
	 */
	public function getConsumerRegistrationData() {
	
	    $consumerRegistration = new RublonConsumerRegistrationMagento();
	    $consumerRegistration->setDomain(self::RUBLON_REGISTER_DOMAIN);
	    return array(
	        'url' => $consumerRegistration->getAPIDomain(),
	        'action' => $consumerRegistration->getConsumerActionURL()
	    );
	
	}	

	public function getBuyBusinessEditionURL() {	    
	    $systemToken = $this->getSystemToken();
	    $url = '';	    	    
	    
	    if ($systemToken) {
	        
	        $data = array(
	            RublonConsumerRegistrationCommon::FIELD_SYSTEM_TOKEN => $systemToken,
	            RublonConsumerRegistrationCommon::FIELD_PARTNER_KEY => ''//$partnerKey
	        );
	        
	        $url = sprintf(self::RUBLON_REGISTER_DOMAIN . '/store/buy/%s', urlencode(base64_encode(serialize($data))));
	    } else {
	        $url = sprintf('mailto:%s?subject=%s', self::RUBLON_EMAIL_SALES, __('Rublon Business Edition'));
	    }
	    
	    return $url;
	}
	
	public function getProjectOwnerEmail() {
	    return Mage::getStoreConfig(self::SETTING_PROJECT_OWNER_EMAIL, Mage::app()->getStore());
	}
	
	public function isProjectOwner($userId = 0) {
	    	    	    	    
	    $projectOwnerEmail = $this->getProjectOwnerEmail();	    	    
	    
	    if (!empty($userId)) {	        
	        $userEmail = Mage::getModel('admin/user')->load($userId)->getData('email');
	    } else {
	        $userId = Mage::getSingleton('admin/session')->getUser()->getUserId();
	        $userEmail = Mage::getModel('admin/user')->load($userId)->getData('email');
	    }
	
	    return !empty($userEmail) && !empty($projectOwnerEmail) && ($projectOwnerEmail == $userEmail);
	}
	
	public function canShowTDMWidget() {
	    return RublonFeature::isBusinessEdition() || $this->isProjectOwner();
	}
	
	public function saveProjectOwner($userEmail = null) {
	    
	    if (empty($userEmail)) {	        
	        $userEmail = $this->getAuthUserEmail();	        
	    }
	    if ($userEmail) {
	        $config = Mage::getConfig();
	        return $config->saveConfig(self::SETTING_PROJECT_OWNER_EMAIL, $userEmail);
	    }
	}
	
	/**
	 * Delete selected config key from database
	 * @param unknown $key
	 */
	public function deleteConfig($key) {
	    $config = Mage::getConfig();
	    $config->deleteConfig($key);
	}
	
	public function cleanCache() {	    
	    // clear cache
	    $cache1 = Mage::app()->getCacheInstance()->flush();
	    $cache2 = Mage::app()->cleanCache();
	    
	    $config = Mage::getConfig();
	    $config->saveConfig(RublonMagentoModule::FLUSHED_CACHE, 1);
	    
	}
}




