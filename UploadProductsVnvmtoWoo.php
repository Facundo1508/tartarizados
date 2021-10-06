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

$ListNref=$_POST['id'];

$ListNrefObj= explode(",",$ListNref);

foreach($ListNrefObj as $idVnvm){
    
    try{

        $url_API = "http://81.45.33.23/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|500|1|1|1|Publicable|||".trim($idVnvm)."|".trim($idVnvm);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        echo "➜ Obteniendo datos origen Vnvm ... \n";
        
        $items_origin = curl_exec($ch);
        
        curl_close($ch);
        $items_origin = str_replace(array("\n", "\r"), '', $items_origin);
        
        if (!$items_origin) {

            echo('❗❗❗❗Error en API origen');
            continue;
            
        }              

	    $getDecodedVnvm = json_decode(utf8_encode($items_origin));
        
        if(is_null($getDecodedVnvm) || empty($getDecodedVnvm)){

            echo "➜ no se encontro el articulo ... \n";
            echo $idVnvm;
            continue;

        }

        $datosClientes = (object)$getDecodedVnvm->articulos;
       
        $registros = $datosClientes->registros;
        
        foreach($registros as $registro){
           
            $imagenes= array();
            $count=0;
            foreach($registro->imagenes as $imgVnvm ){

                 $imagenes[$count] = [                         
                        'src' => (string)'http://81.45.33.23/cgi-vel/vnvm/'.$imgVnvm->visd,
                        'alt' => empty($registro->nombreAlternativo) || is_null($registro->nombreAlternativo)  ? $registro->nombre : $registro->nombreAlternativo
                    ];                                   
                    
                $count++;
            }

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
            //um_mayorista
            $precio9=$registro->{'tarifa-9'}->precio;
            $precio2=$registro->{'tarifa-2'}->precio;
            //um_mayorista-pastelero
            $precio6=$registro->{'tarifa-6'}->precio;
            $precio3=$registro->{'tarifa-3'}->precio;
            
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

            $nameProd= empty($registro->nombreAlternativo ) || is_null($registro->nombreAlternativo )  ? $registro->nombre: $registro->nombreAlternativo ;

            $concepto=empty($registro->concepto) || is_null($registro->concepto) ?"Sin Concepto": $registro->concepto ;
            $anchoDiametro= $registro->ancho=== 0 || empty($registro->ancho) ? $registro->diametro : $registro->ancho;
            $altura= $registro->alto;
            $unidadesCaja=$registro->unidadesCaja;
            $formatoVentaNombre= $registro->formatoVenta->nombre;

            if($registro->porcentajeIvaVenta==="0"){

                $regular_price =$registro->{'tarifa-9'}->precio;
                $sale_price=$registro->{'tarifa-8'}->precio <= 0 ? $registro->{'tarifa-9'}->precio: $registro->{'tarifa-8'}->precio;

            }else{
                $valorIva = '1.'.$registro->porcentajeIvaVenta;
            
                $calculoIVA=doubleval($valorIva);
    
                //selprice se llenara con la tarifa 8 de existir si no es asi sigue usando la 9
                $precioOfertaSinIVA=$registro->{'tarifa-8'}->precio <= 0 ? $registro->{'tarifa-9'}->precio: $registro->{'tarifa-8'}->precio;
                $precioSinIVA =$registro->{'tarifa-9'}->precio;

                $regular_price=$precioSinIVA*$calculoIVA;
                $sale_price=$precioOfertaSinIVA*$calculoIVA;
            }
            $stock_status=round($registro->existencias->existencias)>=1 ? 'instock' : 'outofstock';
            
            $data = [

                'name'=>$nameProd,
                'regular_price'=>(string)$regular_price,
                'sale_price'=>(string)$sale_price,
                'catalog_visibility'=>'visible',
                'manage_stock'=>'true',
                'backorders_allow'=>'false',
                'backorders'=>'no',
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
                'stock_quantity' =>round($registro->existencias->existencias),
                'stock_status' => $stock_status,
                'images' => $imagenes,
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
            
            $resultCreate = $woocommerce->put('products/'.$getWooProducts[0]->id, $data);
            
            if (!$resultCreate) {
                echo ("❗Error al actualizar productos ".$registro->{'N/Ref'}." \n");
            } else {
                $tiempoEjecucion=microtime(true);
                print("✔ Producto ". $registro->{'N/Ref'}." actualizado correctamente \n <br>");            
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