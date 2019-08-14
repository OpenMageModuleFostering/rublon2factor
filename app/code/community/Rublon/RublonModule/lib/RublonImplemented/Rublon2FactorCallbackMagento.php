<?php

require_once dirname (__FILE__) . '/../Rublon/Rublon2FactorCallback.php';

class Rublon2FactorCallbackMagento extends Rublon2FactorCallback {
    
    private $state;
    private $accessToken;

    public function __construct(Rublon2Factor $rublon) {
        parent::__construct($rublon);
    }
    
    public function setState($state) {
        $this->state = $state;
    }
    
	public function getState() {
	    return $this->state;
	} 

	public function setAccessToken($token) {
	    $this->accessToken = $token;
	}
	
	public function getAccessToken() {
	    return $this->accessToken;
	}
	
	public function handleLogout($userId, $deviceId) {
// 	    $dir =  Mage::getBaseDir('var') . '/session/';
// 	    Mage::getSingleton("core/session")->getEncryptedSessionId();
// 	    if (is_dir($dir)) {
// 	        if ($dh = opendir($dir)) {
// 	            while (($file = readdir($dh)) !== false) {
// 	                echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
// 	            }
// 	            closedir($dh);
// 	        }
// 	    }
	    
	}

}
