<?php

/**
 * Header of the admin page (backend)
 *
 */


class Rublon_RublonModule_Block_Adminhtml_Header extends Mage_Adminhtml_Block_Page_Header {
	
	public function __() {
	    
		$args = func_get_args();
		$result = call_user_func_array('parent::__', $args);				
		
		if (!empty($args[0]) AND $args[0] == "Logged in as %s") {
			$helper = Mage::helper('rublonmodule');
			$helper->setModule(RublonMagentoModule::ADMIN);
			
			if ($helper->isRublonSecuredAccount()) {			    
				$result .= ' '. $helper->getRublonIcon();
			}
		}
		return $result;
	}
	
	
}