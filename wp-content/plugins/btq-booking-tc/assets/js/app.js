jQuery(document).ready(function(){ 

	jQuery('#btq-date-start').datepicker({
		dateFormat: 'dd/mm/yy',
		maxViewMode: 3,
		language: 'es',
		autoclose: true,
		todayHighlight: true
	});
	
	jQuery('#btq-date-end').datepicker({
		dateFormat: 'dd/mm/yy',
		maxViewMode: 3,
		language: 'es',
		autoclose: true,
		todayHighlight: true
	});
	
	
	
	jQuery('#vermas').toggle( 
		// Primer click
		function(e){ 
			jQuery('#mostrar').slideDown();
			jQuery(this).text('');
			e.preventDefault();
		},
		
		// Segundo click
		function(e){ 
			jQuery('#mostrar').slideUp();
			jQuery(this).text('Ver mas');
			e.preventDefault();
		}
	);
	
	
	
	jQuery('#vermas1').toggle( 
		// Primer click
		function(z){ 
			jQuery('#mostrar1').slideDown();
			jQuery(this).text('');
			z.preventDefault();
		}, 
		
		// Segundo click
		function(z){ 
			jQuery('#mostrar1').slideUp();
			jQuery(this).text('Ver mas');
			z.preventDefault();
		}
	);
	
	
	
	jQuery('#vermas2').toggle( 
		// Primer click
		function(x){ 
			jQuery('#mostrar2').slideDown();
			jQuery(this).text('');
			x.preventDefault();
		}, 
		
		// Segundo click
		function(x){ 
			jQuery('#mostrar2').slideUp();
			jQuery(this).text('Ver mas');
			x.preventDefault();
		}
	);
	
	
	
	jQuery('#vermas3').toggle( 
		// Primer click
		function(y){ 
			jQuery('#mostrar3').slideDown();
			jQuery(this).text('');
			y.preventDefault();
		}, 
		
		// Segundo click
		function(y){ 
			jQuery('#mostrar3').slideUp();
			jQuery(this).text('Ver mas');
			y.preventDefault();
		}
	);
	
	
	
	jQuery('#btq-booking-tc-form').submit(false);
	jQuery('#btq-booking-tc-form').submit(function(e){ e.preventDefault(); });
	
	jQuery('#btq-bookin-tc-button-search').click(function() {
		jQuery("#wait").css("display", "block");
		
		jQuery('#btq-booking-tc-form').submit(function(e){ e.preventDefault(); });
		
		jQuery(".preloader").css("display", "block");
		jQuery.post(
		    '/wp-admin/admin-ajax.php', 
		    {
				'action' : 'btq_booking_tc_grid',
				'data' : {
					btq_date_start   : moment( jQuery('#btq-date-start').datepicker('getDate') ).format('YYYY-MM-DD'), 
					btq_date_end     : moment( jQuery('#btq-date-end').datepicker('getDate')   ).format('YYYY-MM-DD'),
					btq_type_query   : jQuery('#btq-type-query').val(),
					btq_num_rooms    : jQuery('#btq-num-rooms').val(),
					btq_num_adults   : jQuery('#btq-num-adults').val(),
					btq_num_children : jQuery("#btq-num-children").val()
				}
		    }, 
		    function(response) {
				jQuery('#btq-booking-grid').html(response);
				jQuery(".preloader").css("display", "none");
		    }
		)
		.done(function() {
			jQuery(".preloader").css("display", "none");
		})
		.fail(function() {
			jQuery(".preloader").css("display", "none");
		});
	});
	
	
	
});