
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
				  
				});



				   jQuery(document).ready(function(){ 
  
				    jQuery('#vermas1').toggle( 
				  
				        // Primer click
				        function(h){ 
				            jQuery('#mostrar1').slideDown();
				            jQuery(this).text('');
				            h.preventDefault();
				            
				            
				        }, 
				      
				        // Segundo click
				        function(h){ 
				            jQuery('#mostrar1').slideUp();
				            jQuery(this).text('Ver mas');
				            h.preventDefault();

				            
				        }
				  
				    );
				  
				});



				   