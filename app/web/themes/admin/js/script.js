
(function($) {
$(function() {

	if( moment ) {
//		console.log("Set moment locale to "+$('html').attr("lang"));
		moment.locale($('html').attr("lang"));
	}
	
	// Mask input
	if( $.fn.mask ) {
		$.mask.definitions['s']='[0-6]';
		$("input[data-mask]").each(function() {
			var options = {};
//			console.log("mask", this, $(this).data("mask-autoclear"));
			if( $(this).data("mask-autoclear") !== undefined ) {
				options.autoclear	= $(this).data("mask-autoclear");
//				console.log("options", options);
			}
			$(this).mask($(this).data("mask")+"", options);
		});
	}
	
	$("[data-form-group]").each(function() {
		$(this).find(":input").attr('data-parsley-group', $(this).data('form-group'));
	});

});
})(jQuery);

