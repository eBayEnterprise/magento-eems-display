/**
 * Copyright (c) 2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */
var EemsDisplayFeedUrl = new Class.create();
EemsDisplayFeedUrl.prototype = {
	initialize : function(elemId, ajaxUrl, failMsg){
		this.elemId = elemId;
		this.ajaxUrl = ajaxUrl;
		this.failMsg = failMsg;
	},
	setup: function() {
		var checkBoxEl = $(this.elemId + '_inherit');
		if (checkBoxEl != 'undefined' && checkBoxEl != null){
			checkBoxEl.hide();
		}
		var feedLinkUrl = $(this.elemId);
		if (feedLinkUrl != 'undefined' && feedLinkUrl != null){
			feedLinkUrl.click(function(e) {e.preventDefault();});
		}
	},
	removeLockFile: function() {
		var elem = $(this.elemId + '_remove_lock_file');

		new Ajax.Request(this.ajaxUrl, {
			'parameters': {},
			'onSuccess': function(xhrResponse) {
				var response = {};
				try {
					response = xhrResponse.responseText.evalJSON();
				} catch (e) {
					response.success = false;
					response.message = this.failMsg;
				}
				if (response.success) {
					elem.removeClassName('fail').addClassName('success');
				} else {
					elem.removeClassName('success').addClassName('fail');
				}
				$('eems_display_lockfile_deleting_result').update(response.message);
			}
		});
	}
};
