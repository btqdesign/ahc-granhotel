
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
	
	function vermas() {
		jQuery('.texto_recorrido').hide();
		jQuery('.vermas').click(function () {
			jQuery(this).parent().children('.texto_recorrido').slideToggle(200);
			jQuery(this).text('');
			jQuery(this).parent().css('display','inherit');
			jQuery(this).parent().children('.texto_recorrido').css('display','contents');
			
		});
		jQuery('.texto_recorrido').slideUp(200);
	}
	vermas();

	
	
	jQuery('#btq-booking-tc-form').submit(false);
	
	jQuery('#btq-search').click(function() {
		console.log('#btq-search click function');
		
		jQuery('#btq-booking-tc-form').submit(function(e){ e.preventDefault(); });
		
		jQuery("#wait").css("display", "block");
		
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
				vermas();
		    }
		)
		.done(function() {
			jQuery(".preloader").css("display", "none");
		})
		.fail(function() {
			jQuery(".preloader").css("display", "none");
		});
	});
	
	
	
	jQuery('#btq-btn-rooms').click(function() {
		jQuery('#btq-btn-rooms').addClass('btn-default');
		jQuery('#btq-btn-packages').removeClass('btn-default');
		jQuery('#btq-btn-top').removeClass('btn-default');
		
		jQuery('#btq-type-query').val('rooms');
	});
	
	jQuery('#btq-btn-packages').click(function() {
		jQuery('#btq-btn-rooms').removeClass('btn-default');
		jQuery('#btq-btn-packages').addClass('btn-default');
		jQuery('#btq-btn-top').removeClass('btn-default');
		
		jQuery('#btq-type-query').val('packages');
		
		if((jQuery('#btq-date-start').val() == undefined || jQuery('#btq-date-start').val() == '') || (jQuery('#btq-date-end').val() == undefined || jQuery('#btq-date-end').val() == '')){
			btq_btn_packages();
		}
		else {
			console.log('#btq-btn-packages nada');
			var today = new Date();
			console.log(today);
			console.log(jQuery('#btq-date-start').datepicker('getDate'));
			console.log(jQuery('#btq-date-end').datepicker('getDate'));
		}
	});
	
	jQuery('#btq-btn-top').click(function() {
		jQuery('#btq-btn-rooms').removeClass('btn-default');
		jQuery('#btq-btn-packages').removeClass('btn-default');
		jQuery('#btq-btn-top').addClass('btn-default');
		
		jQuery('#btq-type-query').val('rooms');
	});
	
	function btq_btn_packages(){
		console.log('#btq-btn-packages click function');
		
		jQuery('#btq-booking-tc-form').submit(function(e){ e.preventDefault(); });
		
		jQuery("#wait").css("display", "block");
		
		jQuery(".preloader").css("display", "block");
		jQuery.post(
		    '/wp-admin/admin-ajax.php', 
		    {
				'action' : 'btq_booking_tc_grid_packages',
				'data' : {
					btq_packages_init : 'OK'
				}
		    }, 
		    function(response) {
				jQuery('#btq-booking-grid').html(response);
				jQuery(".preloader").css("display", "none");
				vermas();
		    }
		)
		.done(function() {
			jQuery(".preloader").css("display", "none");
		})
		.fail(function() {
			jQuery(".preloader").css("display", "none");
		});
	}
});