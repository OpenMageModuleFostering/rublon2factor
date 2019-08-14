<?php

require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . 'RublonModule' . DS . 'lib' . DS . 'Rublon' . DS . 'core' . DS . 'API' . DS . 'RublonAPIGetAvailableFeatures.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . 'RublonModule' . DS . 'lib' . DS . 'RublonImplemented' . DS . 'RublonFeature.php');
require_once (Mage::getBaseDir('code') . DS . 'community' . DS . 'Rublon' . DS . 'RublonModule' . DS . 'lib' . DS . 'RublonImplemented' . DS . 'Rublon2FactorGUIMagento.php');

class Rublon_RublonModule_Adminhtml_SettingsController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * 
     * @var Rublon_RublonModule_Helper_Data
     */
    private $helper;    
    
    public function _construct() {
        $this->helper = Mage::helper('rublonmodule');
    }
    
    public function indexAction() {
        
        $this->initialize();
        
        $isAdministrator = $this->helper->isAdministrator();
        $isProjectOwner = $this->helper->isProjectOwner();
        $showNotProtectedMessage = !RublonFeature::isBusinessEdition() && !$this->helper->isAdministrator();
        
        if ($showNotProtectedMessage) {
            $this->helper->addMessage($this->__('Due the Rublon Personal Edition limitation your account is not protected.'), 'notice');
        }
        
        $this->loadLayout()
        ->_setActiveMenu('rublonmodule')
        ->_title($this->__('Index Action'));
                
        
        $gui = Rublon2FactorGUIMagento::getInstance();
                
        $data = array(
            'showUpgradeBox' => (!RublonFeature::isBusinessEdition() && $isAdministrator) ,
            'isProjectOwner' => $isProjectOwner,
            'isBusinessEdition' => RublonFeature::isBusinessEdition(),
            'showNotProtectedMessage' => $showNotProtectedMessage,
            'canShowTrustedDeviceWidget' => $this->helper->canShowTDMWidget(),
            'businessEditionActivatedText' => $this->__('Thank you for using Rublon Business Edition.'),
            'deviceWidget' => $gui->getDeviceWidget(),
            'upgradeNowURL' => $this->helper->getBuyBusinessEditionURL(),
            'boxTitle' => $isProjectOwner?$this->__('Only your account is protected! Need Rublon for more accounts?'):$this->__('Your account is not protected! Need Rublon for more accounts?'),
            'line1Text' => $this->__('You are currently using the Rublon Personal API, which limits protection to 1 account per website (the administrator who has installed and activated the plugin).'),
            'line2Text' => $this->__('If you\'d like to protect more accounts, you need to upgrade to the Rublon Business API.'),
            'line3Text' => $this->__('You can easily order online.'),
            'upgradeNowText' => $this->__('Upgrade Now'),
            'smallHintText' => $this->__('After purchasing the upgrade, please logout and login again to activate your license.'),
            'notProtectedBoxTitle' => $this->__('Your accunt is not protected!'),
            'notProtectedText' => $this->__('Rublon Personal Edition secures only the administrator account. Please contact your administrator to upgrade to the Business Edition.'),
            'flushCacheUrl' => Mage::helper("adminhtml")->getUrl('rublon/adminhtml_settings/flush'),
            'flushCacheText' => $this->__('Clear cache') 
        );
        
        $this->getLayout()->getBlock('RublonModule')->assign('data', $data);
        // my stuff
        
        $this->renderLayout();
        
    }  

    protected function initialize() {
        
        if (!$this->helper->isRublonConfigured()) {
            $this->_redirect('rublon/adminhtml_activation');
        } else {
            // Retrive rublon features
            RublonFeature::getFeatures(false);
        }        
    }
    
    public function flushAction() {        
        $this->helper->cleanCache();
        
        // Retrive rublon features
        RublonFeature::getFeatures(false);
        
        $this->_redirect('rublon/adminhtml_settings');
    }
}