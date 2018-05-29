<?php
/**
 * Plugin Name: BTQ Design Booking TC
 * Plugin URI: http://btqdesign.com/plugins/btq-booking-tc/
 * Description: Booking TravelClick
 * Version: 0.1.0
 * Author: BTQ Design
 * Author URI: http://btqdesign.com/
 * Requires at least: 4.9.5
 * Tested up to: 4.9.5
 * 
 * Text Domain: btq-booking-tc
 * Domain Path: /languages
 * 
 * @package btq-booking-tc
 * @category Core
 * @author BTQ Design
 */


// Exit if accessed directly
defined('ABSPATH') or die('No script kiddies please!');


/*
// Register settings using the Settings API 
function wpdocs_register_my_setting() {
    register_setting( 'my-options-group', 'my-option-name', 'intval' ); 
} 
add_action( 'admin_init', 'wpdocs_register_my_setting' );
 
// Modify capability
function wpdocs_my_page_capability( $capability ) {
    return 'edit_others_posts';
}
add_filter( 'option_page_capability_my-options-group', 'wpdocs_my_page_capability' );
*/

function btq_booking_tc_log($filename, $string = ''){
	$log_dir = plugin_dir_path( __FILE__ ) . 'log' ;
	
	if (!file_exists($log_dir)) {
		mkdir($log_dir, 0755);
	}
	
	file_put_contents($log_dir . DIRECTORY_SEPARATOR . $filename . date('_Y-m-d_U'). '.log', $string);
}

add_action( 'admin_menu', 'btq_booking_tc_admin_menu' );
function btq_booking_tc_admin_menu() {
    add_menu_page(
        __('Booking TC', 'btq-booking-tc'),
        __('Booking TC', 'btq-booking-tc'),
        'manage_options',
        'btq_booking_tc_settings',
        'btq_booking_tc_admin_settings_page',
        'dashicons-building',
        100
    );
    add_submenu_page(
    	'btq_booking_tc_settings', 
    	__('Settings', 'btq-booking-tc'), 
    	__('Settings', 'btq-booking-tc'), 
    	'manage_options', 
    	'btq_booking_tc_settings',
    	'btq_booking_tc_admin_settings_page'
    );
    add_submenu_page(
    	'btq_booking_tc_settings', 
    	__('Debug', 'btq-booking-tc'), 
    	__('Debug', 'btq-booking-tc'), 
    	'manage_options', 
    	'btq_booking_tc_debug',
    	'btq_booking_tc_admin_debug_page'
    );
}

function btq_booking_tc_admin_settings_page() {
?>
	<div class="wrap">
		<h1>Booking TC</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'btq-booking-tc-settings' ); ?>
			<?php do_settings_sections( 'btq-booking-tc-settings' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('SOAP Header To', 'btq-booking-tc'); ?></th>
					<td><input type="number" name="soap_header_to" value="<?php echo esc_attr( get_option('soap_header_to') ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('SOAP Header Action', 'btq-booking-tc'); ?></th>
					<td><input type="number" name="soap_header_action" value="<?php echo esc_attr( get_option('soap_header_action') ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Hotel code english language', 'btq-booking-tc'); ?></th>
					<td><input type="number" name="hotel_code_us" value="<?php echo esc_attr( get_option('hotel_code_us') ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Hotel code spanish language', 'btq-booking-tc'); ?></th>
					<td><input type="number" name="hotel_code_es" value="<?php echo esc_attr( get_option('hotel_code_es') ); ?>" /></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div><!-- wrap -->
<?php
}

