
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
				  
				});

				jQuery(document).ready(function(){ 
  
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
				  
				});


				jQuery(document).ready(function(){ 
  
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
				  
				});

				  



				  


				   