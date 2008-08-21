<?php
header("Content-type: text/javascript");
?>


CCTI = {

	drawing_id: null,
	activeEdit: null,
	activeEditCellOldContent: null,

	debug: function(msg) {
		console.debug(msg);
	},

	editContent: function(cell, program, section, row, col) {
		if( this.activeEdit == null ) {
			this.activeEdit = {cell: cell, program: program, section: section, row: row, col: col};
			this.activeEditCellOldContent = cell.innerHTML;
			if( cell.innerHTML == '&nbsp;' ) cell.innerHTML = '';
			cell.innerHTML = '<textarea style="width:100%; height:100%">' + cell.innerHTML + '</textarea><br /><a href="javascript:CCTI.saveContent()" class="tiny">save</a> <a href="javascript:CCTI.cancelContent()" class="tiny">cancel</a>';
			cell.firstChild.focus();
		}
	},

	saveContent: function() {
		this.ajax( {
			action: "content",
			program: this.activeEdit.program, 
			section: this.activeEdit.section, 
			row: this.activeEdit.row, 
			col: this.activeEdit.col,
			content: this.activeEdit.cell.firstChild.value,
			} );
		this.activeEdit.cell.innerHTML = this.activeEdit.cell.firstChild.value;
		this.activeEdit = null;
	},

	cancelContent: function() {
		this.activeEdit.cell.innerHTML = this.activeEditCellOldContent;
		this.activeEdit = null;
		this.activeEditCellOldContent = null;
	},


	ajax: function(post, callback) {
		post.drawing_id = this.drawing_id;
		var params = this.toPost(post);
	
		if (window.XMLHttpRequest) {
			var ajax = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			var ajax = new ActiveXObject("Microsoft.XMLHTTP");
		}

		ajax.onreadystatechange = function () {
								  CCTI.ajaxRsc(ajax, callback);
								}

		ajax.open('POST', 'cctiserv.php', true);
		ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		if (document.cookie) ajax.setRequestHeader('Cookie', document.cookie);
		ajax.setRequestHeader('Content-length', params.length);
		ajax.setRequestHeader('Connection', 'close');
		ajax.send(params);
	},

	ajaxRsc: function(ajax, callback) {
	  if (ajax.readyState == 4) {
		if (typeof(callback) !== 'undefined') {
		  if (ajax.status == 200) callback(ajax);
		  else callback(false);
		}
	  }
	},

	toPost: function(obj,path,new_path) {
	  if (typeof(path) == 'undefined') var path=[];
	  if (typeof(new_path) != 'undefined') path.push(new_path);
	  var post_str=[];
	  if (typeof(obj) == 'array' || typeof(obj) == 'object') for (var n in obj) post_str.push(this.toPost(obj[n],path,n));
	  else {
	    var base = path.shift();
	    post_str.push(base + (path.length > 0 ? '[' + path.join('][') + ']' : '') + '=' + encodeURIComponent(obj).replace(/&/g, '%26'));
	    path.unshift(base);
	  }
	  path.pop();
	  return post_str.join('&');
	}

	
};