function btq_booking_tc_soap_query_string($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery = 'rooms', $rooms = 1, $adults = 1, $childrens = 0, $availRatesOnly = 'true') {
	
	if ($typeQuery == 'packages'){
		// Paquete
		$wsaTo = 'https://ota2.ihotelier.com/OTA_Seamless/services/FullDataService';
		$wsaAction = 'FULL';
		
		$soapBody = '
		<soap:Body>
			<OTA_HotelAvailRQ Version="2.0" AvailRatesOnly="'. $availRatesOnly .'">
				<POS>
					<Source>
						<RequestorID ID="1" Type="1" />
						<BookingChannel Type="18">
							<CompanyName Code="AHC" />
						</BookingChannel>
					</Source>
				</POS>
				<AvailRequestSegments>
					<AvailRequestSegment ResponseType="FullList">
						<HotelSearchCriteria AvailableOnlyIndicator="false">
							<Criterion>
								<StayDateRange Start="'. $dateRangeStart .'" End="'. $dateRangeEnd .'" />
								<RatePlanCandidates>
									<RatePlanCandidate RatePlanType="11">
										<HotelRefs>
											<HotelRef HotelCode="'. $hotelCode .'" />
										</HotelRefs>
									</RatePlanCandidate>
								</RatePlanCandidates>
								<RoomStayCandidates>
									<RoomStayCandidate Quantity="'. $rooms .'">
										<GuestCounts>
											<GuestCount AgeQualifyingCode="10" Count="'. $adults .'" />
											<GuestCount AgeQualifyingCode="8" Count="'. $childrens .'" />
										</GuestCounts>
									</RoomStayCandidate>
								</RoomStayCandidates>
							</Criterion>
						</HotelSearchCriteria>
					</AvailRequestSegment>
				</AvailRequestSegments>
			</OTA_HotelAvailRQ>
		</soap:Body>';
	}
	else{
		// Habitaciones
		$wsaTo = 'https://ota2.ihotelier.com/OTA_Seamless/services/PropertyAvailabilityService';
		$wsaAction = 'PALS';
		
		$soapBody = '
		<soap:Body>
			<OTA_HotelAvailRQ Version="2.0" AvailRatesOnly="'. $availRatesOnly .'">
				<POS>
					<Source>
						<RequestorID ID="1" Type="1" />
						<BookingChannel Type="18">
							<CompanyName Code="AHC" />
						</BookingChannel>
					</Source>
				</POS>
				<AvailRequestSegments>
					<AvailRequestSegment ResponseType="PropertyList">
						<HotelSearchCriteria AvailableOnlyIndicator="true">
							<Criterion>
								<StayDateRange Start="'. $dateRangeStart .'" End="'. $dateRangeEnd .'" />
								<RatePlanCandidates>
									<RatePlanCandidate>
										<HotelRefs>
											<HotelRef HotelCode="'. $hotelCode .'"/>
										</HotelRefs>
									</RatePlanCandidate>
								</RatePlanCandidates>
								<RoomStayCandidates>
									<RoomStayCandidate Quantity="'. $rooms .'">
										<GuestCounts>
											<GuestCount AgeQualifyingCode="10" Count="'. $adults .'" />
											<GuestCount AgeQualifyingCode="8" Count="'. $childrens .'" />
										</GuestCounts>
									</RoomStayCandidate>
								</RoomStayCandidates>
							</Criterion>
						</HotelSearchCriteria>
						<TPA_Extensions>
							<InventoryData InventoryDataNeeded="True"/>
						</TPA_Extensions>                    
					</AvailRequestSegment>
				</AvailRequestSegments>
			</OTA_HotelAvailRQ>
		</soap:Body>';
	}
	
	$soapHeader = '
		<soap:Header>
			<wsa:MessageID>Message01</wsa:MessageID>
			<wsa:ReplyTo>
				<wsa:Address>NOT NEEDED FOR SYNC REQUEST</wsa:Address>
			</wsa:ReplyTo>
			<wsa:To>'. $wsaTo .'</wsa:To>
			<wsa:Action>'. $wsaAction .'</wsa:Action>
			<wsa:From>
				<SalesChannelInfo ID="AHC" />
			</wsa:From>
			<wsse:Security>
				<wsu:Timestamp>
					<wsu:Created>2011-12-24T16:05:30+05:30</wsu:Created>
					<wsu:Expires>2011-12-25T16:12:46+05:30</wsu:Expires>
				</wsu:Timestamp>
				<wsse:UsernameToken>
					<wsse:Username>ADMIN</wsse:Username>
					<wsse:Password>C0nn3ct0taAp!</wsse:Password>
				</wsse:UsernameToken>
			</wsse:Security>
		</soap:Header>';
	    
	$soapEnvelope = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsa="http://schemas.xmlsoap.org/ws/2004/08/addressing" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        '.$soapHeader.'
        '.$soapBody.'
	</soap:Envelope>';
	
	// Debug Log
	btq_booking_tc_log('soapenvelope', $soapEnvelope);
	
	return array('envelope' => $soapEnvelope, 'wsaTo' => $wsaTo);
	
}

function btq_booking_tc_soap_query($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery = 'rooms', $rooms = 1, $adults = 1, $childrens = 0, $availRatesOnly = 'true'){
	require_once('lib/nusoap.php');
	
	$soap = btq_booking_tc_soap_query_string($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery, $rooms, $adults, $childrens, $availRatesOnly);
	
	$client = new nusoap_client($soap['wsaTo']);
	$client->soap_defencoding = 'UTF-8';
	$client->decode_utf8 = FALSE;
	$result = $client->send($soap['envelope'], $soap['wsaTo'], '');
	
	// Captura de errores
	if (isset($result['Errors'])) {
		$errors = $result['Errors'];
		error_log('Error Code: '. $errors['Error']['!Code'] .' - '. $errors['Error']['!ShortText']);
		return;
	}
	
	return $result;
}

