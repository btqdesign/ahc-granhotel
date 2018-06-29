<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://cliengo.com
 * @since      1.0.0
 *
 * @package    Cliengo
 * @subpackage Cliengo/admin/partials
 */
?>
<script type="text/javascript">
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
</script>
<div id="app">
	<div class="container" style="margin-top: 5%">
		<div class="row col-lg-12" style="margin-bottom: 20px;">
			<?php echo '<img src="'.plugin_dir_url(__FILE__) . '../images/logo.svg'.'" alt="">' ?>
		</div>
		<div class="row col-lg-12">
			<div class="panel panel-success">
				<div class="panel-heading">
					<h4><?php _e( 'Do you already have a Cliengo account?', 'cliengo' ) ?></h4>
				</div>
			  	<div class="panel-body">
			  		<div class="radio">
  						<label>
					    	<input type="radio" v-model="option_select" value="true">
					    	<?php _e( 'Yes', 'cliengo' ) ?>
					  	</label>
					</div>
					<div class="radio">
					  	<label>
					    	<input type="radio" v-model="option_select" value="false">
					    	<?php _e( 'No, I want to create one', 'cliengo' ) ?>
					  	</label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<template>
		<div class="container" style="margin-top: 2%" v-if="option_select == 'true' ">
			<div class="row col-lg-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h4><?php _e( 'Cliengo account', 'cliengo' ) ?></h4>
					</div>
				  	<div class="panel-body">
				  		<div :class="{ 'alert':true, 'alert-dismissible':true, 'alert-danger': alertDanger,'alert-success':alertSuccess }" role="alert" v-show="message_alert == 'true' ">
  							<button type="button" class="close" aria-label="Close" @click="close_message_alert()"><span aria-hidden="true">&times;</span></button>
  							<strong v-show="message_validate_token_error == 'true' ">
  								<?php _e( 'Chatbot token entered is incorrect', 'cliengo' ) ?>
  							</strong>
  							<strong v-show="message_update_token_success == 'true' ">
  								<?php _e( 'Chatbot token has been successfully updated', 'cliengo' ) ?>
  							</strong>
  							<strong v-show="message_update_token_error == 'true' ">
  								<?php _e( 'Chatbot token update failed', 'cliengo' ) ?>
  							</strong>
						</div>
						<div class="form-horizontal">
							<div class="row">
				  				<label for="chatbot_token" class="col-md-2 control-label" style="text-align: left;">Chatbot Token:
				  				</label>
				  				<div class="col-md-5">
					  				<input type="text" name="chatbot_token" id="chatbot_token" class="form-control" v-model="chatbot_token">  	  	
				  				</div>
							</div>
							<div class="row" style="margin-top: 10px;">
				  				<label for="position_chatbot" class="col-md-2 control-label" style="text-align: left;">
				  					<?php _e( 'Position Chatbot', 'cliengo' ) ?>
				  				</label>
				  				<div class="col-md-5">
					  				<select name="position_chatbot" id="position_chatbot" class="form-control" v-model="position_chatbot">
					  					<option value="right">
					  						<?php _e( 'Right', 'cliengo' ) ?>
										</option>
					  					<option value="left">
					  						<?php _e( 'Left', 'cliengo' ) ?>
					  					</option>
					  				</select>  	  	
				  				</div>
							</div>
		  				</div>
		  				<button class="btn btn-info" @click="updateChatbotToken()" style="margin-top: 10px;">
		  					<?php _e( 'Save changes', 'cliengo' ) ?>
		  				</button>
					</div>
				</div>
			</div>
		</div>
		<div class="container" style="margin-top: 2%" v-if="option_select == 'false' ">
			<div class="row col-lg-12">
				<div class="panel panel-info">
					<div class="panel-heading">
						<h4><?php _e( 'Register in less than a minute', 'cliengo' ) ?></h4>
					</div>
				  	<div class="panel-body">
			  			<a href="https://start.cliengo.com/?utm_source=wordpress_plugin&utm_medium=wordpress" style="text-decoration: none;" target="_blank" class="btn btn-info">
			  				<?php _e( 'Create account', 'cliengo' ) ?>  
							<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
			  			</a> 
					</div>
				</div>
			</div>
		</div>
	</template>
</div>