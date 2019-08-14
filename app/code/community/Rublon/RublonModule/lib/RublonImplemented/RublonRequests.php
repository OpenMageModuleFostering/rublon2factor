<?php
/**
 * Class for performing various requests to Rublon servers 
 *
 * @package   rublon2factor\includes
 * @author     Rublon Developers http://www.rublon.com
 * @copyright  Rublon Developers http://www.rublon.com
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class RublonRequests {

	const ERROR_NL_API = 'NEWSLETTER_API_ERROR';
	const ERROR_NL_RUBLON_API = 'NEWSLETTER_RUBLON_API_ERROR';
	const ERROR_ALREADY_SUBSCRIBED = 'NEWSLETTER_ALREADY_SUBSCRIBED_ERROR';
	const ERROR_INVALID_NONCE = 'NEWSLETTER_INVALID_NONCE_ERROR';
	const ERROR_RUBLON_NOT_CONFIGURED = 'RUBLON_NOT_CONFIGURED';

	const SUCCESS_NL_SUBSCRIBED_SUCCESSFULLY = 'NEWSLETTER_SUBSCRIBE_OK';
	

	/**
	 * Rublon2Factor instance
	 * 
	 * @var Rublon2Factor
	 */
	protected $rublon;


	/**
	 * Constructor
	 */
	public function __construct() {

		$this->rublon = Mage::helper('rublonmodule')->getRublon();
    
	}


	/**
	 * Check mobile app status of a single WP user
	 * 
	 * @param WP_User $user
	 * @return string RublonHelper constant
	 */
	public function checkMobileStatus($user_id, $user_email) {
        $helper = Mage::helper('rublonmodule');
        
		if ($helper->isRublonConfigured()) {
    		require_once dirname(__FILE__) . '/RublonAPICheckProtection.php';
    		$check = new RublonAPICheckProtection(
    			$this->rublon,
    			$user_id,
    			$user_email
    		);
    		try {
    			$check->perform();
    		} catch (RublonException $e) {
    			$check = null;
    		}    		
    		
    		if (!empty($check) && $check->isProtectionEnabled($user_id)) {
    			$mobile_user_status = RublonMagentoModule::YES;
    		} else {
    			$mobile_user_status = RublonMagentoModule::NO;
    		}
    		
    		return $mobile_user_status;
		} else {
			return self::ERROR_RUBLON_NOT_CONFIGURED;
		}

	}

	public function subscribeToNewsletter($email) {
	    $helper = Mage::helper('rublonmodule');
	    
		if ($helper->isRublonConfigured()) {
			require_once dirname(__FILE__) . '/RublonAPINewsletterSignup.php';
			$signup = new RublonAPINewsletterSignup($this->rublon, $email);
			try {
				$signup->perform();
				$result = $signup->subscribedSuccessfully();
			} catch (RublonException $e) {
				if ($e instanceof RublonAPIException) {
					$response = $e->getClient()->getResponse();
					if (!empty( $response[RublonAPINewsletterSignup::FIELD_RESULT] )
						&& !empty( $response[RublonAPINewsletterSignup::FIELD_RESULT]['exception'] )
						&& $response[RublonAPINewsletterSignup::FIELD_RESULT]['exception'] == 'AlreadySubscribed_NewsletterException') {
						$result = self::ERROR_ALREADY_SUBSCRIBED;
					} else {
						$result = self::ERROR_NL_API;
					}
				} else {
					$result = self::ERROR_NL_RUBLON_API;
				}
			}
			return ($result !== false) ? $result : self::ERROR_NL_RUBLON_API;
		} else {
			return self::ERROR_RUBLON_NOT_CONFIGURED;
		}

	}


}