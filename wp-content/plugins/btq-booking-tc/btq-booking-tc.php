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
if (!defined('ABSPATH')) {
    exit;
}

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
	
	if ($typeQuery == 'package'){
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
	
	return array('envelope' => $soapEnvelope, 'wsaTo' => $wsaTo);
	
}

function btq_booking_tc_soap_query($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery = 'rooms', $rooms = 1, $adults = 1, $childrens = 0, $availRatesOnly = 'true'){
	require_once('lib/nusoap.php');
	
	$soap = btq_booking_tc_soap_query_string($hotelCode, $dateRangeStart, $dateRangeEnd, $typeQuery = 'rooms', $rooms = 1, $adults = 1, $childrens = 0, $availRatesOnly = 'true');
	
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
			<?php btq_booking_tc_grid_rooms(); ?>
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

function btq_booking_tc_grid_rooms(){
	$response = btq_booking_tc_soap_query('131328', '2018-09-11', '2018-09-12');
	
	//$debug = var_export($response, TRUE);
	//echo htmlentities($debug);
	
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
	
	$images_dir = 'assets/images/';
	
	$i = 0;
	foreach($arrayRoomType as $elementRoomType){
		$roomTypeCode = $elementRoomType['!RoomTypeCode'];
		$images_path = plugin_dir_path( __FILE__ ) . $images_dir . $roomTypeCode;
		$images = btq_booking_tc_grid_get_images($images_path);
		
		$count_img = 1;
		foreach ($images as $im) {
			?>
			<div id="item-<?php echo $count_img; ?>" class="control-operator"></div>
			<?php
			$count_img++;
		}
		?>
		<div class="gallery autoplay items-<?php echo count($images); ?>">	
		<?php
		
		$count_img = 1;
		foreach ($images as $image_name) {
			$image_url = plugins_url( $images_dir . DIRECTORY_SEPARATOR . $image_name, __FILE__ );
			?>
			<img src="<?php echo $image_url?>" alt="" class="img-responsive"/> 
			<?php
			$count_img++;
		}

		$count_img = 1;
		foreach ($images as $im) {                       
			?>
			<a class="control-item" href="#item-<?php echo $count_img; ?>">&#8226;</a> 
			<?php
			$count_img++;
		}
		?>
		<h3 style="font-family: latoreg!important; padding-top:20px;"><?php echo $elementRoomType['!RoomTypeName'] ?></h3>
		<span class="descripRoom"><?php echo $elementRoomType['RoomDescription']['Text']['!Text'] ?></span>
		<?php
		$amenities = $elementRoomType['Amenities']['Amenity'];
		$countAmenities = count($amenities);
			            
		for ($j = 0; $j < $countAmenities; $j++) { 
			$amenity = $amenities[$j]['!RoomAmenity'];
			try {
				if (isset($iconos[$amenities[$j]['!ExistsCode']])){
					$icono = $iconos[$amenities[$j]['!ExistsCode']];
					$image_icono_url = plugins_url( $images_dir . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . $icono, __FILE__ );
					if ($amenity != null) { 
						?>
						<img src="<?php echo $image_icono_url; ?>" width="30" alt="" class="text-center"/>
						<span class="titleIcon text-center"><?php echo $amenity; ?></span>
						<?php
					}
				}
			} 
			catch (Exception $e) {
				echo 'Excepción capturada: ',  $e->getMessage(), "\n";
			}
		} 
		?> 
		<img src="<?php echo plugins_url( $images_dir . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . 'icon_like.png', __FILE__ ); ?>" alt="Like" width="25" height="25">
		<img src="<?php echo plugins_url( $images_dir . DIRECTORY_SEPARATOR . 'iconos' . DIRECTORY_SEPARATOR . 'icon_heart_uns.png', __FILE__ ); ?>" alt="Favorite" width="25" height="25">
		<?php
		$rate_room = array();
		for ($j = 0; $j < count($arrayRoomRate); $j++) {
			if(isset($arrayRoomRate[$j]['!RoomTypeCode'])) {
				if($arrayRoomRate[$j]['!RoomTypeCode'] == $roomTypeCode) array_push($rate_room, $arrayRoomRate[$j]);
			}
		}
		
		$last_index = $index;
								
		for ($l = 0; $l < count($rate_room); $l++) {
			?>
			<input type="radio" class="ratePlanID" name="bestRate" value="bestRate" id="<?php echo "r_" . $rate_room[$l]['!RatePlanCode'] . '_' . $roomTypeCode; ?>"> <?php echo $rate_room[$l]['!RatePlanName']; ?>
			<span style="text-decoration:line-through; font-size:.75em!important;color:#666666">$ <?php echo $currency . " " . (($lang == "es")?($rate_room[$l]['Total']['!AmountAfterTax']+$rate_room[$l]['Total']['!Discount']):$rate_room[$l]['Total']['!GrossAmountBeforeTax']) ?></span><br/>
			<span style="font-size:.93em!important;">$ <?php echo $currency . " " . (($lang == "es")?$rate_room[$l]['Total']['!AmountAfterTax']:$rate_room[$l]['Total']['!AmountBeforeTax']); ?></span>
			<?php
			if ($precio == 0) { 
				/* Inicializa el valor de precio*/
				$precio = ($lang == "es")?$rate_room[$l]['Total']['!AmountAfterTax']:$rate_room[$l]['Total']['!AmountBeforeTax'];
			} 
			else {
				if ($precio > $rate_room[$l]['Total']['!AmountAfterTax']){ /* Valida que sea el precio menor*/
					$precio = $rate_room[$l]['Total']['!AmountAfterTax'];
				}
			}
		}
		?>
		<span style="font-family: latoreg;"><?php echo $currency . " " . $precio; ?>&nbsp;</span>
		<span style="font-family: bulterstencillight; font-size: 14px;">/<?php echo $noche; ?></span>
		<a id="room<?php echo $roomTypeCode?>" href="https://reservations.travelclick.com/<?php echo $hotelCode ?>?themeid=<?php echo $theme ?>&amp;datein=<?php echo date_format(date_create($startDate), "m/d/Y");?>&amp;dateout=<?php echo date_format(date_create($endDate), "m/d/Y");?>&amp;roomtypeid=<?php echo $roomTypeCode; ?>&amp;adults=<?php echo $adults; ?>&amp;children=<?php echo $children; ?>&amp;rooms=<?php echo $rooms ?>&amp;currency=<?php echo $currency?>" 
		target="_blank" class="btn-group btn btn-primary"
		data-hover="" data-border-color="" data-bg="#c69907" data-hover-bg="" data-border-hover="" data-shadow-hover=""
		data-shadow-click="none" data-shadow="" data-shd-shadow="" data-ultimate-target="#ubtn-7197" data-responsive-json-new="{&quot;font-size&quot;:&quot;&quot;,&quot;line-height&quot;:&quot;&quot;}"
		style="font-weight:normal;border:none;background: #c69907;color: #ffffff;width:100%;">
			<span class="ubtn-data ubtn-text "><?php echo $reserva; ?></span>
		</a>
		<?php
		$i++;
		$precio = 0;
	} // foreach($arrayRoomType as $elementRoomType)
} // function btq_booking_tc_grid_rooms()

function btq_booking_tc_grid_form() {
	?>
	<hr class="linea"/>
	
	<section class="row">
		<article class="col-md-5">
			<h5>Selecciona un PAQUETE o HABITACIÓN</h5>
		</article>
		<article class="col-md-7">
			<img src="<?php echo plugins_url( $iconos_dir . DIRECTORY_SEPARATOR . 'gh_calendar2.png', __FILE__ ); ?>" width="30" height="30" id="element1">
			<h5 class="hosp">&nbsp;&nbsp;&nbsp;Consulta tus fechas-tarifa para hospedarte</h5>
		</article>
	</section>

	<hr class="linea" />

	<section class="row">
		
		<article class="col-md-5">
		<button class="button col-xs-12 col-md-4">Habitaciones</button>
		<div class="clearfix visible-xs-block"></div>
		<button class="button col-xs-12 col-md-3">Paquetes</button>
		<div class="clearfix visible-xs-block"></div>
		<button class="button col-xs-12 col-md-5"><img src="<?php echo plugins_url( $iconos_dir . DIRECTORY_SEPARATOR . 'gh_like.png', __FILE__ ); ?>" width="20" height="22" id="element1">Mejor Calificadas</button>
		</article>

		<article class="col-md-5">

			  <input class="buttonpick col-xs-6" id="entrada" src="<?php echo plugins_url( $iconos_dir . DIRECTORY_SEPARATOR . 'gh_calendar1.png', __FILE__ ); ?>" width="30" height="30" placeholder="Fecha de entrada">

			  <input class="buttonpickk col-xs-6" id="salida" placeholder="Fecha de salida">			

			  <div class="clearfix visible-xs-block"></div>	

				<select class="buttonpick2 col-xs-6">
								<option value="1">1 Adulto</option>
								<option value="2">2 Adultos</option>
								<option value="3">3 Adultos</option>
								<option value="4">4 Adultos</option>
								<option value="5">5 Adultos</option>
								<option value="6">6 Adultos</option>
								<option value="7">7 Adultos</option>
								<option value="8">8 Adultos</option>
								<option value="9">9 Adultos</option>
				</select>

							<select class="buttonpick3 col-xs-6">
								
								
								<option value="0">0 Niños</option>
								<option value="1">1 Niño</option>
								<option value="2">2 Niños</option>
								<option value="3">3 Niños</option>
								<option value="4">4 Niños</option>
								<option value="5">5 Niños</option>
								<option value="6">6 Niños</option>
								<option value="7">7 Niños</option>
								<option value="8">8 Niños</option>
								<option value="9">9 Niños</option>
							</select>

		</article>

		<article class="col-md-2">
			
			<button class="buttonbus col-xs-12">BUSCAR</button>

		</article>

	</section>

	<hr class="linea"/>

	<section class="row">
		<article class="col-md-5"></article>
		<article class="col-md-7">
			<p class="recordatorio">*Recuerda que tener una reservación anticipada siempre será una mejor opción (tarifas mostradas a 90 días)</p>
		</article>
	</section>
	<?php
}