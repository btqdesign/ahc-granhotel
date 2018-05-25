<?php
add_action( 'admin_init', 'wpsl_single_location_export' );

/**
 * Handle the export of a single store location.
 *
 * Creates a CSV file holding the location details
 * that can be handed over in case a GDPR related
 * data access request is received.
 *
 * @since 2.2.15
 * @return void
 */
function wpsl_single_location_export() {

    if ( isset( $_GET['wpsl_data_export'] ) && isset( $_GET['wpsl_export_nonce'] ) ) {
        $post_id = absint( $_GET['post'] );

        if ( !wp_verify_nonce( $_GET['wpsl_export_nonce'], 'wpsl_export_' . $post_id ) )
            return;

        if ( is_int( wp_is_post_revision( $post_id ) ) )
            return;

        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        $meta_fields = wpsl_get_field_names( false );
        $meta_data   = get_post_custom( $post_id );
        $post_meta   = '';

        // Loop over the wpsl meta fields, and collect the meta data.
        foreach ( $meta_fields as $meta_field ) {
            if ( $meta_field !== 'hours' ) {
                if ( isset( $meta_data['wpsl_' . $meta_field][0] ) ) {
                    $post_meta['data'][$meta_field] = $meta_data['wpsl_' . $meta_field][0];
                } else {
                    $post_meta['data'][$meta_field] = '';
                }

                $post_meta['headers'][] = $meta_field;
            }
        }

        // Make it possible to add additional custom data from for example ACF
        $post_meta = apply_filters( 'wpsl_single_location_export_data', $post_meta, $post_id );

        if ( $post_meta ) {
            $file_name = 'wpsl-export-' . $post_id . '-' . date('Ymd' ) . '.csv';

            //  Set the download headers for the CSV file.
            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=' . $file_name . '' );

            $output = fopen( 'php://output', 'w' );

            fputcsv( $output, $post_meta['headers'] );
            fputcsv( $output, $post_meta['data'] );

            fclose( $output );
        }

        exit();
    }
}