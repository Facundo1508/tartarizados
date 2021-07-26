<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Vnvm a WooCommerce</title>
</head>
<body>
<?php
$tiempoEjecucionStart=microtime(true);

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
set_time_limit (0);
// Conexión WooCommerce API destino
// ================================
//PRUEBAS
$url_API_woo = 'https://pruebas.tartarizados.com/';
$ck_API_woo = 'ck_41fcb94f0f50e0e1e8f67af0b649c387b62a5417';
$cs_API_woo = 'cs_96648b4e8944fea3016c07a2c7b110965edb1d94';
//PRODUCCION
// $url_API_woo = 'https://tartarizados.com/';
// $ck_API_woo = 'ck_7136b22f816dc374f4955631b762fb33db03ef8b';
// $cs_API_woo ='cs_c71bded97e67e40719225d5992d6ac4570ce7294';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    [
        'version' => 'wc/v3',
        'query_string_auth' => true,
        'verify_ssl' => false
    ]
);
// Conexión API VNVM. Esto tenemos que postear 
// ===========================================
//OBJETO DE PRODUCTOS EN WOOCOMERCE 

$url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|9999||||Publicable||||";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url_API);
curl_setopt($ch, CURLOPT_HEADER, 0);

echo "➜ Obteniendo datos origen Vnvm ... \n";

$items_origin = curl_exec($ch);

curl_close($ch);


if (!$items_origin) {
    exit('❗Error en API origen');
}

//$getDecodedVnvm = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin));
$getDecodedVnvm = json_decode(utf8_decode($items_origin));
//probar este decodificador
//utf8_decode()
//Este es el Objeto que trae Vnvm, sacamos el Id para mandarlo al insert como sku , y para comparar que no haya otro igual 
// if(is_null($getDecodedVnvm->articulos)){

//     echo "➜ no se encontro articulos ... \n";
//     echo $idVnvm;
//     exit;

// }
$datosClientes = (object)$getDecodedVnvm->articulos;

$registros = $datosClientes->registros;

//OBJETO DE PRODUCTOS EN WOOCOMERCE 
$getWooProducts = $woocommerce->get('products');
echo count($getWooProducts);

foreach ($getWooProducts as $KeyWoo) {
            
    foreach($registros as $keyVnvm){

       
        if($keyVnvm->{'N/Ref'}===$KeyWoo->sku){

        $cantVnvm=round($keyVnvm->existencias->existencias);
        $cantWoo=$KeyWoo->stock_quantity;

        //if($cantVnvm !== $cantWoo){
            echo("Producto encontrado");
            try{

            print_r("La N referencia ".$keyVnvm->{'N/Ref'}."\n");
            
            // print_r("Existencias en Woo: ".$KeyWoo->stock_quantity . "\n<br><br>");
            print_r("Existencias en Woo: ".$keyVnvm[$int]->{'tarifa-9'}->precio . "\n<br><br>");
           
            // $concepto=empty($keyVnvm->concepto) || is_null($keyVnvm->concepto) ?"Sin Concepto": $keyVnvm->concepto ;
            // $anchoDiametro= $keyVnvm->ancho=== 0 || empty($keyVnvm->ancho) ? $keyVnvm->diametro : $keyVnvm->ancho;
            // $altura= $keyVnvm->alto;
            // $unidadesCaja=$keyVnvm->unidadesCaja;
            // $formatoVentaNombre= $keyVnvm->formatoVenta->nombre;
                $price=(string)$keyVnvm[$int]->{'tarifa-9'}->precio;
            $data = [        
                'regular_price' => $price
                // 'short_description' => '<div class="concepto_prod"> '.$concepto.'
                //                         <i class="fas fa-arrows-alt-h" aria-hidden="true"></i> '.$anchoDiametro.' mm  <i class="fas fa-arrows-alt-v" aria-hidden="true"></i> '.$altura.' mm
                //                         <i class="fas fa-box" aria-hidden="true"></i> Caja '.$unidadesCaja.' '.$formatoVentaNombre.'
                //                         Ref: ' .$keyVnvm->{'N/Ref'}.'
                //                         <div></div>
                //                         </div>',

                // 'stock_quantity' => round($keyVnvm->existencias->existencias),
                // 'images' => $imagenes
                 
            ];           
            
            $resultCreate = $woocommerce->put('products/'.$KeyWoo->id, $data);
            
            if (!$resultCreate) {
                echo ("❗Error al actualizar productos ".$keyVnvm->{'N/Ref'}." \n");
            } else {
                // $tiempoEjecucion=microtime(true);
                print("✔ Cantidad Actualizada correctamente \n <br>");            
            }
            }catch(Exception $ex)
            {
                echo("Error capturado: " .$ex);
            }
        //}  


      }
    
    }

}
$tiempoEjecucionEnd=microtime(true);
echo(" Obtenidas en :".round($tiempoEjecucionEnd - $tiempoEjecucionStart,2)." segundos");       
?>


</body>
</html>