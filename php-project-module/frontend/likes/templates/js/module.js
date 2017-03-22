var _HOST_, grecaptcha;

(function( $ ) {
	'use strict';
	
	var ajaxUrl = "/_includes_/redesign/pageblocks/common/likes/ajax.php";
	
	var classes = {
		likesDefer: "likes-defer"
		, likesDeferLoaded: "likes-defer_loaded"
		, likes: "likes"
		, buttonLike: "likes__like"
		, buttonDislike: "likes__dislike"
	};
	
	function init() {
		$("." + classes.likes).each(function(index, elem) {
			if (!elem.__initialized) {
				elem.__initialized = true;
				initElem(elem);
			}
		});
		
		initDefers();
	}
	
	function initElem( elem ) {
		var matType = $(elem).attr("data-mat-type");
		var matId = parseInt($(elem).attr("data-mat-id"));
		var likedByMe = parseInt($(elem).attr("data-liked-by-me")) ? true : false;
		var dislikedByMe = parseInt($(elem).attr("data-disliked-by-me")) ? true : false;
		var needLogin = false;
		
		var likeElem = $(elem).find("." + classes.buttonLike);
		var dislikeElem = $(elem).find("." + classes.buttonDislike);
		
		likeElem.on("click", onLike);
		dislikeElem.on("click", onDislike);
		
		function logIn() {
			document.location.href = "/login";
		}
		
		function onLike( e ) {
			if (needLogin) {
				return logIn();
			}
			var params = {
				action: likedByMe ? "unrate" : "like"
				, matType: matType
				, matId: matId
				, H: _HOST_
				, l: document.location.href
			};
			$.post(ajaxUrl, params, onLoad);
		}
		
		function onDislike( e ) {
			if (needLogin) {
				return logIn();
			}
			var params = {
				action: dislikedByMe ? "unrate" : "dislike"
				, matType: matType
				, matId: matId
				, H: _HOST_
				, l: document.location.href
			};
			$.post(ajaxUrl, params, onLoad);
		}
		
		function onLoad( jsonStr ) {
			var json = JSON.parse(jsonStr);
			if (!json.success) {
				onError(json.errorMessage);
			} else {
				onSuccess(json.data);
			}
		}
		
		function onError( text ) {
			console.warn("onError()", text);
		}
		
		function onSuccess( data ) {
			if (data.action == "redirect") {
				document.location.href = data.uri;
				return;
			}
			
			elem.insertAdjacentHTML("beforebegin", data.ratingHtml);
			if (elem.parentNode) {
				elem.parentNode.removeChild(elem);
			}
			
			init();
			document.dispatchEvent(new Event("init"));
		}
	}
	
	function initDefers() {
		var elems = defersFindAndInit($("." + classes.likesDefer));
		var matDict = defersToMatDict(elems);
		if (!matDict) {
			return;
		}
		
		var params = {
			action: "rating"
			, matDict: JSON.stringify(matDict)
			, H: _HOST_
			, l: document.location.href
		};
		$.post(ajaxUrl, params, onRatingLoad);
		
		function onRatingLoad( jsonStr ) {
			var json = JSON.parse(jsonStr);
			if (!json.success) {
				console.warn(json.errorMessage);
				return;
			}
			var data = json.data;
			if (data.action == "redirect") {
				document.location.href = data.uri;
				return;
			}
			if (data.action == "rating") {
				handleRenderedDict(data.dict);
				return;
			}
		}
		
		function handleRenderedDict( htmlDict ) {
			for (var i = 0; i < elems.length; i++) {
				var elem = elems[i];
				var matType = $(elem).attr("data-mat-type");
				var matId = parseInt($(elem).attr("data-mat-id"));
				
				var d = htmlDict[matType] || {};
				var node = d[matId] || {};
				var html = node.ratingHtml || "";
				
				$(elem).html(html);
				$(elem).addClass(classes.likesDeferLoaded);
			}
			init();
			document.dispatchEvent(new Event("init"));
		}
	}
	
	function defersFindAndInit( elems ) {
		var outs = [];
		for (var i = 0; i < elems.length; i++) {
			var elem = elems[i];
			if (!elem.__initialized) {
				elem.__initialized = true;
				outs.push(elem);
			}
		}
		return outs;
	}
	
	function defersToMatDict( elems ) {
		var dict = null;
		for (var i = 0; i < elems.length; i++) {
			var elem = elems[i];
			var matType = $(elem).attr("data-mat-type");
			var matId = parseInt($(elem).attr("data-mat-id"));
			dict = dict || {};
			dict[matType] = dict[matType] || {};
			dict[matType][matId] = 1;
		}
		return dict;
	}
	
	init();
	$(document).ready(init);
	$(document).on("init", init);
	
})( jQuery );
