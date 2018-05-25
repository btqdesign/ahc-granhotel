jQuery(document).ready(function(){ 

	jQuery('#entrada').datepicker({
		format: "dd/mm/yyyy",
		maxViewMode: 3,
		language: "es",
		autoclose: true,
		todayHighlight: true
	});
	
	jQuery('#salida').datepicker({
		format: "dd/mm/yyyy",
		maxViewMode: 3,
		language: "es",
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
	
	
	
	jQuery('#btq-booking-tc-datepicker-form').submit(false);
	
	jQuery('#btq-bookin-tc-button-search').click(function() {
		jQuery("#wait").css("display", "block");
		//jQuery('#btq-bookin-tc-button-search').disabled = true;
		
		console.log('btq-bookin-tc-button-search click');
		jQuery(".preloader").css("display", "block");
		jQuery.post(
		    '/wp-admin/admin-ajax.php', 
		    {
				'action' : 'btq_booking_tc_grid',
				'data' : {
					bookIn : '2018-05-25', 
					bookOut : '2018-05-26',
					adultsAmount : 1,
					childrenAmount : 2,
					roomAmount : 1
				}
		    }, 
		    function(response) {
				console.log("Respnse:\n\n");
				console.log(response);
				console.log("fin");
				//jQuery('#grid-gran-hotel-print').html(response);
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