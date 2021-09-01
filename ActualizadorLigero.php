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

    foreach($registros as $keyVnvm){
       
        $sku=$registro->{'N/Ref'};
        //Este es el objeto que trae Woocommerce, por el sku. Si existe el objeto termina la ejecucion 
        $params = [
            'sku' => (string)$sku
        ];

        $getSku = $woocommerce->get('products', $params);
        
        if($getSku){
        
            $idUpdate = $getSku[0]->sku;
            echo('❗Ya existe el producto, sku = ' . $sku);
            continue;
        }else{   
            try{
                
                $price=(string)$keyVnvm->{'tarifa-9'}->precio;
                
                $data = [        

                    'name' => empty($keyVnvm->nombreAlternativo) || is_null($keyVnvm->nombreAlternativo)  ? $keyVnvm->nombre : $keyVnvm->nombreAlternativo ,

                    'regular_price' => $price,

                    'stock_quantity' => round($keyVnvm->existencias->existencias),
        
                ];   
                $resultCreate = $woocommerce->put('products/'.$KeyWoo->id, $data);
                
                if (!$resultCreate) {
                    echo ("❗Error al actualizar productos ".$keyVnvm->{'N/Ref'}." \n");
                } else {
                    // $tiempoEjecucion=microtime(true);
                    print("✔ Cantidad Actualizada correctamente \n <br>");            
                }
                $paginaDesde++;
            }catch(Exception $ex)
            {
                echo("Error capturado: " .$ex);
                continue;
                $paginaDesde++;
            }
        }
    }
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



