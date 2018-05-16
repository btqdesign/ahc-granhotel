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

function btq_booking_tc_admin_debug_page() {
?>
	<div class="wrap">
		<h1>Debug TravelClick</h1>
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
		<pre style="background-color: white;">
		<?php
			btq_booking_tc_soap_query();
		?>
		</pre>
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
		</soap:Body>
		';
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
		</soap:Body>
		';
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
		</soap:Header>
	';
	    
	$soapEnvelope = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsa="http://schemas.xmlsoap.org/ws/2004/08/addressing" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        '.$soapHeader.'
        '.$soapBody.'
	</soap:Envelope>';
	
	return array('envelope' => $soapEnvelope, 'wsaTo' => $wsaTo);
	
}

function btq_booking_tc_soap_query(){
	require_once('lib/nusoap.php');
	
	$soap = btq_booking_tc_soap_query_string('131328', '2018-08-14', '2018-08-15');
	
	echo htmlentities($soap['envelope'])."\n\n";
	
	$client = new nusoap_client($soap['wsaTo']);
	//$client->soap_defencoding = 'UTF-8';
	//$client->decode_utf8 = TRUE;

	//$result = $client->call($soap['wsaTo'], $soap['envelope']);
	
	echo var_export($client, TRUE);
}