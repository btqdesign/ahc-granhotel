<?php
/**
 * Plugin Name: BTQ Login
 * Plugin URI: http://btqdesign.com/plugins/btq-login/
 * Description: Login con redes sociales.
 * Version: 0.1.0
 * Author: BTQ Design
 * Author URI: http://btqdesign.com/
 * Requires at least: 4.9.6
 * Tested up to: 4.9.6
 * 
 * Text Domain: btq-login
 * Domain Path: /languages
 * 
 * @package btq-login
 * @category Core
 * @author BTQ Design
 */


// Exit if accessed directly
defined('ABSPATH') or die('No script kiddies please!');

/** 
 * Establece el dominio correcto para la carga de traducciones
 */
load_plugin_textdomain('btq-login', false, basename( dirname( __FILE__ ) ) . '/languages');

/**
 * Añade a WordPress los assets JS y CSS necesarios para el Grid.
 *
 * @author José Antonio del Carmen
 * @author Saúl Díaz
 * @return void Integra CSS y JS al frond-end del sitio.
 */
function btq_login_scripts() {
    if (!is_admin()) {
	    wp_enqueue_style( 'bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', 'solaz-child-style','4.1.1');
	    wp_enqueue_style( 'btq-login', plugins_url( 'estilos.css', __FILE__ ), array('solaz-child-style','bootstrap4'),'1.0');
      wp_enqueue_script( 'firebase', 'https://www.gstatic.com/firebasejs/5.0.4/firebase.js', array(), '5.0.4');
      wp_enqueue_script( 'firebase-app', 'https://www.gstatic.com/firebasejs/4.12.1/firebase-app.js', array(), '5.0.4');
	    wp_enqueue_script( 'firebase.auth', 'https://www.gstatic.com/firebasejs/4.12.1/firebase-auth.js', array(), '5.0.4');
	    wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '1.14.3');
	    wp_enqueue_script( 'bootstrap4js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array(), '4.1.1');
	    wp_enqueue_script( 'btq-login-js', plugins_url( 'scripts.js', __FILE__ ), array('firebase'), '1.0');
	}
}
add_action( 'wp_enqueue_scripts', 'btq_login_scripts', 1 );

/**
 * Declara el Widget de BTQ Login en VisualCompouser.
 *
 * @author Saúl Díaz
 * @return void Widget de BTQ Login en VisualCompouser.
 */
function btq_booking_login_VC() {
	vc_map(array(
		'name'     => __( 'BTQ Login', 'btq-login' ),
		'base'     => 'btq-login',
		'class'    => '',
		'category' => __( 'Content', 'btq-login'),
		'icon'     => plugins_url( 'assets/images/iconos' . DIRECTORY_SEPARATOR . 'btqdesign-logo.png', __FILE__ )
	));
}
add_action( 'vc_before_init', 'btq_booking_login_VC' );

/**
 * Función del shortcode que imprime el BTQ Login en el frond-end.
 *
 * @author Saúl Díaz
 * @return string Imprime el BTQ Booking TC
 */
function btq_login_shortcode() {
	ob_start();
	?>

	<div id="botones_primarios"> 
<!-- Button trigger modal -->

      <ul class="mega-menu btq-menu-login">
        <li class="dib customlinks"><a type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter" onclick="closeNav(); pestaña_inicio();"><?php _e('Log in','btq-login'); ?></a></li>
        <li><a type="button" onclick="pestaña_registro(); closeNav();" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter2"><?php _e('Sign up','btq-login'); ?></a></li>  
      </ul>
  </div>
      <!-- Aqui inicia html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->
      <div id="user_div" class="loggedin-div">
        <h3><?php _e('Welcome user: ','btq-login'); ?></h3>
        <p id="user_para"></p>
        <button onclick="logout()"><?php _e('Log out','btq-login'); ?></button>
      </div>
      <!-- Aqui termina html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->
	<?php
	$out = ob_get_clean();
	
	return $out;
} // function btq_login_shortcode()
add_shortcode( 'btq-login', 'btq_login_shortcode' );



