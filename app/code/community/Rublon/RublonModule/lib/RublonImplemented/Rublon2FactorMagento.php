<?php

require_once dirname (__FILE__) . '/../Rublon/Rublon2Factor.php';

class Rublon2FactorMagento extends Rublon2Factor {


	public function canUserActivate() {
	            
		return (Mage::helper('rublonmodule')->isAdministrator());

	}


	public function getLang() {

		return Mage::app()->getLocale()->getLocaleCode();

	}


	public function getAPIDomain() {

		return Mage::helper('rublonmodule')->getRublonDomain();

	}


}