function btq_booking_tc_amenity_icon_name($amenityCode) {
	$amenitiesArray = array(
		'10'       => 'english_air_conditioned.png',
		'12'       => 'english_alarm_clock.png',
		//'codigo' => 'english_amenidades.png',
		//'codigo' => 'english_banera.png',
		//'codigo' => 'english_cafe_de_cortesia.png',
		//'codigo' => 'english_cambio_de_divisas.png',
		'51'       => 'english_coffeemaker.png',
		//'codigo' => 'english_cortesia_nocturna.png',
		'128312'   => 'english_courtesy_sweet_in-the-room.png',
		'59'       => 'english_crib.png',
		//'codigo' => 'english_desayuno_americano_1.png',
		'69'       => 'english_double_bed.png',
		//'codigo' => 'english_escritorio.png',
		'89'       => 'english_fire_alarm_with_light.png',
		'151'      => 'english_free_newspaper.png',
		'59240'    => 'english_free_wifi.png',
		'119'      => 'english_king_bed.png',
		'128'      => 'english_large_suite.png',
		//'codigo' => 'english_llamadas_locales_en_cortesia.png',
		//'codigo' => 'english_menu_almuadas.png',
		//'codigo' => 'english_recamaras_tematicas.png',
		//'codigo' => 'english_regadera_de_mano.png',
		'139963'   => 'english_room_zocalo_view.png',
		'128315'   => 'english_safe_deposit_box.png',
		'128317'   => 'english_selection_of_cushions.png',
		'210'      => 'english_sitting_area.png',
		'217'      => 'english_sofa_bed.png',
		//'codigo' => 'english_telefono_en_el_bañol.png',
		//'codigo' => 'english_television_con_cable.png',
		'128311'   => 'english_welcome_drink.png',
		'54080'    => 'spanish_aire_acondicionado.png',
		'54200'    => 'spanish_amenidades.png',
		'54181'    => 'spanish_banera.png',
		'128319'   => 'spanish_bebida_de_bienvenida.png',
		'54234'    => 'spanish_cafe_de_cortesia.png',
		'54191'    => 'spanish_cafetera.png',
		'54216'    => 'spanish_caja_de_seguridad.png',
		'59204'    => 'spanish_cama_king_size.png',
		'54214'    => 'spanish_cambio_de_divisas.png',
		'59201'    => 'spanish_cortesia_nocturna.png',
		'54192'    => 'spanish_cunas.png',
		'128320'   => 'spanish_desayuno_americano_1.png',
		'59205'    => 'spanish_dos_camas.png',
		'54211'    => 'spanish_escritorio.png',
		'54210'    => 'spanish_estancia.png',
		'139964'   => 'spanish_habitacion_con_vista_zocalo.png',
		'54225'    => 'spanish_habitacion_nupcial.png',
		'59207'    => 'spanish_llamadas_locales_en_cortesia.png',
		'128321'   => 'spanish_menu_almuadas.png',
		'54223'    => 'spanish_recamaras_tematicas.png',
		'54207'    => 'spanish_regadera_de_mano.png',
		'59203'    => 'spanish_telefono_en_el_banol.png',
		'54190'    => 'spanish_television_con_cable.png',
		'59242'    => 'spanish_wifi_en_cortesia.png'
	);
	
	if (!isset($amenitiesArray[$amenityCode]))
		return FALSE;
	
	return $amenitiesArray[$amenityCode];
}


function btq_booking_tc_admin_debug_rooms($hotelCode = '131328') {
	$response = btq_booking_tc_soap_query($hotelCode, '2018-09-11', '2018-09-12');
	
	$RoomAmenities = array();
	$amenities = array();
	
	$RoomType = $response['RoomStays']['RoomStay']['RoomTypes']['RoomType'];
	
	?>
	<table>
		<tr><th>Código de habitación</th><th>Nombre de la habitación</th></tr>
	<?php
	foreach($RoomType as $elementRoomType){
		$RoomAmenities[] = $elementRoomType['Amenities']['Amenity'];
		?><tr><td><?php echo $elementRoomType['!RoomTypeCode']; ?></td><td><?php echo htmlentities($elementRoomType['!RoomTypeName']); ?></td></tr><?php
	}
	?>
	</table>
	<?php /*
	<pre>
		<?php $RoomAmenitiesDebug = var_export($RoomAmenities); echo htmlentities($RoomAmenitiesDebug); ?>
	</pre>
	*/ ?>
	<?php
	
	for ($i = 0; $i < count($RoomAmenities); $i++){
		foreach($RoomAmenities[$i] as $RoomAmenitie){
			if (!isset($amenities[$RoomAmenitie['!ExistsCode']])){
				$amenities[$RoomAmenitie['!ExistsCode']] = $RoomAmenitie['!RoomAmenity'];
			}
		}
	}
	
	//$amenitiesUnique = array_unique($amenities);
	
	?>
	<table>
		<tr><th>Código de amenidad</th><th>Nombre de la amenidad</th></tr>
	<?php
	foreach($amenities as $amenitieCode => $amenitieName){
		?><tr><td><?php echo $amenitieCode; ?></td><td><?php echo htmlentities($amenitieName); ?></td></tr><?php
	}
	?>
	</table>
	<?php
}

function btq_booking_tc_admin_debug_packages($hotelCode = '131328') {
	$response = btq_booking_tc_soap_query($hotelCode, '2018-09-11', '2018-09-12', 'packages');
	
	$RoomAmenities = array();
	$amenities = array();
	
	$RoomType = $response['RoomStays']['RoomStay']['RoomTypes']['RoomType'];
	
	?>
	<table>
		<tr><th>Código de habitación</th><th>Nombre de la habitación</th></tr>
	<?php
	foreach($RoomType as $elementRoomType){
		$RoomAmenities[] = $elementRoomType['Amenities']['Amenity'];
		?><tr><td><?php echo $elementRoomType['!RoomTypeCode']; ?></td><td><?php echo htmlentities($elementRoomType['!RoomTypeName']); ?></td></tr><?php
	}
	?>
	</table>
	<?php /*
	<pre>
		<?php $RoomAmenitiesDebug = var_export($RoomAmenities); echo htmlentities($RoomAmenitiesDebug); ?>
	</pre>
	*/ ?>
	<?php
	
	for ($i = 0; $i < count($RoomAmenities); $i++){
		foreach($RoomAmenities[$i] as $RoomAmenitie){
			if (!isset($amenities[$RoomAmenitie['!ExistsCode']])){
				$amenities[$RoomAmenitie['!ExistsCode']] = $RoomAmenitie['!RoomAmenity'];
			}
		}
	}
	
	//$amenitiesUnique = array_unique($amenities);
	
	?>
	<table>
		<tr><th>Código de amenidad</th><th>Nombre de la amenidad</th></tr>
	<?php
	foreach($amenities as $amenitieCode => $amenitieName){
		?><tr><td><?php echo $amenitieCode; ?></td><td><?php echo htmlentities($amenitieName); ?></td></tr><?php
	}
	?>
	</table>
	<?php
}

