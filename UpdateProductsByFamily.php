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
print_r(ListadoActualizar());
die;
//$ListNref=$_POST['familia'];


 
$ListNrefObj= explode(",",$ListNref);
 
foreach($ListNrefObj as $idVnvm){
 
    try{
 
        $url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|500|1|1|1|Publicable|||||JR0103|JR0103|";
	    //echo $url_API;
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
	//var_dump($getDecodedVnvm);
 
        if(is_null($getDecodedVnvm->articulos) || empty($getDecodedVnvm->articulos)){
 
            echo "➜ no se encontro el articulo ... \n";
            echo $idVnvm;
            continue;
 
        }
 
        $datosClientes = (object)$getDecodedVnvm->articulos;
 
        $registros = $datosClientes->registros;
 
        foreach($registros as $registros){
 
            $imagenes= array();
            $count=0;
            foreach($registros->imagenes as $imgVnvm ){
 
                 $imagenes[$count] = [                         
                        'src' => (string)'http://80.35.251.17/cgi-vel/vnvm/'.$imgVnvm->visd,
                        'alt' => empty($registros->nombreAlternativo) || is_null($registros->nombreAlternativo)  ? $registros->nombre : $registros->nombreAlternativo
                    ];                                   
 
                $count++;
            }
 
            switch ($registros->publicable) {                
                case "N":
                    $visibilidad_publicable="0";//N
                    break;
                case "1":
                    $visibilidad_publicable="1";//B2B
                    break;
                case "2":
                    $visibilidad_publicable="2";//B2C
                    break;                
                default:
                    $visibilidad_publicable="3";//B2B y B2C
            }; 
            //um_mayorista
            $precio9=$registros->{'tarifa-9'}->precio;
            $precio2=$registros->{'tarifa-2'}->precio;
            //um_mayorista-pastelero
            $precio6=$registros->{'tarifa-6'}->precio;
            $precio3=$registros->{'tarifa-3'}->precio;
 
            $resulMeta=array(
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
                'value'=> $visibilidad_publicable
            ];
 
            $nameProd= empty($registros->nombreAlternativo) || is_null($registros->nombreAlternativo)  ? $registros->nombre : $registros->nombreAlternativo;
 
            $concepto=empty($registros->concepto) || is_null($registros->concepto) ?"Sin Concepto": $registros->concepto ;
            $anchoDiametro= $registros->ancho=== 0 || empty($registros->ancho) ? $registros->diametro : $registros->ancho;
            $altura= $registros->alto;
            $unidadesCaja=$registros->unidadesCaja;
            $formatoVentaNombre= $registros->formatoVenta->nombre;
 
            $regular_price=$registros->{'tarifa-9'}->precio;
            //selprice se llenara con la tarifa 8 de existir si no es asi sigue usando la 9
            $sale_price=$registros->{'tarifa-8'}->precio <= 0 ? $registros->{'tarifa-9'}->precio: $registros->{'tarifa-8'}->precio;
            $data = [
 
                'name'=>$nameProd,
                'regular_price'=>(string)$regular_price,
                'sale_price'=>(string)$sale_price,
                'short_description' =>'<div class="concepto_prod">
                <div class="span_concepto">'.$concepto.'</div>
                <div class="sku-prod">Ref: '
                    .$registros->{'N/Ref'}.'</div>
                <div class="div_icons">
                <i class="fas fa-arrows-alt-h" aria-hidden="true"></i>
                '.$anchoDiametro.'mm
                <i class="fas fa-arrows-alt-v" aria-hidden="true"></i>
                '.$altura.'mm
                </div>
                <div class="caja"><i class="fas fa-box"
                aria-hidden="true"></i> Caja '.$unidadesCaja.' '.$formatoVentaNombre.'
                </div>
                </div>',
                'stock_quantity' =>round($registros->existencias->existencias),
                'images' => $imagenes,
                'meta_data' => $meta
 
            ];        
 
 
            $sku=$registros->{'N/Ref'};
            //OBJETO DE PRODUCTOS EN WOOCOMERCE 
            $params = [
                'sku' => (string)$sku
            ];
 
            var_dump($data);
            $getWooProducts = $woocommerce->get('products', $params);      
	    if(empty($getWooProducts)){
                $data["stock_status"] = "instock";
                $data["type"] = "simple";
                $data["backorders"] = "yes";
                $data["sku"] = (string)$sku;
                $data["dimensions"] = [
 
                        'length' => (string)$registros->largo,
                        'width' => (string)$anchoDiametro,
                        'height' => (string)$altura 
                    ];
		var_dump($data);
                $resultCreate = $woocommerce->post('products',  $data);
 
 
	    }
            else{
              $resultCreate = $woocommerce->put('products/'.$getWooProducts[0]->id, $data);
	    }
 
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
 
<?php

function ListadoActualizar(){

$url_API = '80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|500|1|1|1|Publicable|||||JR0103|JR0103|';
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url_API);
curl_setopt($ch, CURLOPT_HEADER, 0);
$items_origin = curl_exec($ch);
curl_close($ch);


$getDecodedVnvm = json_decode(utf8_encode($items_origin));

$datosClientes = (object)$getDecodedVnvm->articulos;

$paginaHasta = $datosClientes->totalRegistros;

// if($paginaHasta > 500 ){

// }

$registros = $datosClientes->registros;
$count = 0;

foreach($registros as $registro){
    
    $ArrayRegistros[$count]=$registro->{'N/Ref'};
    $count++;
    
}

return $ArrayRegistros;
}
?>
</body>
</html>
