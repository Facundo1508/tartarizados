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

$paginaDesde=0;
$paginaHasta=0;

$paginaHasta=ContadorVnvm();
    
while($paginaDesde!=$paginaHasta){

    $url_API = '80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|'.$paginaDesde.'|||Publicable';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url_API);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $items_origin = curl_exec($ch);
    curl_close($ch);

    $getDecodedVnvm = json_decode(utf8_encode($items_origin));

    $datosClientes = (object)$getDecodedVnvm->articulos;

    $registros = $datosClientes->registros;

    foreach($registros as $registro){
       
        try{
                
            $price=(string)$registro->{'tarifa-9'}->precio;

            switch ($registro->publicable) {                
                case "N":
                    $visibilidad="0";//N
                    break;
                case "1":
                    $visibilidad="1";//B2B
                    break;
                case "2":
                    $visibilidad="2";//B2C
                    break;                
                default:
                    $visibilidad="3";//B2B y B2C
            }; 

            //um_mayorista
            $precio9=$registro->{'tarifa-9'}->precio;
            $precio2=$registro->{'tarifa-2'}->precio;
            //um_mayorista-pastelero
            $precio6=$registro->{'tarifa-6'}->precio;
            $precio3=$registro->{'tarifa-3'}->precio;


            $resulMeta= 
            array(
                'um_mayorista' =>
                    array(
                    'regular_price' => $precio9<= $precio2 ? $precio2 : $precio9,
                    'selling_price' => $precio2,
                    ),
                
                    'um_mayorista-pastelero' =>
                    array (
                    'regular_price' => $precio6 <= $precio3 ? $precio3 : $precio6,
                    'selling_price' => $precio3,
                    )
            );

            $meta[0] = [

                'key'=>'_enable_role_based_price',
                'value'=> '1'
            ];
            $meta[1] = [

                'key'=>'_role_based_price',
                'value'=> $resulMeta
            ];
            $meta[2] = [

                'key'=>'_visibilidad_publicable',
                'value'=> $visibilidad
            ];


            $data = [  

                'name' => empty($registro->nombreAlternativo) || is_null($registro->nombreAlternativo)  ? $registro->nombre : $registro->nombreAlternativo ,
                'regular_price' => $price,
                'stock_quantity' => round($registro->existencias->existencias),
                'meta_data' => $meta
            
            ];   

            $sku=$registro->{'N/Ref'};
            //OBJETO DE PRODUCTOS EN WOOCOMERCE 
            $params = [
                'sku' => (string)$sku
            ];
            $getWooProducts = $woocommerce->get('products', $params);    

            print_r($getWooProducts);
            die;
            $resultCreate = $woocommerce->put('products/'.$KeyWoo->id, $data);
             
            if (!$resultCreate) {
                 echo ("❗Error al actualizar productos ".$registro->{'N/Ref'}." \n");
            } else {
                 // $tiempoEjecucion=microtime(true);
                 print("✔ Cantidad Actualizada correctamente \n <br>");            
            }
            

        }catch(Exception $ex)
        {
            echo("Error capturado: " .$ex);           
        }
       
    }
        $paginaDesde++;
}

function ContadorVnvm(){

    $url_API = '80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|1|1|||Publicable';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url_API);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $items_origin = curl_exec($ch);
    curl_close($ch);

    
    $getDecodedVnvm = json_decode(utf8_encode($items_origin));
    
    $datosClientes = (object)$getDecodedVnvm->articulos;

    $paginaHasta= $datosClientes->totalRegistros;
    return $paginaHasta;
}



