<?php
// push your child theme functions here
function solaz_child_scripts() {
    wp_enqueue_style( 'solaz-parent-style', get_template_directory_uri(). '/style.css' );
    wp_enqueue_style( 'solaz-child-style', get_stylesheet_directory_uri() . '/style.css');
}
add_action( 'wp_enqueue_scripts', 'solaz_child_scripts' );
