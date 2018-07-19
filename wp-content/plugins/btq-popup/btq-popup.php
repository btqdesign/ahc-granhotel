<?php
/**
 * Plugin Name: BTQ Popup
 * Plugin URI: https://hotel.idevol.net/wp-content/plugins/btq-popup/btq-popup.html
 * Description: Popup autodesplegable.
 * Version: 1.0
 * Author: BTQ Design
 * Author URI: http://btqdesign.com/
 * Requires at least: 4.9.6
 * Tested up to: 4.9.6
 * 
 * Text Domain: btq-popup
 * Domain Path: /languages
 * 
 * @package btq-popup
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
  $language = btq_popup_current_language_code();
	?>
      <!-- Modal -->
      <div class="modal fade" id="Top5razones" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <?php if ($language == 'es'):?> 
                  <h5 class="modal-title" id="exampleModalLongTitle">RAZONES PARA RESERVAR CON NOSOTROS</h5>
                <?php else:?>
                  <h5 class="modal-title" id="exampleModalLongTitle">REASONS TO BOOK WITH US</h5>
              <?php endif;?>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
              </div>
            <div style="padding: 0px;" class="modal-body">
            <?php if ($language == 'es'):?>
                <img src="<?php echo plugins_url( 'imagenes/pop_up_img_es.jpg', __FILE__ ); ?>" alt="Top 5 razones por las que conviene reservar.">
              <?php else:?>
               <img src="<?php echo plugins_url( 'imagenes/pop_up_img_en.jpg', __FILE__ ); ?>" alt="Top 5 reasons why you should book.">
            <?php endif;?>
            </div>
            <div style="padding: 0px;" class="modal-footer">
             <?php if ($language == 'es'):?>
                <a href="https://granhoteldelaciudaddemexico.com.mx/es/conoce-los-beneficios-de-reservar-con-nosotros/"><img src="<?php echo plugins_url( 'imagenes/btn_pop_up_es.jpg', __FILE__ ); ?>" alt="Top 5 razones por las que conviene reservar."></a>
                <?php else:?>
                <a href="https://granhoteldelaciudaddemexico.com.mx/en/learn-about-the-benefits-of-booking-with-us/"><img src="<?php echo plugins_url( 'imagenes/btn_pop_up_en.jpg', __FILE__ ); ?>" alt="Top 5 reasons why you should book."></a>
                <?php endif;?>
            </div>
          </div>
        </div>
      </div>
    
	<?php
}
add_action('wp_footer', 'btq_popup');

/**
 * Devuelve el código de idioma que se está utilizando.
 *
 * @author Saúl Díaz
 * @return string Código de idioma que se está utilizando.
 */
function btq_popup_current_language_code() {

  $wpml_current_language = apply_filters( 'wpml_current_language', NULL );
  if (!empty($wpml_current_language)){
    $language = $wpml_current_language;
  }
  elseif ( defined( 'ICL_LANGUAGE_CODE' ) ) {
    $language = ICL_LANGUAGE_CODE;
  }
  else {
    $language = 'es';
  }
  
  //Debug
  //btq_booking_tc_log('languages', $language, TRUE);
  
  return $language;
}