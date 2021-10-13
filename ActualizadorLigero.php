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
// $url_API_woo = 'https://pruebas.tartarizados.com/';
// $ck_API_woo = 'ck_41fcb94f0f50e0e1e8f67af0b649c387b62a5417';
// $cs_API_woo = 'cs_96648b4e8944fea3016c07a2c7b110965edb1d94';
//PRODUCCION
$url_API_woo = 'https://tartarizados.com/';
$ck_API_woo = 'ck_7136b22f816dc374f4955631b762fb33db03ef8b';
$cs_API_woo ='cs_c71bded97e67e40719225d5992d6ac4570ce7294';
 
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
 
$paginaDesde=1;
$paginaHasta=0;
 
$paginaHasta=ContadorVnvm();
$arrayErrores=array();   

while($paginaDesde<=$paginaHasta){
 
    $url_API = 'http://81.45.33.23/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|'.$paginaDesde.'||1|Publicable';
 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url_API);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $items_origin = curl_exec($ch);
    curl_close($ch);
    $items_origin = str_replace(array("\n", "\r"), '', $items_origin);
    //$getDecodedVnvm = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin);
    //$items_origin = str_replace('"formatoVenta":{"id":0,"clave":"","nombre":""},','',$items_origin);
	$getDecodedVnvm = json_decode(utf8_encode($items_origin));
   
    ob_flush();
    flush();
 
    
    echo $url_API;
    switch(json_last_error())
    {
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
            echo "<pre>" . utf8_encode(strip_tags($items_origin)) . "</pre>";           
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Invalid or malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
    }
    
 
    $datosClientes = (object)$getDecodedVnvm->articulos;
 
    $registros = $datosClientes->registros;
 
    foreach($registros as $registro){
 
        try{
 
            $price=(string)$registro->{'tarifa-9'}->precio;
            $sale_price=$registro->{'tarifa-8'}->precio <= 0 ? $registro->{'tarifa-9'}->precio: $registro->{'tarifa-8'}->precio;
 
            switch ($registro->publicable) {                
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
 
            if($registros[0]->publicable==='3'){//B2B y B2C
 
                $visibilidad='visible';
 
            }elseif($registros[0]->publicable==='N'){
 
                $visibilidad='hidden';
            }else{
                $visibilidad='search';
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
                'value'=> $visibilidad_publicable
            ];
            $stock_status=round($registro->existencias->existencias)>=1 ? 'instock' : 'outofstock';
            $nameProd= empty($registro->nombre ) || is_null($registro->nombre )  ? $registro->nombreAlternativo: $registro->nombre ;
 
            $concepto=empty($registro->concepto) || is_null($registro->concepto) ?"Sin Concepto": $registro->concepto ;
            $anchoDiametro= $registro->ancho=== 0 || empty($registro->ancho) ? $registro->diametro : $registro->ancho;
            $altura= $registro->alto;
            $unidadesCaja=$registro->unidadesCaja;
            $formatoVentaNombre= $registro->formatoVenta->nombre;
 
            $data = [  
 
                'name' => $nameProd ,
                'catalog_visibility'=>'visible',
                'regular_price' => (string)$price,
                'sale_price'=>(string)$sale_price,
                'short_description' =>'<div class="concepto_prod">
                <div class="span_concepto">'.$concepto.'</div>
                <div class="sku-prod">Ref: '
                    .$registro->{'N/Ref'}.'</div>
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
                'manage_stock'=>'true',
                'backorders_allow'=>'false',
                'backorders'=>'no',
                'stock_quantity' => round($registro->existencias->existencias),
                'stock_status' => $stock_status,
                'meta_data' => $meta
            ];   
            
             //dimensiones
             $dimensiones=array();

             if($registro-> largo>0){
                 $largo = (string)$registro-> largo;//profundidad #capacidad# ????
                 $dimensiones['length'] = $largo;
             }
 
             if($registro-> ancho>0){
                 $ancho = (string)$registro-> ancho;//ancho
                 $dimensiones['width'] = $ancho;
             }
             if($registro-> alto>0){
 
                 $alto = (string)$registro-> alto;//alto
                 $dimensiones['height'] = $alto;
             }
 
             $data['dimensions']=$dimensiones;
 
             if($registro-> capacidad>0){
                 $peso = (string) $registro->capacidad;//capacidad   
                 $data['weight'] = $peso;    
             }
             
            $sku=$registro->{'N/Ref'};
            //OBJETO DE PRODUCTOS EN WOOCOMERCE 
            $params = [
                'sku' => (string)$sku              
            ];
            $getWooProducts = $woocommerce->get('products', $params);    
 
            if(is_null($getWooProducts) || empty($getWooProducts)){
 
                echo ("❗Error al actualizar producto, no se encontro la referencia: ".$registro->{'N/Ref'}." \n");
                continue;
 
            }else{          
 
                $resultCreate = $woocommerce->put('products/'.$getWooProducts[0]->id, $data);
 
                if (!$resultCreate) {
                    echo ("❗Error al actualizar producto: ".$registro->{'N/Ref'}."\n <br>");                                    
 
                } else {
                    // $tiempoEjecucion=microtime(true);
                    print("✔ producto Actualizado correctamente".$registro->{'N/Ref'}." \n <br>");            
                }
            }  
            
            switch(json_last_error())
            {
                case JSON_ERROR_DEPTH:
                    echo ' - Maximum stack depth exceeded';
                break;
                case JSON_ERROR_CTRL_CHAR:
                    echo ' - Unexpected control character found';
                    echo "<pre>" . utf8_encode(strip_tags($items_origin)) . "</pre>";           
                break;
                case JSON_ERROR_SYNTAX:
                    echo ' - Syntax error, malformed JSON';
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo ' - Invalid or malformed JSON';
                break;
                case JSON_ERROR_UTF8:
                    echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            }
        }catch(Exception $ex)
        {
            echo("Error capturado: " .$ex);   
            $arrayErrores[]=$registro->{'N/Ref'};    
        }
 
    }
    
    $paginaDesde++;    
    /*
    if($paginaDesde == 100){
        echo "FIN";
        die();
    }
    */
}
 
mail('ivan.popconsulting@gmail.com', 'Error Actualizador', '<pre>'.print_r($arrayErrores, true).'</pre>');
 
function ContadorVnvm(){
 
    $url_API = '81.45.33.23/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|1|1|||Publicable';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url_API);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $items_origin = curl_exec($ch);
    curl_close($ch);
 
 
    $getDecodedVnvm = json_decode(utf8_encode($items_origin));
 
    $datosClientes = (object)$getDecodedVnvm->articulos;
 
    $paginaHasta= ($datosClientes->totalRegistros/100)+1;
 
    return intval($paginaHasta);
}

