<?php
/**
 * Plugin Name: BTQ Login
 * Plugin URI: http://btqdesign.com/plugins/btq-login/
 * Description: Login con redes sociales.
 * Version: 1.0
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
 * @author José García
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
 * @author José García
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


function btq_login_modals() {
	?>
  

      <div id="botones_primarios"> 
        <!-- Button trigger modal -->
        <u data-toggle="modal" data-target="#Iniciar_Sesion"><?php _e('Log in: ','btq-login'); ?></u>
          
          <!-- Modal -->
          <div class="modal fade" id="Iniciar_Sesion" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"><?php _e('Log in: ','btq-login'); ?></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">    
                    <div id="login_div" class="main-div">  
                      <form>
                          <div id="user_already_exist_fb" class="alert alert-danger" role="alert">
                                <?php _e('The email is already registered with another form of authentication.','btq-login'); ?>
                          </div>
                        <button onclick="facebook_login()" data-dismiss="modal"><img src="<?php echo plugins_url( 'fb.png', _FILE_ ); ?>"/><?php _e('Continue with Facebook','btq-login'); ?></button>
                        <br>
                          <div id="user_already_exist_google" class="alert alert-danger" role="alert">
                                <?php _e('The email is already registered with another form of authentication.','btq-login'); ?>
                        </div>
                        <button onclick="google_login()" data-dismiss="modal"><img src="<?php echo plugins_url( 'google.png', _FILE_ ); ?>"/><?php _e('Continue with Google','btq-login'); ?></button>
                        <br>
                          <div id="email_void" class="alert alert-danger" role="alert">
                                <?php _e('The mail field is empty or does not have the proper format.','btq-login'); ?>
                          </div>
                          <div id="pass_invalid" class="alert alert-danger" role="alert">
                                <?php _e('The email and password do not match.','btq-login'); ?>
                          </div>
                          <div id="user_non_exist" class="alert alert-danger" role="alert">
                                <?php _e('The email is not registered.','btq-login'); ?>
                          </div>
                          <div id="user_already_exist" class="alert alert-danger" role="alert">
                                <?php _e('The email is already registered with another form of authentication.','btq-login'); ?>
                          </div>
                          <div id="internal_error" class="alert alert-danger" role="alert">
                                <?php _e('Something went wrong, try again later.','btq-login'); ?>
                          </div>
                          <form>
                              <input type="email" class="Input" placeholder="<?php _e('Email','btq-login'); ?>" id="email_field" required>
                              <input type="password" class="Input" placeholder="<?php _e('Password','btq-login'); ?>" id="password_field" required>
                              <button type="submit" class="Boton" data-dismiss="modal" onclick="login()"><?php _e('Log in to your account','btq-login'); ?></button>
                          </form>
                      </form>  
                    </div>
                </div>
                <div class="modal-footer">
                  <p>¿No eres miembro? <u data-toggle="modal" data-target="#Registro" data-dismiss="modal">Registrate</u></p>
                  <p class="Footer_Text" data-toggle="modal" data-target="#Restablecer_Pass" data-dismiss="modal">¿Olvidaste tu contraseña?</p>

                </div>
              </div>
            </div>
          </div>
        



          <u data-toggle="modal" data-target="#Registro">Registro</u>
          <!-- Modal -->
      <div class="modal fade" id="Registro" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle">Registrate</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            
            <div class="modal-body">    
                  <!-- Aqui inicia html para registro de usuario por correo --> 
                  <div id="registro" class="loggedin-div">
                    <h3>Registrate con tu correo: </h3>
                    <div id="email_void_register" class="alert alert-danger" role="alert">
                          El campo de correo esta vacio o no tiene el formato adecuado.
                    </div>
                    <div id="user_already_exist_register" class="alert alert-danger" role="alert">
                          El correo ya esta registrado con otra forma de autenticación.
                    </div>
                    <div id="passwords_dont_match" class="alert alert-danger" role="alert">
                          Las contraseñas no coinciden.
                    </div>
                    <div id="password_length" class="alert alert-danger" role="alert">
                        La contraseña debe tener almenos 6 caracteres.
                    </div>




                    <div id="internal_error_register" class="alert alert-danger" role="alert">
                          Algo salió mal, vuelve a intentarlo mas tarde.
                    </div>

                    <input type="email" placeholder="Correo" id="new_email_field" required/>
                    <input type="password" placeholder="Contraseña" id="new_password_field" required/>
                    <input type="password" placeholder="Confirma tu contraseña" id="password_field_confirmation" required/>
                    <button onclick="nuevo_usuario()">Finaliza tu registro </button>
                  </div>
                  <div class="modal-footer">
                    <p>¿Ya eres miembro? <u data-toggle="modal" data-target="#Iniciar_Sesion" data-dismiss="modal">Inicia Sesión</u></p>
                    <p class="Footer_Text" class="Footer_Text" data-toggle="modal" data-target="#Restablecer_Pass" data-dismiss="modal">¿Olvidaste tu contraseña?</p> 
                  </div>  
            </div>
          </div>
        </div>
      </div>




      <!-- Modal -->
  <div class="modal fade" id="Restablecer_Pass" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Restablecer Contraseña</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">    
              <!-- Aqui inicia html para registro de usuario por correo --> 
              <div id="recuperar" class="loggedin-div">
                <h3>Ingresa tu correo: </h3>
                <div id="recover_success" class="alert alert-success" role="alert">
                    Hemos enviado un correo de restablecimiento de contraseña al correo que nos proporcionaste.
                </div>
                <div id="recover_fail" class="alert alert-danger" role="alert">
                    No hemos podido enviar el correo de restauración de contraseña, intentalo mas tarde.
                </div>
                <input type="email" placeholder="Correo" id="recover_email_field" required/>
                <button onclick="recuperar_contrasena()">Restablecer tu contraseña </button>
              </div>
              <!-- Aqui termina html para registro de usuario por correo -->     
        </div>
        <div class="modal-footer">
          <p>¿Ya eres miembro? <u data-toggle="modal" data-target="#Iniciar_Sesion" data-dismiss="modal">Inicia Sesión</u></p>
          <p>¿No eres miembro? <u data-toggle="modal" data-target="#Registro" data-dismiss="modal">Registrate</u></p>
        </div>
      </div>
    </div>
  </div>
</div>



      <!-- Aqui inicia html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->
      <div id="user_div" class="loggedin-div">
          <div id="register_success" class="alert alert-success" role="alert">
              Tu registro se ha completado correctamente, hemos iniciado sesion automaticamente por ti.
          </div>            
        <h3>Bienvenido: </h3>
        <p id="user_para"></p>
        <button onclick="logout()">Cerrar Sesión</button>
      </div>
      <!-- Aqui termina html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->

	<?php
}
add_action('wp_footer', 'btq_login_modals');