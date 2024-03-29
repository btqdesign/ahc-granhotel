<?php
/**
 * The Template for displaying all archive products.
 *
 * Override this template by copying it to yourtheme/tp-hotel-booking/templates/archive-room.php
 *
 * @author 		ThimPress
 * @package 	wp-hotel-booking/templates
 * @version     1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); ?>

	<?php
		/**
		 * hotel_booking_before_main_content hook
		 *
		 * @hooked hotel_booking_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked hotel_booking_breadcrumb - 20
		 */
		do_action( 'hotel_booking_before_main_content' );
	?>

		<?php
			/**
			 * hotel_booking_archive_description hook
			 *
			 * @hooked hotel_booking_taxonomy_archive_description - 10
			 * @hooked hotel_booking_room_archive_description - 10
			 */
			do_action( 'hotel_booking_archive_description' );
		?>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * hotel_booking_before_room_loop hook
				 *
				 * @hooked hotel_booking_result_count - 20
				 * @hooked hotel_booking_catalog_ordering - 30
				 */
				do_action( 'hotel_booking_before_room_loop' );
			?>

			<?php hotel_booking_room_loop_start(); ?>

				<?php hotel_booking_room_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php hb_get_template_part( 'content', 'room' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php hotel_booking_room_loop_end(); ?>
			<?php 
			global $solaz_settings;
			?>
			<?php if($solaz_settings['hotel-type-pagination'] == 'pagination') :?>
				<?php
					/**
					 * hotel_booking_after_room_loop hook
					 *
					 * @hooked hotel_booking_pagination - 10
					 */
					do_action( 'hotel_booking_after_room_loop' );
				?>
			<?php else:?>	
				<?php 
					global $wp_query;
					$current_page = get_query_var('paged') ? intval(get_query_var('paged')) : 1;
				?>
				<div class="load-more room-loadmore text-center col-md-12">
                    <a class="btn btn-primary" data-paged="<?php echo esc_attr($current_page) ?>" data-totalpage="<?php echo esc_html($wp_query->max_num_pages); ?>" id="room-loadmore"> 
                    <?php 
                        echo esc_html__('Loading More','solaz');
                    ?>   
                    </a>
                </div>
			<?php endif;?>		


		<?php endif; ?>

	<?php
		/**
		 * hotel_booking_after_main_content hook
		 *
		 * @hooked hotel_booking_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'hotel_booking_after_main_content' );
	?>

	<?php
		/**
		 * hotel_booking_sidebar hook
		 *
		 * @hooked hotel_booking_get_sidebar - 10
		 */
		do_action( 'hotel_booking_sidebar' );
	?>

<?php get_footer(); ?>