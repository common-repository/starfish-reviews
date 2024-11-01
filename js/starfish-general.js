String.prototype.format = function () {
    var args = arguments;
    return this.replace(/\{\{|\}\}|\{(\d+)\}/g, function (m, n) {
        if (m === "{{") {
            return "{";
        }
        if (m === "}}") {
            return "}";
        }
        return args[n];
    });
};

function getQueryParams() {
    var params = {};
    var ps = window.location.search.split(/\?|&/);
    for (var i = 0; i < ps.length; i++) {
        if (ps[i]) {
            var p = ps[i].split(/=/);
            params[p[0]] = p[1];
        }
    }
    return params;
}

/* Disable conflicting/offensive stylesheets and javascript files that may be present with Starfish Pages */
jQuery(document).ready(function ($) {
	// Gravity Forms
	$("link[href*='/gravityforms/css/font-awesome.min.css']").prop('disabled', true);
	$("link[href*='/gravityforms/css/font-awesome.min.css']").remove();
	// WPTouch
	$("link[src*='fastclick']").prop('disabled', true);
	$("link[src*='fastclick']").remove();
});

/** Validators **/
function isEmail(email) {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email)
}

function validateUrl($, element) {
	const errorClass = 'srm-val-error';
	const infoClass = 'srm-val-info';
	if (element.val().indexOf('https://') === 0 || element.val().indexOf('http://') === 0 || element.val().indexOf('fb://') === 0) {
		element.removeClass(errorClass);
		element.next('.srm-val-error-msg').remove();
		$('#publish').prop('disabled', false);
		if (element.val().indexOf('fb://') === 0 && !element.hasClass(infoClass)) {
			element.addClass(infoClass);
			element.after('<div class="srm-val-info-msg" style="color:orange">Warning: This URL format will only work on mobile devices with the facebook app installed.</div>');
		}
	} else if (!element.hasClass(errorClass)) {
		element.addClass(errorClass);
		element.after('<div class="srm-val-error-msg" style="color:red">Invalid URL (i.e. "http://" or "https://" or "fb://")</div>');
		$('#publish').prop('disabled', true);
	}
}