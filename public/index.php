<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';

$ruc = $_REQUEST['ruc'];

$url = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image';

$url2 = 'http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRuc&nroRuc='.$ruc.'&codigo=';
$ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
shell_exec ( 'curl --request POST --data-binary "@captcha.jpg" http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image > captcha.jpg; tesseract captcha.jpg out; var=$( cat foo.txt );curl http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRuc&nroRuc='.$ruc.'&codigo=$var' );
//

/*curl -d "accion=image" --dump-header headers http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias
curl -b -o headers http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias



curl --request POST --data-binary "@captcha.jpg" http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image > captcha.jpg; tesseract captcha.jpg out -l eng

curl -i --data "accion=image" http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias*/


curl --request POST --data-binary "@captcha.jpg" http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image > captcha.jpg; tesseract captcha.jpg out; var=$( cat foo.txt );

    echo $ch;
$raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/ruc/public/captcha.jpg')){
        unlink($_SERVER['DOCUMENT_ROOT'].'/ruc/public/captcha.jpg');
    }
    $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/ruc/public/captcha.jpg','w+');
    fwrite($fp, $raw);
    fclose($fp);

$tesseract = new TesseractOCR(__DIR__ . '/captcha.jpg');

// Perform OCR on the uploaded image
$captcha = $tesseract->run();

$url2 = $url2.$captcha.'&tipdoc=1';
echo $url2;
$ch = curl_init ($url2);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
$raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists(__DIR__ .'/dataruc.txt')){
        unlink(__DIR__ .'/dataruc.txt');
    }
    $fp = fopen(__DIR__ .'/dataruc.txt','w+');
    fwrite($fp, $raw);
    fclose($fp);
