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
	
	
	
	$('#btq-bookin-tc-button-search').click(function() {
		//$("#wait").css("display", "block");
		console.log('btq-bookin-tc-button-search click');
		/*
		ini = moment($("#bookIn").datepicker("getDate")).format('YYYY/MM/DD');
		out = moment($("#bookOut").datepicker("getDate")).format('YYYY/MM/DD');
		console.log('+++ ini: ' + ini + ' +++ out: ' + out);
		lang = window.location.pathname == "/" ? "en" : "es";
		console.log("idioma: ", lang, "ini: ", ini, "out: ", out, "lang: ", lang, "new ini:", $("#bookIn").val(), "new fin: ", $("#bookOut").val());
		$(".preloader").css("display", "block");
		$.post(
		    ajaxurl, 
		    {
				'action' : 'grid_gran_hotel_ajax',
				'data' : {
						bookIn : ini.replace(/\//g, "-"), 
						bookOut : out.replace(/\//g, "-"),
						adultsAmount : $("#adults").val(),
						childrenAmount : $("#children").val(),
						roomAmount : $("#roomsAmount").val(),
						lang: lang
					}
		    }, 
		    function(response) {
				console.log("Respnse:\n\n");
				console.log(response);
				console.log("fin");
				$('#grid-gran-hotel-print').html(response);
				$(".preloader").css("display", "none");
		    }
		)
		.done(function() {
			$(".preloader").css("display", "none");
		})
		.fail(function() {
			$(".preloader").css("display", "none");
		});
		*/
	});
});