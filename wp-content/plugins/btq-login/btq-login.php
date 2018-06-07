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
	<script src="https://cdn.firebase.com/libs/firebaseui/2.5.1/firebaseui.js"></script>
	<link type="text/css" rel="stylesheet" href="https://cdn.firebase.com/libs/firebaseui/2.5.1/firebaseui.css" />
<?php
}
add_action('wp_enqueue_scripts', 'btq_login_wp_head', 1);