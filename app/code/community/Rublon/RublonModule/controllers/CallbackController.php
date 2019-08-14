<?php
/**
 * Rublon callback controller
 *
 * @package   rublon/rublon2factor
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon Callback for frontend and backend
 * 
 * Magento Connect has own callback in Observer - this callback will redirect to proper URL if needed.
 */
class Rublon_RublonModule_CallbackController extends Mage_Core_Controller_Front_Action {
	
	
	/**
	 * Rublon Callback instance
	 * 
	 * @var RublonCallback
	 */
	protected $callback;
	

	/**
	 * Pre-dispatch
	 */
	public function preDispatch() {
		
		$helper = Mage::helper('rublonmodule');
		
		if ($helper->isRublonEnabled()) {
			
			require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . 'RublonModule' . DS . 'lib' . DS . 'RublonImplemented' . DS .'Rublon2FactorCallbackMagento.php');
			
			try {			    
			    			    			    
			    $rublonCallbackParams = $this -> getRequest() -> getParams();
			     
			    $state = isset($rublonCallbackParams['state']) ? $rublonCallbackParams['state'] : '';
			    $token = isset($rublonCallbackParams['token']) ? $rublonCallbackParams['token'] : null;
			    
			    $callback = new Rublon2FactorCallbackMagento($helper->getRublon());
			    $callback->setState($state);
			    $callback->setAccessToken($token);
                
			    $module = 'admin';
			    	
			    // set session namespace
			    $this->_sessionNamespace = ($helper->isAdmin()) ? 'adminhtml' : 'frontend';
			    
			    parent::preDispatch();			    
			
			    // Set module
			    $helper->setModule($module);

			    // run Rublon authentication
			    $callback->call(array($this, 'callbackSuccess'), array($this, 'callbackFailure'));
			 
			    
			} catch(RublonException $e) {			    
				$helper->addError($e, array('method' => __METHOD__, 'file' => __FILE__));				
				$helper->returnToPage($helper->getSettingsPageUrl());
			}
			
		}
		
	}

	/**
	 * Redirect after Rublon Callback
	 */
	public function indexAction() {
		$helper = Mage::helper('rublonmodule');
		
        // Get Rublon settings page URL				    
		$url = $helper->getSettingsPageUrl();

		// get Dashboard URL
		$url = $helper->getAfterLoginUrl();
		
		// Redirect
		$helper->returnToPage($url);
	}
	
	
	/**
	 * Set session namespace and call parent preDispatch method
	 * 
	 * Called from Rublon Callback instance to initialize admin or frontend session.
	 * 
	 * @return void
	 */
	public function setSessionNamespace() {
		if (Mage::helper('rublonmodule')->isAdmin()) {
			$this->_sessionNamespace = 'admin';
		} else {
			$this->_sessionNamespace = 'frontend';
		}
		parent::preDispatch();
	}
	
	public function callbackSuccess($user_id, Rublon2FactorCallback $callback) {
	     	     
	    $user = Mage::getModel('admin/user')->load($user_id);
	    Mage::getSingleton('admin/session')->setUser($user);
	    Mage::getSingleton('admin/session')->refreshAcl();
	    
	    // Save info about project owner
	    $projectOwner = $callback->getConsumerParam(RublonAPICredentials::FIELD_PROJECT_OWNER);
	    if ($projectOwner === -1) { // Personal edition disabled - clear cashed features
	        RublonFeature::deleteFeaturesFromCache();
	    } elseif ($projectOwner) {
	        $userEmail = $user->getEmail();
	        Mage::helper('rublonmodule')->saveProjectOwner($userEmail);
	    }
	
	    return true;
	}
	
	public function callbackFailure() {
	    Mage::helper('rublonmodule')->addError(sprintf($this->__('An error accured during authentication process. Please try again or contact us at <a href="mailto:%s">%s</a>.'), RublonMagentoModule::RUBLON_SUPPORT_EMAIL, RublonMagentoModule::RUBLON_SUPPORT_EMAIL), array(
            'method' => __METHOD__,
            'file' => __FILE__
        ));
	}
}
