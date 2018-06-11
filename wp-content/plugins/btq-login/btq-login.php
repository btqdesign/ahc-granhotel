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
 * Integra en la etiqueta <head> el tag de Firebase
 *
 * @author Saúl Díaz
 * @return string Tag de Firebase
 */
function btq_login_wp_head(){
?>
	<script src="https://www.gstatic.com/firebasejs/5.0.4/firebase.js"></script>
	<script>
	  // Initialize Firebase
	  var config = {
	    apiKey: "AIzaSyAJKAc_-VwG7Lt_LeSjbNnr8LEzms1WJxk",
	    authDomain: "btq-ahm-gran-hotel.firebaseapp.com",
	    databaseURL: "https://btq-ahm-gran-hotel.firebaseio.com",
	    projectId: "btq-ahm-gran-hotel",
	    storageBucket: "",
	    messagingSenderId: "241886061865"
	  };
	  firebase.initializeApp(config);
	</script>
	<script src="https://www.gstatic.com/firebasejs/5.0.4/firebase-app.js"></script>
	<script src="https://www.gstatic.com/firebasejs/5.0.4/firebase-auth.js"></script>

<!-- Leave out Storage -->
<!-- <script src="https://www.gstatic.com/firebasejs/4.10.1/firebase-storage.js"></script> -->

<script>
  var config = {
    // ...
  };
  firebase.initializeApp(config);
</script>
<?php
}
//add_action('wp_enqueue_scripts', 'btq_login_wp_head', 1);

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
	<script>
		ui.start('#firebaseui-auth-container', {
		  signInOptions: [
		    firebase.auth.EmailAuthProvider.PROVIDER_ID,
		    firebase.auth.GoogleAuthProvider.PROVIDER_ID,
			firebase.auth.FacebookAuthProvider.PROVIDER_ID
		  ],
		  // Other config options...
		});
	</script>
	<?php
	$out = ob_get_clean();
	
	return $out;
}
add_shortcode( 'btq-login', 'btq_login_shortcode' );