/**
 * Justboil.me - a TinyMCE image upload plugin
 * jbimages/js/dialog.js
 *
 * Released under Creative Commons Attribution 3.0 Unported License
 *
 * License: http://creativecommons.org/licenses/by/3.0/
 * Plugin info: http://justboil.me/
 * Author: Viktor Kuzhelnyi
 *
 * Version: 2.3 released 23/06/2013
 */


/* ====== Provide support if this .js file is used outside the context of tinyMCE ====== */
if(tinyMCEPopup){
	var usingFullTinyMCEPopup = true
} else {
	var tinyMCEPopup = {
		getLang: function(id){
			var p = id.split('.');
			console.log(tinyMCE);
			console.log(p);
			return tinyMCE['en.jbimages_dlg'][p[1]];
		}
	}
}

if(usingFullTinyMCEPopup){
	tinyMCEPopup.requireLangPack();
}


var jbImagesDialog = {
	
	resized : false,
	iframeOpened : false,
	timeoutStore : false,
	
	init : function() {
		document.getElementById("upload_target").src += '/' + tinyMCEPopup.getLang('jbimages_dlg.lang_id', 'english');
		if (navigator.userAgent.indexOf('Opera') > -1)
		{
			document.getElementById("close_link").style.display = 'block';
		}
	},
	
	inProgress : function() {
		document.getElementById("upload_infobar").style.display = 'none';
		document.getElementById("upload_additional_info").innerHTML = '';
		document.getElementById("upload_form_container").style.display = 'none';
		document.getElementById("upload_in_progress").style.display = 'block';
		this.timeoutStore = window.setTimeout(function(){
			document.getElementById("upload_additional_info").innerHTML = tinyMCEPopup.getLang('jbimages_dlg.longer_than_usual', 0) + '<br />' + tinyMCEPopup.getLang('jbimages_dlg.maybe_an_error', 0) + '<br /><a href="#" onClick="jbImagesDialog.showIframe()">' + tinyMCEPopup.getLang('jbimages_dlg.view_output', 0) + '</a>';
			//tinyMCEPopup.editor.windowManager.resizeBy(0, 30, tinyMCEPopup.id);
		}, 20000);
	},
	
	showIframe : function() {
		if (this.iframeOpened == false)
		{
			document.getElementById("upload_target").className = 'upload_target_visible';
			//tinyMCEPopup.editor.windowManager.resizeBy(0, 190, tinyMCEPopup.id);
			this.iframeOpened = true;
		}
	},
	
	uploadFinish : function(result) {
		if (result.resultCode == 'failed')
		{
			window.clearTimeout(this.timeoutStore);
			document.getElementById("upload_in_progress").style.display = 'none';
			document.getElementById("upload_infobar").style.display = 'block';
			document.getElementById("upload_infobar").innerHTML = result.result;
			document.getElementById("upload_form_container").style.display = 'block';
			
			if (this.resized == false)
			{
				//tinyMCEPopup.editor.windowManager.resizeBy(0, 30, tinyMCEPopup.id);
				this.resized = true;
			}
		}
		else
		{
			document.getElementById("upload_in_progress").style.display = 'none';
			document.getElementById("upload_infobar").style.display = 'block';
			document.getElementById("upload_infobar").innerHTML = tinyMCEPopup.getLang('jbimages_dlg.upload_complete', 0);
			var altText = prompt('Title your image for ADA compliance:');
			var assetId = result.asset.id;
			setAltText(result.asset.id, altText, 
				function(){
					$.get('/asset/get.php?asset_id=' + assetId, function(asset){
						$('#uploaded-images').prepend(buildAssetHTML(asset));
						$("#upload_form_container").fadeIn();
					});
				});
		}
	}
};
if(usingFullTinyMCEPopup){
	tinyMCEPopup.onInit.add(jbImagesDialog.init, jbImagesDialog);
	function insertImage(asset){
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img src="' + asset.imgSrc +'" alt="' + asset.alt + '" data-asset-id="'+asset.id+'"/>');
		tinyMCEPopup.close();
	}
}
