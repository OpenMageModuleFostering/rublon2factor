<?php
/**
 * Rublon administrator model
 *
 * @package   rublon/rublonmodule
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon administrator model class
 * 
 * This entity contains all customer scoped rublon data including profile ID.
 */
class Rublon_RublonModule_Model_User extends Mage_Core_Model_Abstract
{
	/**
	 * Default constructor
	 *
	 */
	protected function _construct()
	{
		$this->_init('rublon2factor/user');
	}

	/**
	 * loads by Rublon profile id
	 *
	 * @param int $rublonProfileId
	 * @return Rublon_Rublon2Factor_Model_User
	 */
	public function loadByRublonProfileId($rublonProfileId)
	{
		return $this->load($rublonProfileId,'rublon_profile_id');
	}
	
	
	
}

?>