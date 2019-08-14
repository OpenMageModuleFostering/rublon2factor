<?php

/**
 * Menu of the admin page (backend)
 *
 */
class Rublon_RublonModule_Block_Adminhtml_Menu extends Mage_Adminhtml_Block_Page_Menu {
	
	
	/**
	 * Append Rublon menu item
	 * 
	 * @see Mage_Adminhtml_Block_Page_Menu::getMenuArray()
	 * @return array
	 */
	public function getMenuArray() {
		
		$result = parent::getMenuArray();		
		
		$helper = Mage::helper('rublonmodule');
		$helper->setModule(RublonMagentoModule::ADMIN);
							
		if (!empty($result['rublonmodule']) && !empty($result['rublonmodule']['children'])) {
            foreach ($result['rublonmodule']['children'] as &$child) {
                if ($child['label'] == 'Settings') {
                    if (!$helper->isRublonConfigured()) {
                        $child['label'] = 'Activation';
                    }
                }
            }		    		    
		} elseif($helper->isRublonConfigured()) {
		  $result['rublonmodule'] = array(
                'label' => 'Rublon',
                'sort_order' => '10',
                'url' => '',
                'active' => '',
                'level' => 0,
		        'children' => array(
                    'settings' => array(
                        'label' => $this->__('Trusted Devices'),
                        'sort_order' => '10',
                        'url' => $helper->getSettingsPageUrl(),
                        'active' => '',
                        'level' => 0
                    )
                )
            );    
		}
		
		return $result;
		
	}
	
	
}