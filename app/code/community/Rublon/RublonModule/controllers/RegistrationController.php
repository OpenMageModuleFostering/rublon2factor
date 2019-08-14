<?php
/**
 * Rublon registration controller
 *
 * @package   rublon/rublon2factor
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon registration controller class
 */
class Rublon_RublonModule_RegistrationController extends Mage_Adminhtml_Controller_Action {
	
	
	protected $_publicActions = array('callback');
	
	/**
	 * Rublon helper instance
	 * 
	 * @var Rublon_Rublon2Factor_Helper_Data
	 */
	protected $helper;
	
	

	/**
	 * Pre dispatch controller actions. 
	 * Sets session namespace to admin side.
	 */
	public function preDispatch() {
		$this->_sessionNamespace = 'adminhtml';
		parent::preDispatch();
		$this->helper = Mage::helper('rublonmodule');
		$this->helper->initRegistration();
	}
	
	/**
	 * Initialize consumer registration 
	 */
    public function initializeAction() {
    	$this->run(RublonConsumerRegistrationMagento::ACTION_INITIALIZE);
    }
    
    
    
    /**
     * Communication URL for consumer registration
     * 
     * Without checking magento secret.
     */
    public function callbackAction() {
    	$registrationParams = $this -> getRequest() -> getParams();
    	$action = isset($registrationParams['action']) ? $registrationParams['action'] : null;
    	$this->run($action);
    }
    
    
    /**
     * Run specific action
     * 
     * @param string $action
     */
    protected function run($action) {
    	$helper = Mage::helper('rublonmodule');
    	$helper->setModule(RublonMagentoModule::ADMIN);
    	$helper->consumerRegistrationAction($action);
    }

}
