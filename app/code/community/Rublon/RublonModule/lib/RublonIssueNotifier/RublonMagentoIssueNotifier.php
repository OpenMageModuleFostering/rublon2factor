<?php

require_once 'RublonIssueNotifier.php';

class RublonMagentoIssueNotifier extends RublonIssueNotifier {
	
	
	/**
	 * Rublon helper instance
	 * 
	 * @var Rublon_Rublon2Factor_Helper_Data
	 */
	protected $helper;
	
	/**
	 * Silent notification
	 * @var bool
	 */
	protected $silence;
	
	
	/**
	 * Constructor - set the Rublon helper instance
	 * 
	 * @param Rublon_Rublon2Factor_Helper_Data $helper
	 */
	function __construct($helper) {
		$this->helper = $helper;
	}
	
	function setSilence($value) {
	    $this->silence = $value;
	}
	
	/**
	 * Add Magento specific variables into issue information
	 * 
	 * @see RublonIssueNotifier::formatData()
	 */
	protected function formatData($issue, $options) {
		
		$data = parent::formatData($issue, $options);
		
		if (function_exists('ini_get_all')) {
			@ $data['context']['php']['ini'] = ini_get_all(null, false);
		}
		if (function_exists('get_loaded_extensions')) {
			@ $data['context']['php']['loaded_extensions'] = get_loaded_extensions();
		}
		
		if (empty($data['email'])) {
			$data['email'] = $this->helper->getAuthUserEmail();
		}
		
		$data['context']['_SERVER'] = $_SERVER;
		$data['context']['_POST'] = false;
		$data['context']['_GET'] = false;
		$data['context']['_COOKIE'] = false;
		
		return $data;
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::getDomain()
	 */
	protected function getDomain() {
		return $this->helper->getRublonDomain();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::getTechnology()
	 */
	protected function getTechnology() {
		return $this->helper->getTechnology();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::sendByBrowser()
	 */
	protected function sendByBrowser(array $options) {
	    if (!$this->silence) {
    		$content = $this->helper->__('Issue notification has been sent to the Rublon support team.');
    		$content .= $this->getBrowserIssueForm($options);
    		Mage::getSingleton('core/session')->addNotice($content);
	    }
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::getCurrentUrl()
	 */
	protected function getCurrentUrl() {
		return $this->helper->getCurrentURL();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see RublonIssueNotifier::send()
	 */
	protected function send(array $options) {
	    if ($this->helper->isTrackingAllowed()) {
	        parent::send($options);
	    }
	}
	
}