function btq_booking_tc_admin_debug_page() {
?>
	<div class="wrap">
		<h1>Debug TravelClick</h1>
		<!--
		<form method="post" action="options.php">
			<?php settings_fields( 'btq-booking-tc-settings' ); ?>
			<?php do_settings_sections( 'btq-booking-tc-settings' ); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('Hotel code english language', 'btq-booking-tc'); ?></th>
					<td><textarea name="hotel_soap" type="textarea" cols="" rows=""><?php echo esc_attr( get_option('hotel_soap') ); ?></textarea></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		-->
		<div style="background-color: white;">
			<?php btq_booking_tc_admin_debug_rooms('95698'); ?>
			<?php btq_booking_tc_admin_debug_rooms('131328'); ?>
		</div>
		<pre style="background-color: white;">
		<?php
			/*
			$images_dir = 'assets/images/340132';
			
			$images_path = plugin_dir_path( __FILE__ ) . $images_dir;
			//$image_url = plugins_url( $images_dir . DIRECTORY_SEPARATOR . 'wordpress.png', __FILE__ );
			echo $images_path . "\n";
			
			$images = btq_booking_tc_grid_get_images($images_path);
			echo var_export($images, TRUE) . "\n\n";
			
			foreach($images as $image_name){
				$image_url = plugins_url( $images_dir . DIRECTORY_SEPARATOR . $image_name, __FILE__ );
				echo $image_url . "\n";
			}
			*/
		?>
		</pre>
	</div><!-- wrap -->
<?php
}

function btq_booking_tc_grid_split_description($string){
	if(!empty($string) && is_string($string)){
		$stringStripTags = strip_tags($string);
		$wordsArray = explode(' ', $stringStripTags);
		
		$wordsArrayFirst = Array();
		$wordsArrayLast  = Array();
		for($i = 0; $i < count($wordsArray); $i++){
			if ($i <= 30) {
				$wordsArrayFirst[] = $wordsArray[$i];
			}
			else {
				$wordsArrayLast[] = $wordsArray[$i];
			}
		}
		
		$textFirst = implode(' ', $wordsArrayFirst);
		$textLast  = implode(' ', $wordsArrayLast);
		
		?>
		<div><?php echo $textFirst; ?> <a class="vermas">Ver más</a></div>
		<div class="texto_recorrido" style="display:none"><?php echo $textLast; ?></div>
		<?php
	}
}

function btq_booking_tc_grid_get_images($path) {
	$files = scandir($path);
	$images = array();
	
	foreach ($files as $file) {
		if (!is_dir($path . DIRECTORY_SEPARATOR . $file)){
			if (preg_match('/^.*\.(jpg|jpeg|png|gif)$/', $file) !== FALSE) array_push($images, $file);
		}
	}
	
	return $images;
}

