jQuery(document).ready(function(){
	
	jQuery('#btq-date-start').datepicker({
		dateFormat: 'dd/mm/yy',
		maxViewMode: 3,
		language: 'es',
		autoclose: true,
		todayHighlight: true,
		minDate: 0
		onSelect: function(date){
			jQuery('#btq-date-end').datepicker( 'option', 'minDate', date);
	    }
	});
	
	jQuery('#btq-date-end').datepicker({
		dateFormat: 'dd/mm/yy',
		maxViewMode: 3,
		language: 'es',
		autoclose: true,
		todayHighlight: true,
		minDate: 0
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
		btq_btn_search();
	});
	
	jQuery('#btq-btn-rooms').click(function() {
		jQuery('#btq-btn-rooms').addClass('btn-default');
		jQuery('#btq-btn-packages').removeClass('btn-default');
		jQuery('#btq-btn-top').removeClass('btn-default');
		
		jQuery('#btq-type-query').val('rooms');
		
		var today = new Date();
		if((jQuery('#btq-date-start').datepicker('getDate') <= today) || (jQuery('#btq-date-end').datepicker('getDate') <= today)){
			btq_btn_rooms();
		}
		else if ((jQuery('#btq-date-start').datepicker('getDate') > today) && (jQuery('#btq-date-end').datepicker('getDate') > today)){
			btq_btn_search();
		}
		else {
			console.log('#btq-btn-rooms nada');
		}
	});
	
	jQuery('#btq-btn-packages').click(function() {
		jQuery('#btq-btn-rooms').removeClass('btn-default');
		jQuery('#btq-btn-packages').addClass('btn-default');
		jQuery('#btq-btn-top').removeClass('btn-default');
		
		jQuery('#btq-type-query').val('packages');
		
		var today = new Date();
		if((jQuery('#btq-date-start').datepicker('getDate') <= today) || (jQuery('#btq-date-end').datepicker('getDate') <= today)){
			btq_btn_packages();
		}
		else if ((jQuery('#btq-date-start').datepicker('getDate') > today) && (jQuery('#btq-date-end').datepicker('getDate') > today)){
			btq_btn_search();
		}
		else {
			console.log('#btq-btn-packages nada');
		}
	});
	
	jQuery('#btq-btn-top').click(function() {
		jQuery('#btq-btn-rooms').removeClass('btn-default');
		jQuery('#btq-btn-packages').removeClass('btn-default');
		jQuery('#btq-btn-top').addClass('btn-default');
		
		jQuery('#btq-type-query').val('rooms');
		
		var today = new Date();
		if((jQuery('#btq-date-start').datepicker('getDate') <= today) || (jQuery('#btq-date-end').datepicker('getDate') <= today)){
			btq_btn_rooms();
		}
		else if ((jQuery('#btq-date-start').datepicker('getDate') > today) && (jQuery('#btq-date-end').datepicker('getDate') > today)){
			btq_btn_search();
		}
		else {
			console.log('#btq-btn-rooms nada');
		}
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
	
	function btq_btn_rooms(){
		console.log('#btq-btn-rooms click function');
		
		jQuery('#btq-booking-tc-form').submit(function(e){ e.preventDefault(); });
		
		jQuery("#wait").css("display", "block");
		
		jQuery(".preloader").css("display", "block");
		jQuery.post(
		    '/wp-admin/admin-ajax.php', 
		    {
				'action' : 'btq_booking_tc_grid_rooms',
				'data' : {
					btq_rooms_init : 'OK'
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
	
	function btq_btn_search(){
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
	}
});