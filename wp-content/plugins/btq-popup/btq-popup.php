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

      <!-- Modal -->
      <div class="modal fade" id="Top5razones" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div style="padding: 0px;" class="modal-body">
              <img src="imagenes/pop_up_img.jpg" alt="Top 5 razones por las que conviene reservar.">
            </div>
            <div style="padding: 0px;" class="modal-footer">
                <a href="https://granhoteldelaciudaddemexico.com.mx/en/learn-about-the-benefits-of-booking-with-us/"><img src="imagenes/btn_pop_up.jpg" alt="Top 5 razones por las que conviene reservar."></a>
            </div>
          </div>
        </div>
      </div>


	<?php
	$out = ob_get_clean();
	
	return $out;
} // function btq_login_shortcode()
add_shortcode( 'btq-login', 'btq_login_shortcode' );



function btq_login_modals() {
	?>

      <!-- Modal -->
      <div class="modal fade" id="Top5razones" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div style="padding: 0px;" class="modal-body">
              <img src="imagenes/pop_up_img.jpg" alt="Top 5 razones por las que conviene reservar.">
            </div>
            <div style="padding: 0px;" class="modal-footer">
                <a href="https://granhoteldelaciudaddemexico.com.mx/en/learn-about-the-benefits-of-booking-with-us/"><img src="imagenes/btn_pop_up.jpg" alt="Top 5 razones por las que conviene reservar."></a>
            </div>
          </div>
        </div>
      </div>



	<?php
}
add_action('wp_footer', 'btq_login_modals');