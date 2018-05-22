
				$(function(){

				$('#entrada').datepicker({
						 format: "dd/mm/yyyy",
						 maxViewMode: 3,
						 language: "es",
						 autoclose: true,
						 todayHighlight: true
						});

				$('#salida').datepicker({
						 format: "dd/mm/yyyy",
						 maxViewMode: 3,
						 language: "es",
						 autoclose: true,
						 todayHighlight: true

						});

				})
						

				    $(document).ready(function(){ 
  
				    $('#vermas').toggle( 
				  
				        // Primer click
				        function(e){ 
				            $('#mostrar').slideDown();
				            $(this).text('');
				            e.preventDefault();
				        }, // Separamos las dos funciones con una coma
				      
				        // Segundo click
				        function(e){ 
				            $('#mostrar').slideUp();
				            $(this).text('Ver mas');
				            e.preventDefault();
				        }
				  
				    );
				  
				});
						
					