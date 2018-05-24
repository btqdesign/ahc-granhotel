
				jQuery(function(){

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

				})
						

				    jQuery(document).ready(function(){ 
  
				    jQuery('#vermas').toggle( 
				  
				        // Primer click
				        function(e){ 
				            jQuery('#mostrar #mostrar1').slideDown();
				            jQuery(this).text('');
				            e.preventDefault();
				            
				            
				        }, // Separamos las dos funciones con una coma
				      
				        // Segundo click
				        function(e){ 
				            jQuery('#mostrar #mostrar1').slideUp();
				            jQuery(this).text('Ver mas');
				            e.preventDefault();

				            
				        }
				  
				    );
				  
				});
						
					