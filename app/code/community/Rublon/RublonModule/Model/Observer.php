<?php
/**
 * Rublon2Factor for magento event listener
 *
 * @package   rublon/rublonmodule
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon events observer class
 */
class Rublon_RublonModule_Model_Observer extends Mage_Core_Model_Abstract {

	/**
	 * Implements customer_login hook to apply customer second 
	 * factor authentication, if it is enabled.
	 */
	public function customerLogin($observer) {
		if ($customerId = $observer->getCustomer()->getId()) {
			$helper = Mage::helper('rublonmodule');
			$helper->setModule(RublonMagentoModule::FRONT);
			if ($helper->isRublonConfigured() AND $helper->isRublonEnabled() AND $helper->isRublonSecuredUser($customerId)) {
				$helper->authenticateCustomer($observer);
			}
		}
	}
	
	/**
	 * After admin login
	 * 
	 * @param unknown $observer
	 */
	public function afterAdminLogin($observer) {
		if ($observer->getResult() AND $userId = $observer->getUser()->getId()) { // successful login
			$connect = (strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), 'downloader') !== false);
			$helper = Mage::helper('rublonmodule');
			
			$helper->setModule($connect ? RublonMagentoModule::CONNECT : RublonMagentoModule::ADMIN);
			
			if ($helper->isRublonEnabled()) {
				$helper->authenticateAdmin($observer);
			}
		}
	}
	
	
	/**
	 * Magento Connect custom callback URL
	 * 
	 * Because Magento Connect has own session and cookie restricted to directory "/downloader"
	 * the Rublon callback must be called in this location.
	 * 
	 * @param object $observer
	 */
	public function controllerInit($observer) {
	    
		$helper = Mage::helper('rublonmodule');		
		
		if ($helper->isRublonEnabled() AND isset($_GET['rublon']) AND $_GET['rublon'] == 'callback') {
		    
			require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . 'RublonModule' . DS . 'lib' . DS . 'RublonImplemented' . DS .'Rublon2FactorCallbackMagento.php');
			
			$helper->setModule(RublonMagentoModule::CONNECT);
			     
		    $state = isset($_GET['state']) ? $_GET['state'] : '';
		    $token = isset($_GET['token']) ? $_GET['token'] : null;
		    
			try {
				$callback = new Rublon2FactorCallbackMagento($helper->getRublon());
			    $callback->setState($state);
			    $callback->setAccessToken($token);
			    
			    $callback->call(array($this, 'callbackSuccess'), array($this, 'callbackFailure'));
			    die('test');
			    
			} catch (RublonException $e) {
				$helper->addError($e, array('method' => __METHOD__, 'file' => __FILE__));
				$helper->returnToPage($helper->getSettingsPageUrl());
			}
			
		}
	}
	
	public function callbackSuccess($user_id, Rublon2FactorCallback $callback) {

	    $sessionId = Mage::getSingleton("admin/session")->getEncryptedSessionId();	    
	    $helper = Mage::helper('rublonmodule');	    	     
	    $user = Mage::getModel('admin/user')->load($user_id);
	    
	    Mage::getSingleton('admin/session')->setUser($user);
	    Mage::getSingleton('admin/session')->refreshAcl();	   	    
	    
		$url = $helper->getMagentoConnectUrl();		
		$helper->returnToPage($url);
	}
	
	public function callbackFailure() {
	    Mage::helper('rublonmodule')->addError(sprintf($this->__('An error accured during authentication process. Please try again or contact us at <a href="mailto:%s">%s</a>.'), RublonMagentoModule::RUBLON_SUPPORT_EMAIL, RublonMagentoModule::RUBLON_SUPPORT_EMAIL), array(
            'method' => __METHOD__,
            'file' => __FILE__
        ));
	}

}
?>