<?php
/**
 * Plugin Name: BTQ popup
 * Plugin URI: https://hotel.idevol.net/wp-content/plugins/btq-popup/btq-popup.html
 * Description: Popup autodesplegable.
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
load_plugin_textdomain('btq-popup', false, basename( dirname( __FILE__ ) ) . '/languages');

/**
 * Añade a WordPress los assets JS y CSS necesarios para el Grid.
 *
 * @author José Antonio del Carmen
 * @return void Integra CSS y JS al frond-end del sitio.
 */
function btq_popup_scripts() {
    if (!is_admin()) {
	    wp_enqueue_style( 'bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', 'solaz-child-style','4.1.1');
	    wp_enqueue_script( 'firebase', 'https://www.gstatic.com/firebasejs/5.0.4/firebase.js', array(), '5.0.4');
	    wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '1.14.3');
	    wp_enqueue_script( 'bootstrap4js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array(), '4.1.1');
	    wp_enqueue_script( 'btq-popup-js', plugins_url( 'script.js', __FILE__ ), array('firebase'), '1.0');
	}
}
add_action( 'wp_enqueue_scripts', 'btq_popup_scripts', 1 );

/**
 * Declara el Widget de BTQ Login en VisualCompouser.
 *
 * @author José Antonio del Carmen
 * @return void Widget de BTQ popup en VisualCompouser.
 */
function btq_popup_VC() {
	vc_map(array(
    'name'     => __( 'BTQ Popup', 'btq-popup' ),
		'base'     => 'btq-popup',
		'class'    => '',
		'category' => __( 'Content', 'btq-popup'),
		'icon'     => plugins_url( 'assets/images/iconos' . DIRECTORY_SEPARATOR . 'btqdesign-logo.png', __FILE__ )
	));
}
add_action( 'vc_before_init', 'btq_popup_VC' );



function btq_popup() {
	?>
      <!-- Modal -->
      <div class="modal fade" id="Top5razones" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div style="padding: 0px;" class="modal-body">
              <img src="<?php echo plugins_url( 'imagenes/pop_up_img.jpg', __FILE__ ); ?>" alt="Top 5 razones por las que conviene reservar.">
            </div>
            <div style="padding: 0px;" class="modal-footer">
                <a href="https://granhoteldelaciudaddemexico.com.mx/en/learn-about-the-benefits-of-booking-with-us/"><img src="<?php echo plugins_url( 'imagenes/btn_pop_up.jpg', __FILE__ ); ?>" alt="Top 5 razones por las que conviene reservar."></a>
            </div>
          </div>
        </div>
      </div>

	<?php
}
add_action('wp_footer', 'btq_popup');