function btq_booking_tc_grid_rooms($language = 'es', $dateRangeStart = '2018-09-21', $dateRangeEnd = '2018-09-22', $typeQuery = 'rooms', $rooms = 1, $adults = 1, $childrens = 0, $availRatesOnly = 'true'){
	
	switch($language){
		case 'es':
			$hotelCode = '131328';
			$currency  = 'MXN';
		break;
		case 'en':
			$hotelCode = '95698';
			$currency  = 'USD';
		break;
	}
	
	$response = btq_booking_tc_soap_query($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery, $rooms, $adults, $childrens, $availRatesOnly);
	
	// Debug Log
	$response_log = var_export($response, TRUE);
	btq_booking_tc_log('grid_rooms', $response_log);
	
	$RoomType = $response['RoomStays']['RoomStay']['RoomTypes']['RoomType'];
	
	$arrayRoomType = array();
	foreach($RoomType as $RoomTypeElement){
		$arrayRoomType[] = $RoomTypeElement;
	}
	
	
	$RoomRate = $response['RoomStays']['RoomStay']['RoomRates']['RoomRate'];
	
	$arrayRoomRate = array();
	foreach($RoomRate as $RoomRateElement){
		$arrayRoomRate[] = $RoomRateElement;
	}
	
	$images_path = 'assets/images/';
	
	$i = 0;
	foreach($arrayRoomType as $elementRoomType){
		$roomTypeCode = $elementRoomType['!RoomTypeCode'];
		$images_dir = plugin_dir_path( __FILE__ ) . $images_path . $roomTypeCode;
		$images = btq_booking_tc_grid_get_images($images_dir);
		?>
		
		<section class="row">
			
			<article class="col-md-5">
				<div id="btq-carousel-<?php echo $roomTypeCode; ?>" class="carousel slide" data-ride="carousel">
					<!-- Indicators -->
					<ol class="carousel-indicators">
					<?php 
					$count_img = 0;
					foreach ($images as $im) {
					$class_active = ($count_img == 0) ? ' class="active"' : '';
					?>
						<li data-target="#btq-carousel-<?php echo $roomTypeCode; ?>" data-slide-to="<?php echo $count_img; ?>"<?php echo $class_active; ?>></li>
					<?php
					$count_img++;
					}
					?>
					</ol>
					
					<!-- Wrapper for slides -->
					<div class="carousel-inner">
					<?php
					$count_img = 1;
					foreach ($images as $image_name) {
					$image_url = plugins_url( $images_path . $roomTypeCode . DIRECTORY_SEPARATOR . $image_name, __FILE__ );
					$class_active = ($count_img == 1) ? ' active' : '';
					?> 
						<div class="item<?php echo $class_active?>">
							<img src="<?php echo $image_url; ?>" alt="Habitaciones">
						</div>
					<?php
					$count_img++;
					}
					?>
					</div>

					<!-- Left and right controls -->
					<a class="left carousel-control" href="#btq-carousel-<?php echo $roomTypeCode; ?>" data-slide="prev">
						<span class="glyphicon glyphicon-chevron-left"></span>
						<span class="sr-only">Anterior</span>
					</a>
					<a class="right carousel-control" href="#btq-carousel-<?php echo $roomTypeCode; ?>" data-slide="next">
						<span class="glyphicon glyphicon-chevron-right"></span>
						<span class="sr-only">Siguiente</span>
					</a>
				</div>
			</article>
			
			<article class="col-md-4">
				<h3 class="titulo"><?php echo $elementRoomType['!RoomTypeName'] ?></h3>
				<?php btq_booking_tc_grid_split_description($elementRoomType['RoomDescription']['Text']['!Text']); ?>
				
				<?php
				foreach($elementRoomType['Amenities']['Amenity'] as $RoomAmenitie){
					if ( isset( $RoomAmenitie['!ExistsCode'] ) ){
						//$RoomAmenitie['!ExistsCode'], $RoomAmenitie['!RoomAmenity'];
						$amenityCode     = $RoomAmenitie['!ExistsCode'];
						$amenityFileName = btq_booking_tc_amenity_icon_name($amenityCode);
						if (!empty($amenityFileName)) {
							$image_icono_url = plugins_url( $images_path . DIRECTORY_SEPARATOR . 'amenity' . DIRECTORY_SEPARATOR . $amenityFileName, __FILE__ );
							?>
							<img class="iconoshabitacion" src="<?php echo $image_icono_url; ?>" alt="<?php echo htmlentities($RoomAmenitie['!RoomAmenity']); ?>" title="<?php echo htmlentities($RoomAmenitie['!RoomAmenity']); ?>" width="40" height="40">
							<?php
						}
						else {
							error_log( 'ExistsCode: ' . $RoomAmenitie['!ExistsCode'] . ' - RoomAmenity: ' . $RoomAmenitie['!RoomAmenity'] );
						} 
					}
				}
				?>
							
				<hr class="linealetras" style="border-color:#C9B891;" style="border:2px;" />
				<img src="<?php echo plugins_url( $images_path . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . 'icon_like.png', __FILE__ ); ?>" alt="Like" width="25" height="25">
				<img src="<?php echo plugins_url( $images_path . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . 'icon_heart_uns.png', __FILE__ ); ?>" alt="Heart" width="25" height="25">
			</article>
			
			<article class="col-md-3 grisfondo">
				<form>
				<?php
				$rate_room = array();
				for ($j = 0; $j < count($arrayRoomRate); $j++) {
					if(isset($arrayRoomRate[$j]['!RoomTypeCode'])) {
						if($arrayRoomRate[$j]['!RoomTypeCode'] == $roomTypeCode) array_push($rate_room, $arrayRoomRate[$j]);
					}
				}
										
				for ($l = 0; $l < count($rate_room); $l++) {
					?>
					<label class="radio-inline">
					<input type="radio" name="optradio">$<?php echo $currency . " " . (($language == 'es')?$rate_room[$l]['Total']['!AmountAfterTax']:$rate_room[$l]['Total']['!AmountBeforeTax']); ?> <br> <?php echo $rate_room[$l]['!RatePlanName']; ?>
					</label>
					<?php
					if ($precio == 0) { 
						/* Inicializa el valor de precio*/
						$precio = ($language == 'es')?$rate_room[$l]['Total']['!AmountAfterTax']:$rate_room[$l]['Total']['!AmountBeforeTax'];
					} 
					else {
						if ($precio > $rate_room[$l]['Total']['!AmountAfterTax']){ /* Valida que sea el precio menor*/
							$precio = $rate_room[$l]['Total']['!AmountAfterTax'];
						}
					}
				}
				?>
				</form>
				
				<hr class="linea"/>
				<h3 align="center">$<?php echo $currency . " " . $precio; ?>/noche</h3>
				<button type="button" class="btn btq-btn" onclick="location.href='https://reservations.travelclick.com/<?php echo $hotelCode ?>?themeid=<?php echo $theme ?>&amp;datein=<?php echo date_format(date_create($startDate), "m/d/Y");?>&amp;dateout=<?php echo date_format(date_create($endDate), "m/d/Y");?>&amp;roomtypeid=<?php echo $roomTypeCode; ?>&amp;adults=<?php echo $adults; ?>&amp;children=<?php echo $children; ?>&amp;rooms=<?php echo $rooms ?>&amp;currency=<?php echo $currency?>'">Reservar Ahora</button>
			</article>
			
		</section>
		
		<hr class="lineaabajo" />
		<?php
		$i++;
		$precio = 0;
	} // foreach($arrayRoomType as $elementRoomType)
} // function btq_booking_tc_grid_rooms()

