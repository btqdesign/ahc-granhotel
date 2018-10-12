<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.cliengo.com
 * @since      1.0.0
 *
 * @package    Cliengo
 * @subpackage Cliengo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cliengo
 * @subpackage Cliengo/admin
 * @author     Your Name <email@example.com>
 */
class Cliengo_Form {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }
  public function exists_chatbot_token() {

    global $wpdb;


    $chatbot_token = $wpdb->get_row( "SELECT * FROM wp_options WHERE option_name ='cliengo_chatbot_token'");

    echo $chatbot_token->option_value;

    wp_die();
  }

  public function update_chatbot_token()
  {
    $response = $this->update_cliengo_option('cliengo_chatbot_token', $_POST['chatbot_token'])
      && $this->update_cliengo_option('cliengo_chatbot_position', $_POST['position_chatbot']);
    if ($response)
      $this->create_install_code_cliengo($_POST['chatbot_token']);

    echo $response;

    wp_die();
  }

  // Método privado para actualizar tabla de opciones de wp de forma robusta (con backward compability)
  private function update_cliengo_option ($option, $new_value)
  {
    // Obtenemos opción existente.
    $current = get_option($option);

    $response = true;
    // Si la opción ya existe, la actualizamos.
    if ($current !== false)
    {
      if (strcmp($current, $new_value) !== 0)
        // Actualizamos si el valor actual de la opción es distinto al nuevo.
        $response = update_option($option, $new_value);
    }
    else
    {
      // Agregamos nueva opción.
      $response = add_option($option, $new_value);
    }

    return $response;
  }

  /*
  * Esta funcion se encargara de codigo javascript de instalacion de cliengo
  *
  */
  public function create_install_code_cliengo($chatbot_token)
  {
    $array_chatbot_token = explode('-',$chatbot_token); //esto se encarga de dividir el chatbot token
    // $array_chatbot_token[0] = Company ID
    // $array_chatbot_token[1] = Website ID
    $install_code_cliengo = '(function(){var ldk=document.createElement("script"); ldk.type="text/javascript";';
    $install_code_cliengo .= 'ldk.async=true; ldk.src="https://s.cliengo.com/weboptimizer/' . $array_chatbot_token[0] . '/';
    $install_code_cliengo .= $array_chatbot_token[1];
    $install_code_cliengo .= '.js?platform=wordpress"; var s=document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ldk, s);})();';

    // Eliminamos cualquier salto de línea posible
    $install_code_cliengo = str_replace(array("\r", "\n"), '', $install_code_cliengo);

    $this->write_to_file_install_code_cliengo_js($install_code_cliengo);
  }

  /*
  * Esta funcion se encargara de escribir en el archivo cliengo/admin/js/script_install_cliengo.js el script de instalacion de cliengo
  *
  */
  public function write_to_file_install_code_cliengo_js($install_code_cliengo)
  {
    $ruta_install_code_cliengo_file = plugin_dir_path( __FILE__ ) . '../public/js/script_install_cliengo.js';

    $file_open = fopen($ruta_install_code_cliengo_file, 'w');

    fputs($file_open, $install_code_cliengo);

    fclose($file_open);
  }
}
