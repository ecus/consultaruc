#!/bin/bash
curl --request POST --data-binary "@captcha.jpg" http://www.sunat.gob.pe/cl-ti-itmrconsruc/captcha?accion=image > captcha.jpg
tesseract captcha.jpg out
ecus=`cat out.txt`
url='http://www.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias?accion=consPorRuc&nroRuc='
url2=$url$1
url3=$url2"&codigo="
url4=$url3$ecus
echo $url4 > url.txt
curl $url4 > ecus.txt
curl $url4 > ecus.txt