function btq_booking_tc_grid_packages($language = 'es', $dateRangeStart = '2018-09-21', $dateRangeEnd = '2018-09-22', $typeQuery = 'rooms', $rooms = 1, $adults = 1, $childrens = 0, $availRatesOnly = 'true'){
	
	switch($language){
		case 'es':
			$hotelCode = '131328';
			$currency  = 'MXN';
		break;
		case 'en':
			$hotelCode = '95698';
			$currency  = 'USD';
		break;
	}
	
	$response = btq_booking_tc_soap_query($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery, $rooms, $adults, $childrens, $availRatesOnly);
	
	// Debug Log
	$response_log = var_export($response, TRUE);
	btq_booking_tc_log('grid_packages', $response_log);
	
	$ResponseRatePlan = $response['RoomStays']['RoomStay']['RatePlans']['RatePlan'];
	
	$arrayRatePlan = array();
	foreach($ResponseRatePlan as $RatePlanElement){
		if ($RatePlanElement['!RatePlanType'] == 'Package'){
			$arrayRatePlan[] = $RatePlanElement;
		}
	}
	
	// Debug Log
	$response_log = var_export($arrayRatePlan, TRUE);
	btq_booking_tc_log('grid_packages_rate_plan', $response_log);
	
	
	$ResponseRoomRate = $response['RoomStays']['RoomStay']['RoomRates']['RoomRate'];
	
	$arrayRoomRate = array();
	foreach($ResponseRoomRate as $RoomRateElement){
		if ($RoomRateElement['!RatePlanType'] == 'Package'){
			$arrayRoomRate[$RoomRateElement['!RatePlanCode']] = $RoomRateElement;
		}
	}
	
	// Debug Log
	$response_log = var_export($arrayRoomRate, TRUE);
	btq_booking_tc_log('grid_packages_room_rate', $response_log);
	
	$ResponseRoomType = $response['RoomStays']['RoomStay']['RoomTypes']['RoomType'];
	
	$arrayRoomTypeAll = array();
	foreach($ResponseRoomType as $RoomTypeElement){
		$arrayRoomTypeAll[$RoomTypeElement] = $RoomTypeElement;
	}
	
	$arrayRoomType = array();
	foreach($arrayRoomTypeAll as $arrayRoomTypeAllElement){
		foreach($arrayRoomRate as $arrayRoomRateElement){
			if($arrayRoomTypeAllElement['!RoomTypeCode'] == $arrayRoomRateElement['!RoomTypeCode']){
				$arrayRoomType[$arrayRoomTypeAllElement['!RoomTypeCode']] = $arrayRoomTypeAllElement;
			}
		}
	}
	
	// Debug Log
	$response_log = var_export($arrayRoomType, TRUE);
	btq_booking_tc_log('grid_packages_room_type', $response_log);
	
	
	$images_path = 'assets/images/';
	
	$i = 0;
	foreach($arrayRatePlan as $elementRatePlan){
		
		$RatePlanCode = $elementRatePlan['!RatePlanCode'];
		$roomRate = $arrayRoomRate[$RatePlanCode];
		$roomTypeCode = $roomRate['!RoomTypeCode'];
		$roomType = $arrayRoomType[$roomTypeCode];
		
		// Debug Log
		$response_log = var_export($roomRate, TRUE);
		btq_booking_tc_log('grid_packages_for_room_rate', $response_log);
		
		$images_dir = plugin_dir_path( __FILE__ ) . $images_path . $roomTypeCode;
		$images = btq_booking_tc_grid_get_images($images_dir);
		?>
		
		<section class="row">
			
			<article class="col-md-5">
				<div id="btq-carousel-<?php echo $RatePlanCode; ?>" class="carousel slide" data-ride="carousel">
					<!-- Indicators -->
					<ol class="carousel-indicators">
					<?php 
					$count_img = 0;
					foreach ($images as $im) {
					$class_active = ($count_img == 0) ? ' class="active"' : '';
					?>
						<li data-target="#btq-carousel-<?php echo $RatePlanCode; ?>" data-slide-to="<?php echo $count_img; ?>"<?php echo $class_active; ?>></li>
					<?php
					$count_img++;
					}
					?>
					</ol>
					
					<!-- Wrapper for slides -->
					<div class="carousel-inner">
					<?php
					$count_img = 1;
					foreach ($images as $image_name) {
					$image_url = plugins_url( $images_path . $roomTypeCode . DIRECTORY_SEPARATOR . $image_name, __FILE__ );
					$class_active = ($count_img == 1) ? ' active' : '';
					?> 
						<div class="item<?php echo $class_active?>">
							<img src="<?php echo $image_url; ?>" alt="Habitaciones">
						</div>
					<?php
					$count_img++;
					}
					?>
					</div>

					<!-- Left and right controls -->
					<a class="left carousel-control" href="#btq-carousel-<?php echo $RatePlanCode; ?>" data-slide="prev">
						<span class="glyphicon glyphicon-chevron-left"></span>
						<span class="sr-only">Anterior</span>
					</a>
					<a class="right carousel-control" href="#btq-carousel-<?php echo $RatePlanCode; ?>" data-slide="next">
						<span class="glyphicon glyphicon-chevron-right"></span>
						<span class="sr-only">Siguiente</span>
					</a>
				</div>
			</article>
			
			<article class="col-md-4">
				<h3 class="titulo"><?php echo $elementRatePlan['!RatePlanName'] ?></h3>
				<?php btq_booking_tc_grid_split_description($elementRatePlan['RatePlanDescription']['Text']['!Text']); ?>
				
				<?php
				foreach($roomType['Amenities']['Amenity'] as $RoomAmenitie){
					if ( isset( $RoomAmenitie['!ExistsCode'] ) ){
						//$RoomAmenitie['!ExistsCode'], $RoomAmenitie['!RoomAmenity'];
						$amenityCode     = $RoomAmenitie['!ExistsCode'];
						$amenityFileName = btq_booking_tc_amenity_icon_name($amenityCode);
						if (!empty($amenityFileName)) {
							$image_icono_url = plugins_url( $images_path . DIRECTORY_SEPARATOR . 'amenity' . DIRECTORY_SEPARATOR . $amenityFileName, __FILE__ );
							?>
							<img class="iconoshabitacion" src="<?php echo $image_icono_url; ?>" alt="<?php echo htmlentities($RoomAmenitie['!RoomAmenity']); ?>" title="<?php echo htmlentities($RoomAmenitie['!RoomAmenity']); ?>" width="40" height="40">
							<?php
						}
						else {
							error_log( 'ExistsCode: ' . $RoomAmenitie['!ExistsCode'] . ' - RoomAmenity: ' . $RoomAmenitie['!RoomAmenity'] );
						} 
					}
				}
				?>
							
				<hr class="linealetras" style="border-color:#C9B891;" style="border:2px;" />
				<img src="<?php echo plugins_url( $images_path . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . 'icon_like.png', __FILE__ ); ?>" alt="Like" width="25" height="25">
				<img src="<?php echo plugins_url( $images_path . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . 'icon_heart_uns.png', __FILE__ ); ?>" alt="Heart" width="25" height="25">
			</article>
			
			<article class="col-md-3 grisfondo">
				<form>
					<label class="radio-inline">
	                  <input type="radio" name="optradio">$<?php echo $currency . " " . (($language == 'es')?$roomRate['Total']['!AmountAfterTax']:$roomRate['Total']['!AmountBeforeTax']); ?> <br> <?php echo $roomRate['!RoomTypeName']; ?>
	                </label>
				</form>

				<?php
				/* Inicializa el valor de precio*/
				$precio = ($language == 'es')?$roomRate['Total']['!AmountAfterTax']:$roomRate['Total']['!AmountBeforeTax'];
				?>
					
				<hr class="linea"/>
				<h3 align="center">$<?php echo $currency . " " . $precio; ?>/noche</h3>
				<button type="button" class="btn btq-btn" onclick="location.href='https://reservations.travelclick.com/<?php echo $hotelCode ?>?themeid=<?php echo $theme ?>&amp;datein=<?php echo date_format(date_create($startDate), "m/d/Y");?>&amp;dateout=<?php echo date_format(date_create($endDate), "m/d/Y");?>&amp;roomtypeid=<?php echo $roomTypeCode; ?>&amp;adults=<?php echo $adults; ?>&amp;children=<?php echo $children; ?>&amp;rooms=<?php echo $rooms ?>&amp;currency=<?php echo $currency?>'">Reservar Ahora</button>
			</article>
			
		</section>
		
		<hr class="lineaabajo" />
		<?php
		$i++;
		$precio = 0;
	} // foreach($arrayRoomType as $elementRatePlan)
} // function btq_booking_tc_grid_packages()

