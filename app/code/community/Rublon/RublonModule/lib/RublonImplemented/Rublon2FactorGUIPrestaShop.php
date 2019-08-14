<?php

require_once dirname (__FILE__) . '/../Rublon/Rublon2FactorGUI.php';

class Rublon2FactorGUIMagento extends Rublon2FactorGUI {
	
	public static $instance;	
	
	public static function getInstance() {
        $helper = Mage::helper('rublonmodule');
        
		if (empty(self::$instance)) {
			self::$instance = new self(
				$helper->getRublon(),
				$helper->getAuthUserId(),
				$helper->getAuthUserEmail(),
				$logout_listener = $helper->isLogoutListenerEnabled()
			);			
		}

		return self::$instance;
	}


	public function getConsumerScript() {
		// Don't show consumer script, it will be embeded in the footer action using self::renderConsumerScript() method.
		return '';
	}


	/**
	 * Returns Rublon Button for plugin's registration.
	 * 
	 * Since the registration is now handled automatically,
	 * the button is not necessary.
	 *
	 * @return RublonButton
	 */
	protected function createActivationButton($activationURL) {
		return '';
	}


	/**
	 * Create Trusted Devices Widget container for WP Dashboard 
	 * 
	 * @return string
	 */
	public function getTDMWidget() {
	    $helper = Mage::helper('rublonmodule');
		$result = '';

		if ($helper->isRublonConfigured()) {
			
			
		    $protection_type = $helper->isUserProtected();
			
			switch ($protection_type) {
				case RublonMagentoModule::PROTECTION_TYPE_MOBILE:
					$result .= '<p>' . sprintf(__r('Your account is protected by <a href="%s" target="_blank">Rublon</a>.'), RublonHelper::rubloncomUrl()) . '</p>';
					break;
				case RublonMagentoModule::PROTECTION_TYPE_EMAIL:
					$result .= '<p>' . sprintf(__r('Your account is protected by <a href="%s" target="_blank">Rublon</a>.'), RublonHelper::rubloncomUrl())
						. ' ' . sprintf(__r('Get the <a href="%s/get" target="_blank">Rublon mobile app</a> for more security.'), RublonHelper::rubloncomUrl()) . '</p>';
					break;				
			}
	
			$result .= $this->getDeviceWidget();
	
		}

		return $result;

	}


	/**
	 * Create Trusted Devices Widget container for WP Dashboard
	 *
	 * @return string
	 */
	public function getACMWidget() {
		return $this->getShareAccessWidget();
	}

	
	/**
	 * Return Rublon Consumer Script URL.
	 * 
	 * @return string
	 */
	public function renderConsumerScript() {
		return parent::getConsumerScript();
	}
    
	/**
	 * Return Rublon Badge code.
	 * 
	 * @return RublonBadge
	 */
	public function getBadgeWidget() {	    
	    return new RublonBadge();
	}
    
	public function getDeviceWidget() {
	    return new Rublon2FactorGUI($this->getRublon(), RublonHelper::getEmployeeId(), RublonHelper::getEmployeeEmailById(RublonHelper::getEmployeeId()));
	}
	
	public function userBox() {

		return '';

	}


}

