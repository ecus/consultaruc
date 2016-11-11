<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';

include 'html_dom.php';


$tipo = $_REQUEST['tipo'];
$doc  = $_REQUEST['doc'];
$url  = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image';

switch ($tipo) {
    case 1:
        # RUC
        $url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRuc&nroRuc='.$doc.'&codigo=';
        break;
    case 2:
        # DNI
        $url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorTipdoc&nrodoc='.$doc.'&codigo=';
        break;
    case 3:
        # C.E.
        $url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorTipdoc&nrodoc='.$doc.'&codigo=';
        break;
    case 4:
        # PASAPORTE
        $url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorTipdoc&nrodoc='.$doc.'&codigo=';
        break;
    case 5:
        $url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorTipdoc&nrodoc='.$doc.'&codigo=';
        # DIPLOMATICO
        break;
    case 6:
        $url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRazonSoc&razSoc='.$doc.'&codigo=';
        # RAZON
        break;
    default:
        echo "error";
        break;
}
// echo $url2;
// echo $url2;
echo (new TesseractOCR('captcha.png'))
    ->run();
    $image_file = "captcha.jpg";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
    $data = curl_exec($ch);
    //split the header and body of request
    $matches = preg_split('/^\s*$/im', $data);
    $header = $matches[0];
    //extract cookie from header
    preg_match_all('/Set-Cookie: (.*?)\s+/i', $header, $gCookie, PREG_PATTERN_ORDER);
    // if (isset($gCookie)) {
    	# code...
	    $gCookie = $gCookie[1][0];
    // }
    // echo $gCookie;

    //The body is the image, we cleanup the header/body line break and save it
    $body = $matches[1] ;
    $body = implode("\n", array_slice(explode("\n", $body), 1));
    file_put_contents($image_file, $body);

    curl_close($ch);

$tesseract = new TesseractOCR(__DIR__ . '/captcha.jpg');
// Perform OCR on the uploaded image
$captcha = $tesseract->run();
// echo "<br />";
// var_dump($captcha);
// echo "<br />";
if (strlen($captcha) < 4){
	die (json_encode('Lo siento no pude procesar el captcha, intenta nuevamente.'));
}

switch ($tipo) {
    case 1:
        # RUC
        $url2 = $url2.$captcha.'&tipdoc=1';
        break;
    case 2:
        # DNI
        $url2 = $url2.$captcha.'&tipdoc=1';
        break;
    case 3:
        # C.E.
        $carneext = $url2.$captcha.'&tipdoc=4';
        break;
    case 4:
        # PASAPORTE
        $passport = $url2.$captcha.'&tipdoc=7';
        break;
    case 5:
        # DIPLOMATICO
        $diplo = $url2.$captcha.'&tipdoc=A';
        break;
    case 6:
        # RAZON
        $url2 = $url2.$captcha.'&tipdoc=1';
        break;
    default:
        echo "error";
        break;
}

// echo $url2;
$ch = curl_init ($url2);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: '.$gCookie));
    curl_setopt($ch, CURLOPT_VERBOSE, true);
$raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists(__DIR__ .'/dataruc.txt')){
        unlink(__DIR__ .'/dataruc.txt');
    }
    $fp = fopen(__DIR__ .'/dataruc.txt','w+');
    fwrite($fp, $raw);
    fclose($fp);

$html   = file_get_html(__DIR__ .'/dataruc.txt');

$titles = $html->find('td.bgn');
$cells  = $html->find('td.bg');
$i      = 0;
$flag   = false;

if (count($cells) == 0){
	die (json_encode('Lo siento no pude extraer la data, intenta nuevamente.'));
}

$info = array();
foreach($cells as $cell) {
	// N&uacute;mero de RUC:
	// Tipo de Documento:
	// Nombre Comercial:
	// Direcci&oacute;n del Domicilio Fiscal:
	$title = $titles[$i]->plaintext;
	// var_dump($title);
	if ($i == 0) {
		$var = explode( ' - ', $cell->plaintext );
		$info['ruc'] = $var[0];
		$info['razon'] = $var[1];

	}
	// echo $title;
	if ( strcmp($title ,"Direcci&oacute;n del Domicilio Fiscal:") == 0 && !$flag) {
		$var2 = explode( ' - ', $cells[$i-1]->plaintext );
		$info['dist'] = trim($var2[2]);
		$info['prov'] = trim($var2[1]);
		$aux = trim($var2[0]);
		$var3 = explode(" ",$aux);
		$pos = count($var3) -1;
		$info['dep'] = trim($var3[$pos]);
		$var3[$pos] = '';
		$dir = join(" ", $var3);
		$info['direc'] = trim($dir);
		$flag = true;
	}
	// echo $title." - ".$cells[$i]->plaintext."<br>";
	if (strcmp(trim($title) , "Estado del Contribuyente:") == 0) {
	// echo $title." - ".$cells[$i]->plaintext."<br>";
		$info['estado'] = trim($cell->plaintext);
	}

	// echo $titles[$i]->plaintext.'  ';
    // echo $cell->plaintext. '<br>';
	$i = $i +1;
}
echo json_encode($info);
/*
Número de RUC:
10457338568 - URRUTIA SANTAMARIA ERIK CHRISTOPER
Tipo Contribuyente:
PERSONA NATURAL SIN NEGOCIO
Tipo de Documento:
DNI 45733856 - URRUTIA SANTAMARIA, ERIK CHRISTOPER
Nombre Comercial:
-
Fecha de Inscripción:
07/10/2009
Fecha de Inicio de Actividades:
01/10/2009
Estado del Contribuyente:
ACTIVO

Condición del Contribuyente:
HABIDO
Dirección del Domicilio Fiscal:
CAL.JUAN VIZCARDO NRO. 178 P.J. MURO Y DIEGO FERRE LAMBAYEQUE - CHICLAYO - CHICLAYO
Sistema de Emisión de Comprobante:
MANUAL
Actividad de Comercio Exterior:
SIN ACTIVIDAD
Sistema de Contabilidad:
MANUAL
Profesión u Oficio:
PROFESION U OCUPACION NO ESPECIFICADA
Actividad(es) Económica(s):
93098 - OTRAS ACTIVID.DE TIPO SERVICIO NCP
Comprobantes de Pago c/aut. de impresión (F. 806 u 816):
RECIBO POR HONORARIOS
Sistema de Emision Electronica:
RECIBOS POR HONORARIOS AFILIADO DESDE 29/11/2014
Emisor electrónico desde:
29/11/2014
Comprobantes Electrónicos:
RECIBO POR HONORARIO (desde 29/11/2014)
Afiliado al PLE desde:
-
*/