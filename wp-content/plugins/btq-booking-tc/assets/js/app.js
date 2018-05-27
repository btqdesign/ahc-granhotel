jQuery(document).ready(function(){ 

	jQuery('#entrada').datepicker({
		dateFormat: 'dd/mm/yy',
		maxViewMode: 3,
		language: 'es',
		autoclose: true,
		todayHighlight: true
	});
	
	jQuery('#salida').datepicker({
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
					entrada : moment(jQuery("#entrada").datepicker("getDate")).format('YYYY-MM-DD'), 
					salida  : moment( jQuery("#salida").datepicker("getDate")).format('YYYY-MM-DD'),
					adultos : jQuery("#adultos").val(),
					ninos   : jQuery("#ninos").val(),
					roomAmount : 1
				}
		    }, 
		    function(response) {
				console.log("Respnse:\n\n");
				console.log(response);
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