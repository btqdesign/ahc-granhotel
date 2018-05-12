<?php
// push your child theme functions here
function solaz_child_scripts() {
    wp_enqueue_style( 'solaz-parent-style', get_template_directory_uri(). '/style.css' );
    wp_enqueue_style( 'solaz-child-style', get_stylesheet_directory_uri() . '/style.css', 'solaz-parent-style');
    wp_enqueue_style( 'butler', get_stylesheet_directory_uri() . '/assets/font-face/font.css', 'solaz-child-style');
}
add_action( 'wp_enqueue_scripts', 'solaz_child_scripts' );

add_filter('final_output', function($output) {
    // Soporte HTTPS
    $output = str_replace('http:', 'https:', $output);
    return $output;
});