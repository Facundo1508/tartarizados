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
set_time_limit ( 0 );
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

$ListNref=$_POST['id'];

$ListNrefObj= explode(",",$ListNref);

foreach($ListNrefObj as $idVnvm){
   
    try{

        $url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|1|1|1|Publicable|.|.|".$idVnvm."|".$idVnvm;

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
        
        $getDecodedVnvm = json_decode(utf8_encode($items_origin));
        
        $datosClientes = (object)$getDecodedVnvm->articulos;

        $registros = $datosClientes->registros;
        
        foreach($registros as $registros){
            $imagenes= array();
            $count=0;
            foreach($registros->imagenes as $imgVnvm ){
    
                $imagenes[$count] = [ 
    
                    'src' => (string)'http://80.35.251.17/cgi-vel/pruebas/'.$imgVnvm->visd,
                ];         
    
                $count++;
            }
            
            $concepto=empty($registros->concepto) || is_null($registros->concepto) ?"Sin Concepto": $registros->concepto ;
            $anchoDiametro= $registros->ancho=== 0 || empty($registros->ancho) ? $registros->diametro : $registros->ancho;
            $altura= $registros->alto;
            $unidadesCaja=$registros->unidadesCaja;
            $formatoVentaNombre= $registros->formatoVenta->nombre;
            $data = [        
                'short_description' => '<div class="concepto_prod"> '.$concepto.'
                                        <i class="fas fa-arrows-alt-h" aria-hidden="true"></i> '.$anchoDiametro.' mm  <i class="fas fa-arrows-alt-v" aria-hidden="true"></i> '.$altura.' mm
                                        <i class="fas fa-box" aria-hidden="true"></i> Caja '.$unidadesCaja.' '.$formatoVentaNombre.'
                                        Ref: ' .$registros->{'N/Ref'}.'
                                        <div></div>
                                        </div>',
                'stock_quantity' => round($registros->existencias->existencias),
                'images' => $imagenes
                 
            ];           
            $sku=$registros->{'N/Ref'};
            //OBJETO DE PRODUCTOS EN WOOCOMERCE 
            $params = [
                'sku' => (string)$sku
            ];
            $getWooProducts = $woocommerce->get('products', $params);      
    
            $resultCreate = $woocommerce->put('products/'.$getWooProducts[0]->id, $data);
            
            if (!$resultCreate) {
                echo ("❗Error al actualizar productos ".$registros->{'N/Ref'}." \n");
            } else {
                $tiempoEjecucion=microtime(true);
                print("✔ Producto ". $registros->{'N/Ref'}." actualizado correctamente \n <br>");            
            }
    
        }
        
    }
    catch(Exception $ex){
        echo($ex);
        continue;
    }

}
?>

</body>
</html>