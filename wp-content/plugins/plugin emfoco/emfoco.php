<?php
/**
 * Plugin Name: BTQ emfoco
 * Plugin URI: https://hotel.idevol.net/wp-content/plugins/plugin%20emfoco/emfoco.php
 * Description: Creador de post master
 * Version: 1.0
 * Author: BTQ Design
 * Author URI: http://btqdesign.com/
 * Requires at least: 4.9.6
 * Tested up to: 4.9.6
 * 
 * Text Domain: btq-emfoco
 * Domain Path: /languages
 * 
 * @package btq-emfoco
 * @category Core
 * @author BTQ Design
 */


// Exit if accessed directly
defined('ABSPATH') or die('No script kiddies please!');


/**
 * Añade a WordPress los assets JS y CSS necesarios para el Grid.
 *
 * @author José Antonio del Carmen
 * @return void Integra CSS y JS al frond-end del sitio.
 */
function btq_emfoco_scripts() {
    if (!is_admin()) {
	    wp_enqueue_style( 'bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css', 'solaz-child-style','4.1.1');
	    wp_enqueue_script( 'firebase', 'https://www.gstatic.com/firebasejs/5.0.4/firebase.js', array(), '5.0.4');
	    wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '1.14.3');
	    wp_enqueue_script( 'bootstrap4js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array(), '4.1.1');
	    wp_enqueue_script( 'btq-emfoco-js', plugins_url( 'script.js', __FILE__ ), array('firebase'), '1.0');
	}
}
add_action( 'wp_enqueue_scripts', 'btq_emfoco_scripts', 1 );

/**
 * Declara el Widget de BTQ Login en VisualCompouser.
 *
 * @author José Antonio del Carmen
 * @return void Widget de BTQ popup en VisualCompouser.
 */
function btq_emfoco_VC() {
	vc_map(array(
    'name'     => __( 'BTQ emfoco', 'btq-emfoco' ),
		'base'     => 'btq-emfoco',
		'class'    => '',
		'category' => __( 'Content', 'btq-emfoco'),
		'icon'     => plugins_url( 'assets/images/iconos' . DIRECTORY_SEPARATOR . 'btqdesign-logo.png', __FILE__ )
	));
}
add_action( 'vc_before_init', 'btq_emfoco_VC' );

    /**
     * Register and add settings
     */
    add_action('admin_menu', function() {
        add_options_page( 'Btq Emfoco', 'Btq Emfoco', 'manage_options', 'Btq Emfoco', 'Btq_emfoco_pagina' );
    });

    add_action( 'admin_init', function() {
        register_setting( 'btq-emfoco-settings', 'map_option_1' );
        register_setting( 'btq-emfoco-settings', 'map_option_2' );
        register_setting( 'btq-emfoco-settings', 'map_option_3' );
        register_setting( 'btq-emfoco-settings', 'map_option_4' );
        register_setting( 'btq-emfoco-settings', 'map_option_5' );
        register_setting( 'btq-emfoco-settings', 'map_option_6' );
    });
     

    function Btq_emfoco_pagina() {
        ?>
          <div class="wrap">
            <form action="options.php" method="post">
       
              <?php
                settings_fields( 'btq-emfoco-settings' );
                do_settings_sections( 'btq-emfoco-settings' );
              ?>
              <table>
                   
                  <tr>
                      <th>Your name</th>
                      <td><input type="text" placeholder="Your name" name="map_option_1" value="<?php echo esc_attr( get_option('map_option_1') ); ?>" size="50" /></td>
                  </tr>
                  <tr>
                      <th>Your biography</th>
                      <td><textarea placeholder="Your bio" name="map_option_2" rows="5" cols="50"><?php echo esc_attr( get_option('map_option_2') ); ?></textarea></td>
                  </tr>
       
                  <tr>
                      <th>Your age</th>
                      <td>
       
                          <select name="map_option_3">
                              <option value="">&mdash; select &mdash;</option>
                              <option value="10-20" <?php echo esc_attr( get_option('map_option_3') ) == '10-20' ? 'selected="selected"' : ''; ?>>10-30</option>
                              <option value="20-30" <?php echo esc_attr( get_option('map_option_3') ) == '20-30' ? 'selected="selected"' : ''; ?>>20-30</option>
                              <option value="30-50" <?php echo esc_attr( get_option('map_option_3') ) == '30-50' ? 'selected="selected"' : ''; ?>>30-50</option>
                          </select>
       
                      </td>
                  </tr>
       
                  <tr>
                      <th>Your gender</th>
                      <td>
                          <label>
                              <input type="radio" name="map_option_4" value="male" <?php echo esc_attr( get_option('map_option_4') ) == 'male' ? 'checked="checked"' : ''; ?> /> Male <br/>
                          </label>
                          <label>
                              <input type="radio" name="map_option_4" value="female" <?php echo esc_attr( get_option('map_option_4') ) == 'female' ? 'checked="checked"' : ''; ?> /> Female
                          </label>
                      </td>
                  </tr>
       
                  <tr>
                      <th>Do you love WordPress?</th>
                      <td>
                          <label>
                              <input type="checkbox" name="map_option_5" <?php echo esc_attr( get_option('map_option_5') ) == 'on' ? 'checked="checked"' : ''; ?> />Yes, I love WordPress
                          </label><br/>
                          <label>
                              <input type="checkbox" name="map_option_6" <?php echo esc_attr( get_option('map_option_6') ) == 'on' ? 'checked="checked"' : ''; ?> />No, I love WordPress
                          </label>
                      </td>
                  </tr>
       
                  <tr>
                      <td><?php submit_button(); ?></td>
                  </tr>
       
              </table>
       
            </form>
          </div>
        <?php
      }