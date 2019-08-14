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
					$result .= '<p>' . sprintf(Mage::helper('rublonmodule')->__('Your account is protected by <a href="%s" target="_blank">Rublon</a>.'), $helper->getRublonDomain()) . '</p>';
					break;
				case RublonMagentoModule::PROTECTION_TYPE_EMAIL:
					$result .= '<p>' . sprintf(Mage::helper('rublonmodule')->__('Your account is protected by <a href="%s" target="_blank">Rublon</a>.'), $helper->getRublonDomain())
						. ' ' . sprintf(Mage::helper('rublonmodule')->__('Get the <a href="%s/get" target="_blank">Rublon mobile app</a> for more security.'), $helper->getRublonDomain()) . '</p>';
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
	    $helper = Mage::helper('rublonmodule');
	    return new Rublon2FactorGUI($this->getRublon(), $helper->getAuthUserId(), $helper->getAuthUserEmail());
	}
	
	public function userBox() {

		return '';

	}


}

