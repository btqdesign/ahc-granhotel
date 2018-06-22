<?php

/**
 * Output Buffering
 *
 * Buffers the entire WP process, capturing the final output for manipulation.
 */

ob_start();

add_action(
	'shutdown', 
	function() {
	    $final = '';
	
	    // We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
	    // that buffer's output into the final output.
	    $levels = ob_get_level();
	
	    for ($i = 0; $i < $levels; $i++)
	    {
	        $final .= ob_get_clean();
	    }
	
	    // Apply any filters to the final output
		echo apply_filters('final_output', $final);
	}, 
	0
);

add_filter('final_output', function($output) {
    // Soporte HTTPS
    $output = str_replace('http:', 'https:', $output);
    $output = str_replace('https://schemas.xmlsoap.org', 'http://schemas.xmlsoap.org', $output);
    $output = str_replace('https://docs.oasisopen.org', 'http://docs.oasisopen.org', $output);
    $output = str_replace('https://www.sitemaps.org', 'http://www.sitemaps.org', $output);
    return $output;
});
