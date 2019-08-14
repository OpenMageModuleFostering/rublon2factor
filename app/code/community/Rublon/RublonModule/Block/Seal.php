<?php
/**
 * Rublon Seal logo
 *
 * @package   rublon/rublon2factor
 * @author     Adips Sp. z o.o.
 * @copyright  Adips Sp. z o.o.
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

/**
 * Rublon Seal logo block
 */
class Rublon_RublonModule_Block_Seal extends Mage_Core_Block_Template
{
	/**
	 * Returns the html code of Rublon seal
	 *
	 * @return string
	 */
	protected function _toHtml() {
	
	  	$whereIam = Mage::getDesign()->getArea();
	
	  	if ($whereIam == 'adminhtml') {
		    $sealCSS = 'position:relative;top:35px;text-align:center;width:96px;';
	    } else {
	      $sealCSS = 'margin-top: 30px; float: right;top:25px;';            
	    } 
	
	    $badgeImgUrl = Mage::getDesign()->getSkinBaseUrl() . 'Rublon/img/rublon_badge.svg';
	    
		$helper = Mage::helper('rublonmodule');
		return sprintf(<<<'END'
		    
			<div style="%s" id="RublonSeal">
				<div id="rublon-seal"><div class="rublon-seal-link"><div id="RublonBadgeWidget"><img src="%s"></div><a style="color:#0073aa;text-decoration:none;font-size:10px" id="RublonBadgeLink" href="https://rublon.com" target="_blank">Rublon 2FA</a></div></div>
			</div>
			<script type="text/javascript">
		    
		    (function (d, s, id) { var js; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "https://code.rublon.com/rublon-sdk-js-sa"; var p = d.getElementsByTagName(s)[0]; p.parentNode.insertBefore(js, p); })(document, "script", "RublonConsumerJs-sdk");
		    
			if (document.addEventListener) {
				document.addEventListener("DOMContentLoaded", function() {
					if (document.getElementById("loginForm")) { // admin login
						var seal = document.getElementById("RublonSeal");
						var node = seal.parentNode;
						node.removeChild(seal);
						node.appendChild(seal);
					}
				});
			}
			</script>
END
			, $sealCSS, $badgeImgUrl );
	}
}
 

?>