<?php
// push your child theme functions here
function solaz_parent_btq_scripts() {
	wp_enqueue_style( 'solaz-parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'solaz_parent_btq_scripts' );

function solaz_child_scripts() {
    wp_enqueue_style( 'solaz-child-style', get_stylesheet_directory_uri() . '/style.css', 'solaz-parent-style');
    wp_enqueue_style( 'butler', get_stylesheet_directory_uri() . '/assets/font-face/font.css', 'solaz-child-style');
}
add_action( 'wp_enqueue_scripts', 'solaz_child_scripts', 1000);

add_filter('final_output', function($output) {
    // Soporte HTTPS
    $output = str_replace('http:', 'https:', $output);
    $output = str_replace('https://schemas.xmlsoap.org', 'http://schemas.xmlsoap.org', $output);
    $output = str_replace('https://docs.oasisopen.org', 'http://docs.oasisopen.org', $output);
    return $output;
});

add_action( 'after_setup_theme', 'btq_menu' );
function btq_menu() {
  register_nav_menu( 'btq-menu', __( 'BTQ Menu', 'btq-menu' ) );
  register_nav_menu( 'btq-menu-terraza', __( 'BTQ Menu', 'btq-menu-terraza' ) );
  register_nav_menu( 'btq-menu-banquete', __( 'BTQ Menu', 'btq-menu-banquete' ) );
}