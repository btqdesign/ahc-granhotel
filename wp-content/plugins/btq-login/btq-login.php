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
 * @author Saúl Díaz
 * @return void Integra CSS y JS al frond-end del sitio.
 */
function btq_login_scripts() {
    if (!is_admin()) {
	    wp_enqueue_style( 'bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', 'solaz-child-style','4.1.1');
	    wp_enqueue_style( 'btq-login', plugins_url( 'estilos.css', __FILE__ ), array('solaz-child-style','bootstrap4'),'1.0');
	    wp_enqueue_script( 'firebasejs', 'https://www.gstatic.com/firebasejs/5.0.4/firebase.js', array(), '5.0.4');
	    wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '1.14.3');
	    wp_enqueue_script( 'bootstrap4js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array(), '4.1.1');
	    wp_enqueue_script( 'btq-login-js', plugins_url( 'scripts.js', __FILE__ ), array('firebasejs'), '1.0');
	}
}
add_action( 'wp_enqueue_scripts', 'btq_login_scripts', 1001 );

/**
 * Integra en la etiqueta <head> el tag de Firebase
 *
 * @author José Antonio del Carmen
 * @return string Tag de Firebase
 */
function btq_login_wp_head(){
	if (!is_admin()) {
?>
	<script>
	// Aqui se inicializa firebase
	var config = {
		apiKey: "AIzaSyAJKAc_-VwG7Lt_LeSjbNnr8LEzms1WJxk",
		authDomain: "btq-ahm-gran-hotel.firebaseapp.com",
		databaseURL: "https://btq-ahm-gran-hotel.firebaseio.com",
		projectId: "btq-ahm-gran-hotel",
		storageBucket: "btq-ahm-gran-hotel.appspot.com",
		messagingSenderId: "241886061865"
	};
	firebase.initializeApp(config);
	</script>
	<!--
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	-->
<?php
	}
}
add_action('wp_enqueue_scripts', 'btq_login_wp_head', 1002);

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
        <button id="botones" type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">Inicia Sesión</button>
          
          <!-- Modal -->
          <div class="modal hide fade in" data-backdrop="false" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle">Iniciar Sesión</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">    
                    <div id="login_div" class="main-div">      
                        <button onclick="facebook_login()" data-dismiss="modal"><img src="<?php echo plugins_url( 'fb.png', __FILE__ ); ?>"/>Continuar con Facebook </button>
                        <br>
                        <button onclick="google_login()" data-dismiss="modal"><img src="<?php echo plugins_url( 'google.png"', __FILE__ ); ?>"/>  Continuar con Google </button>
                        <br>
                        <input type="email" placeholder="Correo" id="email_field" />
                        <input type="password" placeholder="Contraseña" id="password_field" />
                        <button onclick="login()" data-dismiss="modal">Ingresa a tu cuenta </button>
                        <!-- Aqui termina la pestaña de inicio -->
                    </div>
                </div>
                <div class="modal-footer">
                  <p>¿No eres miembro? <u onclick="pestaña_registro()" data-toggle="modal" data-target="#exampleModalCenter2" data-dismiss="modal">Registrate</u></p>
                  <p style="color: #c69807;" onclick="pestaña_recuperar()" data-toggle="modal" data-target="#exampleModalCenter3" data-dismiss="modal">¿Olvidaste tu contraseña?</p>

                </div>
              </div>
            </div>
          </div>
        



          <button id="botones" type="button" onclick="pestaña_registro()" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter2">Registro</button>
          <!-- Modal -->
      <div class="modal hide fade in" data-backdrop="false" id="exampleModalCenter2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                    <input type="email" placeholder="Correo" id="new_email_field" required/>
                    <input type="password" placeholder="Contraseña" id="new_password_field" required/>
                    <button onclick="nuevo_usuario()" data-dismiss="modal">Finaliza tu registro </button>
                  </div>
                  <!-- Aqui termina html para registro de usuario por correo -->   
                  <div id="registro_completado" class="loggedin-div">
                    <h3>Registro exitoso: </h3>
                      <br>
                    <h5>Tu registro se ha completado correctamente, hemos iniciado sesión automaticamente por ti.</h5>
                  </div>  
                  <div class="modal-footer">
                    <p style="color: #c69807;" onclick="pestaña_recuperar()" data-toggle="modal" data-target="#exampleModalCenter3" data-dismiss="modal">¿Olvidaste tu contraseña?</p> 
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
          <h5 class="modal-title" id="exampleModalLongTitle">Restablecer Contraseña</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">    
              <!-- Aqui inicia html para registro de usuario por correo --> 
              <div id="recuperar" class="loggedin-div">
                <h3>Ingresa tu correo: </h3>
                <input type="email" placeholder="Correo" id="recover_email_field" required/>
                <button onclick="recuperar_contrasena()">Restablecer tu contraseña </button>
              </div>
              <!-- Aqui termina html para registro de usuario por correo -->     
              <div id="recuperado" class="loggedin-div">
                <h3>Restablecimiento de Contraseña: </h3>
                <br
                <h5>Hemos enviado un correo de restablecimiento de contraseña al correo que nos proporcionaste.</h5>
              </div>
        </div>
        <div class="modal-footer">
          <p>¿Ya eres miembro? <u onclick="pestaña_inicio()" data-toggle="modal" data-target="#exampleModalCenter" data-dismiss="modal">Inicia Sesión</u></p>
          <p>¿No eres miembro? <u onclick="pestaña_registro()" data-toggle="modal" data-target="#exampleModalCenter2" data-dismiss="modal">Registrate</u></p>
        </div>
      </div>
    </div>
  </div>
</div>



      <!-- Aqui inicia html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->
      <div id="user_div" class="loggedin-div">
        <h3>Bienvenido Usario: </h3>
        <p id="user_para"></p>
        <button onclick="logout()">Cerrar Sesión</button>
      </div>
      <!-- Aqui termina html en caso de iniciar sesion con cualquier metodo, muestra esta pestaña -->

	<?php
	$out = ob_get_clean();
	
	return $out;
} // function btq_login_shortcode()
add_shortcode( 'btq-login', 'btq_login_shortcode' );