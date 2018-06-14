<?php 
	$destino = "info@granhotelcdmex.com.mx"; 
	$asunto = "Cliente Interesado en un Evento (aw)";
	$nombre = $_POST['nombre'];
	$from = $_POST['correo'];
	$telefono = $_POST['telefono'];
	$mensaje = $_POST['mensaje'];
	$destino2 = $nombre."(".$from.")";
	$comentario = "Datos del Cliente Interesado en un Evento"."\n\n".
	"Nombre: ".$nombre."\n"."Correo: ".$from."\n"."Teléfono: ".$telefono."\n\n\n".$mensaje;

	$headers = 'From: '.$destino2."\r\n".
			   'Reply-To:'.$destino2."\r\n".
			   'Cc: websalesmanager@grupoahc.mx'."\r\n".
			   'Bcc: digitalsalesmanager@grupoahc.mx'."\r\n".
			   'X-Mailer: PHP/'.phpversion();

	mail($destino, utf8_decode($asunto), utf8_decode($comentario),$headers);
	header( "Location: Gracias_Banquetes.html" );
?>