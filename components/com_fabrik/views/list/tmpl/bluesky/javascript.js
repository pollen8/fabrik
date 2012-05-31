/**
 * @author Robert
 */
head.ready(function() {
	$A($$('.fabrikList tr')).each(function(r){
		$(r).addEvent('mouseover', function(e){
			if (r.hasClass('oddRow0') || r.hasClass('oddRow1')){
				r.addClass('fabrikHover');
			}
		}, r);
		
		$(r).addEvent('mouseout', function(e){
			r.removeClass('fabrikHover');
		}, r);
		
		$(r).addEvent('click', function(e){
			if (r.hasClass('oddRow0') || r.hasClass('oddRow1')){
				$$('.fabrikList tr').each(function(rx){
					rx.removeClass('fabrikRowClick');
				});
				r.addClass('fabrikRowClick');
			}
		}, r);
	});
})
	
