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
		<pre style="background-color: white;">
		<?php
			//btq_booking_tc_grid();
			
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
	
	$path_images = plugin_dir_path( __FILE__ ) . 'assets/images/340132';
	
	$i = 0;
	foreach($arrayRoomType as $elementRoomType){
		$roomTypeCode = $elementRoomType['!RoomTypeCode'];
		$path_images = "$pathImageWP/$roomTypeCode";
		$images = get_images($path_images);
		?>
			<div class="col-xs-12 col-lg-12" style="border-top:1px solid #d1d1d1;">     
			    <div class="row">
			      <div class="col-xs-12 col-md-4 col-lg-4">
			          <!--all gallery-->
			          <div class="gallery"> 
			            <div id="control-item" class="control-operator">
			
			              <?php 
			                $count_img = 1;
			                foreach ($images as $im) {
			                ?>
			                  <div id="item-<?php echo $count_img; ?>" class="control-operator"></div>
			                <?php
			                  $count_img++;
			                }
			              ?>
			            </div>
			            <!--<a href="#popup" class="viewMore">More +</a> -->
			            <!--gallery-->
			            <div class="gallery autoplay items-<?php echo count($images); ?>">
			
			            <?php
			              $count_img = 1;
			              foreach ($images as $im) {
			                $rutaImagen = "/$pathImageWP/" . $roomTypeCode . "/" . $im ;
			            ?>
			              
			                <figure class="item custom-controls">
			                  <div class="superfluous">
			                    <img src="<?php echo $rutaImagen?>" alt="" class="img-responsive"/> 
			                  </div>
			                </figure>
			                <?php
			                  $count_img++;
			                }
			              ?>
			
			            <div class="controls"> 
			              <?php 
			                $count_img = 1;
			                foreach ($images as $im) {                       
			                ?>
			                  <a class="control-item" href="#item-<?php echo $count_img; ?>">&#8226;</a> 
			                <?php
			                  $count_img++;
			                }
			              ?>
			            </div>
			          <!--gallery--> 
			          </div>
			          <!--all gallery-->
			        </div>
			      </div>
			      <div class="col-xs-12 col-md-8 col-lg-8">
			        <div class="col-xs-12 col-md-7 col-lg-7">
			          <h3 style="font-family: latoreg!important; padding-top:20px;"><?php echo $elementRoomType['!RoomTypeName'] ?></h3>
			          <h4 style="font-family: latoreg!important; padding-left: 30px;"></h4>
			          <div style="width: 40px; height: 1px; background: #beab80; display: block; margin:15px 0; text-align: left;"></div>
			          <span class="descripRoom"><?php echo $elementRoomType['RoomDescription']['Text']['!Text'] ?></span>
			          <div class="col-lg-12">
			          <?php
			            $amenities = $elementRoomType['Amenities']['Amenity'];
			            $countAmenities = count($amenities);
			            
			            for ($j = 0; $j < $countAmenities; $j++) { 
			                $amenity = $amenities[$j]['!RoomAmenity'];
			
			                try {
			                    if (isset($iconos[$amenities[$j]['!ExistsCode']])){
			                        $icono = $iconos[$amenities[$j]['!ExistsCode']];
			                    
			                        if ($amenity != null) { ?>
			                            <div class="icons" style="display: inline-block; text-align:center; padding:10px;">
			                            <img src="/<?php echo $pathImageWP; ?>/iconos/<?php echo $icono; ?>" width="30" alt="" class="text-center"/>
			                            <br/>
			                            <span class="titleIcon text-center"><?php echo $amenity; ?></span>
			                            </div>
			                        <?php
			                        }
			                    }
			
			                } catch (Exception $e) {
			                    echo 'Excepción capturada: ',  $e->getMessage(), "\n";
			                }
			            } ?> 
			    
			            <hr style="border-color:#beab80;"/>
			    
			            <button type="button" id="ubtn-6182" class="ubtn ult-adjust-bottom-margin ult-responsive ubtn-normal ubtn-no-hover-bg  none  ubtn-sep-icon ubtn-sep-icon-at-left  ubtn-left  ubtn-only-icon buttomLike  tooltip-5a4b1adca7ea8 ubtn-tooltip left"
			              data-toggle="tooltip" data-placement="left" title="" data-hover="" data-border-color="" data-bg="" data-hover-bg=""
			              data-border-hover="" data-shadow-hover="" data-shadow-click="none" data-shadow="" data-shd-shadow="" data-ultimate-target="#ubtn-6182"
			              data-responsive-json-new="{&quot;font-size&quot;:&quot;&quot;,&quot;line-height&quot;:&quot;&quot;}" style="font-weight:normal;border:none;color: #c69907;"
			              data-original-title="Me gusta">
			                <img src="/<?php echo $pathImageWP; ?>/iconos/icon_like.png" alt="Like" width="25" height="25">
			              <span class="ubtn-hover" style="background-color:"></span>
			              <span class="ubtn-data ubtn-text "></span>
			            </button>
			    
			            <button type="button" id="ubtn-8548" class="ubtn ult-adjust-bottom-margin ult-responsive ubtn-normal ubtn-no-hover-bg  none  ubtn-sep-icon ubtn-sep-icon-at-left  ubtn-left  ubtn-only-icon buttomLike  tooltip-5a4b2077c6fd9 ubtn-tooltip right"
			              data-toggle="tooltip" data-placement="right" title="" data-hover="" data-border-color="" data-bg="" data-hover-bg=""
			              data-border-hover="" data-shadow-hover="" data-shadow-click="none" data-shadow="" data-shd-shadow="" data-ultimate-target="#ubtn-8548"
			              data-responsive-json-new="{&quot;font-size&quot;:&quot;&quot;,&quot;line-height&quot;:&quot;&quot;}" style="font-weight:normal;border:none;color: #c69907;"
			              data-original-title="Mi favorito">
			              <img src="/<?php echo $pathImageWP; ?>/iconos/icon_heart_uns.png" alt="Favorite" width="25" height="25">
			              <span class="ubtn-hover" style="background-color:"></span>
			              <span class="ubtn-data ubtn-text "></span>
			            </button>
			    
			          </div>
			        </div>
			        <div class="col-xs-12 col-md-5 col-lg-5">
			          <div style="background:#C9C9C9; padding: 10px; overflow:hidden;">
							<?php
								$rate_room = array();
								for ($j = 0; $j < count($arrayRoomRate); $j++) {
									//if ($i > 0)
									//	$index = $last_index + ($j + 1);
									//else
									//	$index = $i + $j;
									if(isset($arrayRoomRate[$j]['!RoomTypeCode'])) {
										if($arrayRoomRate[$j]['!RoomTypeCode'] == $roomTypeCode)
											array_push($rate_room, $arrayRoomRate[$j]);
									}
								}
								
								// Debug
								//echo '<h2>rate_room</h2><pre>' . var_export($rate_room, TRUE) . '</pre>';
								//return;
								
								$last_index = $index;
								
								for ($l = 0; $l < count($rate_room); $l++) {
									?>
									<div class="col-xs-12 col-lg-12" style="padding:0px!important;">
									
									    <div class="col-xs-12 col-lg-6" style="font-size:.75em; line-height:1.5em; padding:0px!important;">
									        <input type="radio" class="ratePlanID" name="bestRate" value="bestRate" id="<?php echo "r_" . $rate_room[$l]['!RatePlanCode'] . '_' . $roomTypeCode; ?>"> <?php echo $rate_room[$l]['!RatePlanName']; ?>
									    </div>
									    
									    <div class="col-xs-12 col-lg-6" style="text-align:right; padding:0px!important;">
									      <span style="text-decoration:line-through; font-size:.75em!important;color:#666666">$ <?php echo $currency . " " . (($lang == "es")?($rate_room[$l]['Total']['!AmountAfterTax']+$rate_room[$l]['Total']['!Discount']):$rate_room[$l]['Total']['!GrossAmountBeforeTax']) ?></span><br/>
									      <span style="font-size:.93em!important;">$ <?php echo $currency . " " . (($lang == "es")?$rate_room[$l]['Total']['!AmountAfterTax']:$rate_room[$l]['Total']['!AmountBeforeTax']); ?></span>
									    </div>
									    
									</div>
									<?php
									
									if ($precio == 0) { /* Inicializa el valor de precio*/
										$precio = ($lang == "es")?$rate_room[$l]['Total']['!AmountAfterTax']:$rate_room[$l]['Total']['!AmountBeforeTax'];
									} 
									else {
										if ($precio > $rate_room[$l]['Total']['!AmountAfterTax']) /* Valida que sea el precio menor*/
											$precio = $rate_room[$l]['Total']['!AmountAfterTax'];
									}
								}
							?>      
			            <div class="col-xs-12 col-lg-12" style="padding:0px!important;"><hr/></div>
			            <div class="col-xs-12 col-lg-12" style="max-width: 200px!important; padding:0px!important;">
			                <div class="col-xs-12 col-lg-12" style="padding:0px!important;">
			              <h3 style="font-weight: 100!important;">
			                <span style="font-family: latoreg;"><?php echo $currency . " " . $precio; ?>&nbsp;</span>
			                <span style="font-family: bulterstencillight; font-size: 14px;">/<?php echo $noche; ?></span>
			              </h3>
			              </div>
			            </div>
			          </div>
			          <!-- 
			          <div style="width: 100%; height: 50px; display: block;"></div>
			          <div><ul><li><a href="" class="cp_id_1c036" style="color:#3C3C3B;"><?php echo $compare; ?></a></li></ul></div> 
			          <div style="width: 100%; height: 50px; display: block;"></div>-->
			          <div>
			            <a id="room<?php echo $roomTypeCode?>" href="https://reservations.travelclick.com/<?php echo $hotelCode ?>?themeid=<?php echo $theme ?>&amp;datein=<?php echo date_format(date_create($startDate), "m/d/Y");?>&amp;dateout=<?php echo date_format(date_create($endDate), "m/d/Y");?>&amp;roomtypeid=<?php echo $roomTypeCode; ?>&amp;adults=<?php echo $adults; ?>&amp;children=<?php echo $children; ?>&amp;rooms=<?php echo $rooms ?>&amp;currency=<?php echo $currency?>" 
			            target="_blank" class="btn-group btn btn-primary"
			            data-hover="" data-border-color="" data-bg="#c69907" data-hover-bg="" data-border-hover="" data-shadow-hover=""
			            data-shadow-click="none" data-shadow="" data-shd-shadow="" data-ultimate-target="#ubtn-7197" data-responsive-json-new="{&quot;font-size&quot;:&quot;&quot;,&quot;line-height&quot;:&quot;&quot;}"
			            style="font-weight:normal;border:none;background: #c69907;color: #ffffff;width:100%;">
			            <span class="ubtn-hover" style="background-color:"></span>
			            <span class="ubtn-data ubtn-text "><?php echo $reserva; ?></span>
			            </a>
			          </div>
			        </div>
			      </div>
			    </div>
			</div>
		<?php
		$i++;
		$precio = 0;
	} // foreach($arrayRoomType as $elementRoomType)
}