function btq_booking_tc_grid_form($language = 'es') {
	$iconos_dir = 'assets/images/iconos';
	?>
		<hr class="linea"/>	
		
		<section class="row">
			<article class="col-md-12">
				<h5 class="hosp"><?php _e('Select a PACKAGE or ROOM','btq-booking-tc'); ?></h5>
			</article>
		</section>

		<hr class="linea" />

		<section class="row">
			<div class="col-xs-12 col-md-4">
				<button id="btq-btn-rooms" name="btq-btn-rooms" class="btn btn-default btq-btn"><?php _e('Rooms','btq-booking-tc'); ?></button>
			</div>
			<div class="col-xs-12 col-md-4">
				<button id="btq-btn-packages" name="btq-btn-rooms" class="btn btq-btn"><?php _e('Packages','btq-booking-tc'); ?></button>
			</div>
			<div class="col-xs-12 col-md-4">
				<button id="btq-btn-top" name="btq-btn-top" class="btn btq-btn"><?php _e('Top Rated','btq-booking-tc'); ?></button>
			</div>
		</section>
		
		<hr class="linea" />
		
		<section class="row">
			
			<form name="btq-booking-tc-form" id="btq-booking-tc-form" action="" target="_self" method="post">
					
				<article class="col-xs-12 col-md-4">
					<div class="row">
						<div class="col-xs-6">
							<div class="form-group">
								<input class="btq-input" id="btq-date-start" name="btq-date-start" placeholder="<?php _e('Arrival Date','btq-booking-tc'); ?>">
							</div>
						</div>
						<div class="col-xs-6">
							<div class="form-group">
								<input class="btq-input" id="btq-date-end" name="btq-date-end" placeholder="<?php _e('Departure Date','btq-booking-tc'); ?>">
							</div>		
						</div>
					</div>
				</article>
				
				<article class="col-xs-12 col-md-4">
					<div class="row">
						<div class="col-xs-6">
							<div class="form-group">
								<select class="btq-select" id="btq-num-adults" name="btq-num-adults">
									<?php for ($i = 1; $i <= 9; $i ++) { ?>
									<option value="<?php echo $i; ?>"><?php echo sprintf( _n( '%s Adult', '%s Adults', $i, 'btq-booking-tc' ), $i); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-xs-6">
							<div class="form-group">
								<select class="btq-select" id="btq-num-children" name="btq-num-children">
									<?php for ($i = 0; $i <= 9; $i ++) { ?>
									<option value="<?php echo $i; ?>"><?php echo sprintf( _n( '%s Children', '%s Children', $i, 'btq-booking-tc' ), $i); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
				</article>
				
				<article class="col-xs-12 col-md-2">
					<div class="form-group">
						<select class="btq-select" id="btq-num-rooms" name="btq-num-rooms">
							<?php for ($i = 1; $i <= 9; $i ++) { ?>
							<option value="<?php echo $i; ?>"><?php echo sprintf( _n( '%s Room', '%s Rooms', $i, 'btq-booking-tc' ), $i); ?></option>
							<?php } ?>
						</select>
					</div>
				</article>
				
				<article class="col-xs-12 col-md-2">	
					<input type="hidden" id="btq-type-query" name="btq-type-query" value="rooms">				
					<button class="btn btq-btn" name="btq-search" id="btq-search"><?php _e('SEARCH','btq-booking-tc'); ?></button>
				</article>
			
			</form>
			
		</section>

		<hr class="linea"/>
	<?php
}

add_action( 'wp_enqueue_scripts', 'btq_booking_tc_grid_scripts', 1001 );
function btq_booking_tc_grid_scripts() {
    wp_enqueue_style( 'btq-booking-tc-grid', plugins_url( 'assets/css' . DIRECTORY_SEPARATOR . 'estilos.css', __FILE__ ), 'solaz-child-style','1.0.0');
    wp_enqueue_script( 'btq-booking-tc-grid-js', plugins_url( 'assets/js' . DIRECTORY_SEPARATOR . 'app.js', __FILE__ ), array(), '1.0.0');
    wp_enqueue_script( 'moment', plugins_url( 'assets/js' . DIRECTORY_SEPARATOR . 'moment.min.js', __FILE__ ), array(), '2.21.0', true);
}

add_action( 'vc_before_init', 'btq_booking_tc_grid_VC' );
function btq_booking_tc_grid_VC() {
	vc_map(array(
		'name'     => __( 'BTQ Booking', 'btq-booking-tc' ),
		'base'     => 'btq-booking-tc-grid',
		'class'    => '',
		'category' => __( 'Content', 'btq-booking-tc'),
		'icon'     => plugins_url( 'assets/images/iconos' . DIRECTORY_SEPARATOR . 'btqdesign-logo.png', __FILE__ )
	));
}

add_shortcode( 'btq-booking-tc-grid', 'btq_booking_tc_grid_shortcode' );
function btq_booking_tc_grid_shortcode() {	
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$language = ICL_LANGUAGE_CODE;
	}
	else {
		$language = 'es';
	}
	
	ob_start();
	?>
	<div class="container">
    <?php
	btq_booking_tc_grid_form($language);
	?>
	<div id="btq-booking-grid">
		<?php
		btq_booking_tc_grid_rooms($language, date('Y-m-d', ( time() + (60*60*24*90) )), date('Y-m-d', ( time() + (60*60*24*91) )) );
		?>
	</div>
	</div>
	<?php
	$out = ob_get_clean();
	
	return $out;
}

add_action( 'wp_ajax_btq_booking_tc_grid', 'btq_booking_tc_grid_ajax' );
add_action( 'wp_ajax_nopriv_btq_booking_tc_grid', 'btq_booking_tc_grid_ajax' );
function btq_booking_tc_grid_ajax() {
	// Debug Log
	$post_log = var_export($_POST, TRUE);
	btq_booking_tc_log('ajax-post', $post_log);
	
	if (isset(
		$_POST['data'],
		$_POST['data']['btq_date_start'],
		$_POST['data']['btq_date_end'],
		$_POST['data']['btq_type_query'],
		$_POST['data']['btq_num_rooms'],
		$_POST['data']['btq_num_adults'],
		$_POST['data']['btq_num_children']
	)){
		$post_data = $_POST['data'];
		
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$language = ICL_LANGUAGE_CODE;
		}
		else {
			$language = 'es';
		}
		
		if ($post_data['btq_type_query'] == 'rooms'){
			btq_booking_tc_grid_rooms($language, $post_data['btq_date_start'], $post_data['btq_date_end'], $post_data['btq_type_query'], $post_data['btq_num_rooms'], $post_data['btq_num_adults'], $post_data['btq_num_children']);
		}
		elseif ($post_data['btq_type_query'] == 'packages'){
			btq_booking_tc_grid_packages($language, $post_data['btq_date_start'], $post_data['btq_date_end'], $post_data['btq_type_query'], $post_data['btq_num_rooms'], $post_data['btq_num_adults'], $post_data['btq_num_children']);
		}
		else {
			echo '';
		}
	}
	else {
		echo '';
	}
}