function btq_login_modals() {
	?>
	<div id="botones_primarios_modals"> 
  <!-- Button trigger modal -->

          <!-- Modal -->
          <div class="modal hide fade in" data-backdrop="false" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"><?php _e('Login','btq-login'); ?></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">    
                    <div id="login_div" class="main-div">      
                        <button onclick="facebook_login()" data-dismiss="modal"><img src="<?php echo plugins_url( 'fb.png', __FILE__ ); ?>"/><?php _e('Continue with Facebook','btq-login'); ?></button>
                        <br>
                        <button onclick="google_login()" data-dismiss="modal"><img src="<?php echo plugins_url( 'google.png', __FILE__ ); ?>"/><?php _e('Continue with Google','btq-login'); ?></button>
                        <br>
                        <input type="email" placeholder="<?php _e('Email','btq-login'); ?>" id="email_field" />
                        <input type="password" placeholder="<?php _e('Password','btq-login'); ?>" id="password_field" />
                        <button onclick="login()" data-dismiss="modal"><?php _e('Log in to your account','btq-login'); ?></button>
                        <!-- Aqui termina la pestaña de inicio -->
                    </div>
                </div>
                <div class="modal-footer">
                  <p><?php _e('Not a member?','btq-login'); ?><u onclick="pestaña_registro()" data-toggle="modal" data-target="#exampleModalCenter2" data-dismiss="modal"><?php _e('Sing up','btq-login'); ?></u></p>
                  <p style="color: #c69807;" onclick="pestaña_recuperar()" data-toggle="modal" data-target="#exampleModalCenter3" data-dismiss="modal"><?php _e('Forgot your password?','btq-login'); ?></p>

                </div>
              </div>
            </div>
          </div>
        



          
          <!-- Modal -->
      <div class="modal hide fade in" data-backdrop="false" id="exampleModalCenter2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle"><?php _e('Sign up','btq-login'); ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            
            <div class="modal-body">    
                  <!-- Aqui inicia html para registro de usuario por correo --> 
                  <div id="registro" class="loggedin-div">
                    <h3><?php _e('Register with your email','btq-login'); ?></h3>
                    <input type="email" placeholder="<?php _e('Email','btq-login'); ?>" id="new_email_field" required/>
                    <input type="password" placeholder="<?php _e('Password','btq-login'); ?>" id="new_password_field" required/>
                    <button onclick="nuevo_usuario()" data-dismiss="modal"><?php _e('Finalize your registration','btq-login'); ?></button>
                  </div>
                  <!-- Aqui termina html para registro de usuario por correo -->   
                  <div id="registro_completado" class="loggedin-div">
                    <h3><?php _e('Successful registration','btq-login'); ?></h3>
                      <br>
                    <h5><?php _e('Your registration has been completed correctly, we have logged in automatically for you.','btq-login'); ?></h5>
                  </div>  
                  <div class="modal-footer">
                    <p style="color: #c69807;" onclick="pestaña_recuperar()" data-toggle="modal" data-target="#exampleModalCenter3" data-dismiss="modal"><?php _e('Forgot your password?','btq-login'); ?></p> 
                  </div>  
                </div>
          </div>
        </div>
      </div>




      <!-- Modal -->
  <div class="modal hide fade in" data-backdrop="false" id="exampleModalCenter3" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle"><?php _e('Reset your password','btq-login'); ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">    
              <!-- Aqui inicia html para registro de usuario por correo --> 
              <div id="recuperar" class="loggedin-div">
                <h3><?php _e('Enter your Email','btq-login'); ?></h3>
                <input type="email" placeholder="<?php _e('Email','btq-login'); ?>" id="recover_email_field" required/>
                <button onclick="recuperar_contrasena()"><?php _e('Reset your password','btq-login'); ?></button>
              </div>
              <!-- Aqui termina html para registro de usuario por correo -->     
              <div id="recuperado" class="loggedin-div">
              <h3><?php _e('Reset your password','btq-login'); ?></h3>
                <br>
                <h5><?php _e('We have sent a password reset email to the email address you provided.','btq-login'); ?></h5>
              </div>
        </div>
        <div class="modal-footer">
          <p><?php _e('Are you already a member?','btq-login'); ?><u onclick="pestaña_inicio()" data-toggle="modal" data-target="#exampleModalCenter" data-dismiss="modal"><?php _e('Log in','btq-login'); ?></u></p>
          <p><?php _e('Not a member?','btq-login'); ?><u onclick="pestaña_registro()" data-toggle="modal" data-target="#exampleModalCenter2" data-dismiss="modal"><?php _e('Sign up','btq-login'); ?></u></p>
        </div>
      </div>
    </div>
  </div>
</div>


      <!-- Aqui inicia html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->
      <div id="user_div" class="loggedin-div">
        <h3><?php _e('Welcome user: ','btq-login'); ?></h3>
        <p id="user_para"></p>
        <button onclick="logout()"><?php _e('Log out','btq-login'); ?></button>
      </div>
      <!-- Aqui termina html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->

	<?php
}
add_action('wp_footer', 'btq_login_modals');