<?php

class Rublon_RublonModule_Adminhtml_ActivationController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * 
     * @var Rublon_RublonModule_Helper_Data
     */
    private $helper;
    
    public function _construct() {
        $this->helper = Mage::helper('rublonmodule');                               
    }
    
    public function indexAction() {        
        
        $this->loadLayout()
        ->_setActiveMenu('rublonmodule')
        ->_title($this->__('Rublon Two-Factor authentication'));

        $email = Mage::helper('rublonmodule')->getAuthUserEmail();
        $profilePageUrl = Mage::helper('adminhtml')->getUrl('adminhtml/system_account');
        
        $data = array(
            'logo_url' => Mage::getDesign()->getSkinBaseUrl() . 'Rublon/img/rublon_logo_32x32.png',
            'rublon_visual_url' => Mage::getDesign()->getSkinBaseUrl() . 'Rublon/img/rublon_visual.gif',
            'path' => $this->__('Rublon Two-Factor authentication activation'),
            'title' => $this->__('Help improve Rublon Account Security'),
            'allow_message' => $this->__('Thank you for installing Rublon Account Security for Magento! Please help us improve it by allowing us to gather configuration data that give us the possibility to maintain high compatibility with different servers, plugins and themes.'),
            'api_message' => array(
                $this->__('Rublon Account Security works by talking to the Rublon API. This requires an API key, which needs to be generated specifically for your website.'),
                sprintf($this->__('Due to security reasons, this requires a registration with your email address: <strong>%s</strong>. In order to register with a different email address, change it in your <a href="%s">profile settings</a>.'), $email, $profilePageUrl)
            ),
            'registration_init_action' => Mage::helper('adminhtml')->getUrl('rublon/adminhtml_activation/initialize')
        );
        
        $this->getLayout()->getBlock('RublonModule')->assign($data);        
   
        $this->renderLayout();
        
    }  

    public function initializeAction() {        
                
        if (!$this->helper->isRublonConfigured()) {            
            // Load Rublon activation layout
            $this->loadLayout()
            ->_setActiveMenu('rublonmodule')
            ->_title($this->__('Index Action'));
            
            // Create registration object instance
            $consumer_registration = new RublonConsumerRegistrationMagento();
            
            // Get request data
            $request = $this -> getRequest() -> getParams();
                        
            if (!empty($request['apiregAllowTracking'])) {

                // Save allow tracking
                $consumer_registration->saveAllowTracking($request['apiregAllowTracking']);
                
                // Notify about tracking enabled
                try {
                    $this->helper->getIssueNotifier()->setSilence(true);
                    $this->helper->getIssueNotifier()->notify('Preliminary statistics from a Magento store.', array('message-type' => RublonMagentoModule::RUBLON_NOTIFY_TYPE_STATS));
                    $this->helper->getIssueNotifier()->setSilence(false);
                } catch (Exception $e) {
                    // Do nothing.
                }
            }            
            
            if (!empty($request['apiregNewsletterSignup'])) {
                // Save newsletter signup
                $consumer_registration->saveNewsletterSignUp($request['apiregNewsletterSignup']);
            }
            
            // Wrap registration form to submit registration data
            $data = array(
                'logo_url' => Mage::getDesign()->getSkinBaseUrl() . 'Rublon/img/rublon_logo_32x32.png',
                'rublon_visual_url' => Mage::getDesign()->getSkinBaseUrl() . 'Rublon/img/rublon_visual.gif',
                'path' => $this->__('Rublon Two-Factor authentication activation'),
                'registration_form_hidden' => $consumer_registration->retrieveRegistrationForm()
            );
//             $block = $this->getLayout()->createBlock('core/text')->setText($consumer_registration->retrieveRegistrationForm());
//             $this->_addContent($block);
//             var_dump($data); die();
            $this->getLayout()->getBlock('RublonModule')->assign($data);
             
            $this->renderLayout();
        }
    }
    
}