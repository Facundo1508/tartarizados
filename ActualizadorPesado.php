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

    $url_API = '81.45.33.23/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|'.$paginaDesde.'|||Publicable';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url_API);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $items_origin = curl_exec($ch);
    curl_close($ch);

    $getDecodedVnvm = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin);
	$getDecodedVnvm = json_decode(utf8_encode($getDecodedVnvm));

    $datosClientes = (object)$getDecodedVnvm->articulos;

    $registros = $datosClientes->registros;

    foreach($registros as $registro){

        try{

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

            }elseif($registro->publicable==='N'){

                echo('❗El producto esta configurado como publicable N');
                continue;

            }else 
            {
            
                $imgVisd = $registro->imagenes;
            
                if(empty($imgVisd) || is_null($imgVisd)){

                    $images[] = [ 

                        'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
                    ];              

                }else{

                    foreach ($imgVisd as $key => $value) {

                        $images[$key] = [ 

                            'src' => (string)'http://81.45.33.23/cgi-vel/vnvm/'.$value->visd,
                            'alt' => empty($registro->nombreAlternativo) || is_null($registro->nombreAlternativo)  ? $registro->nombre : $registro->nombreAlternativo
                        ];
                    }
                };  
            
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
            
                $catFamilia = $registro->familia;
                $familia = [];    foreach ($catFamilia as $key1 => $value1) {

                    $familia[$key1] = $value1;         
                
                }
            
                $concepto=empty($registro->concepto) || is_null($registro->concepto) ?"Sin Concepto": $registro->concepto ;
                $anchoDiametro= $registro->ancho=== 0 || empty($registro->ancho) ? $registro->diametro : $registro->ancho;
                $altura= $registro->alto;
                $unidadesCaja=$registro->unidadesCaja;
                $formatoVentaNombre= $registro->formatoVenta->nombre;
                if($registro->publicable==='3'){
                
                    $visibilidad='visible';
                
                }elseif($registro->publicable==='N'){
                
                    $visibilidad='hidden';
                }else{
                    $visibilidad='search';
                };
            
                $regular_price=$registro->{'tarifa-9'}->precio;
                $sale_price=$registro->{'tarifa-8'}->precio <= 0 ? $registro->{'tarifa-9'}->precio: $registro->{'tarifa-8'}->precio;
                $stock_status=round($registro->existencias->existencias)>=1 ? 'instock' : 'outofstock';
                $nameProd= empty($registro->nombre ) || is_null($registro->nombre )  ? $registro->nombreAlternativo: $registro->nombre ;

                $data = [        
                
                    'name' => $nameProd,
                    //Options: simple, grouped, external and variable. Default is simple. SOLO TIENE ESTOS TIPOS 
                    'type' => 'simple',
                    'regular_price' => (string)$regular_price,
                    'sale_price'=>(string)$sale_price,  
                    'manage_stock'=>'true',
                    'backorders_allow'=>'false',     
                    'backorders'=>'no', 
                    'short_description' =>'<div class="concepto_prod">
                            <div class="span_concepto">'.$concepto.'</div>
                            <div class="sku-prod">Ref: '
                                .(string)$sku.'</div>
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
                    'backorders'=>'yes',
                    'sku' => (string)$sku,
                    'dimensions' => [
                    
                        'length' => (string)$registro-> largo,
                        'width' => (string)$registro-> ancho,
                        'height' => (string)$registro-> alto 
                    ],

                    'stock_quantity' => round($registro->existencias->existencias),
                    //stock_status Options: instock, outofstock, onbackorder. Default is instock.
                    //aqui se podria solucionar mirando el stock de vnvm y eligiendo la opcion correcta 
                    'stock_status' => $stock_status,
                    //Catalog visibility. Options: visible, catalog, search and hidden. Default is visible.
                    'catalog_visibility' => $visibilidad,

                    'images' => $images,
                    'meta_data' => $meta
                
                ];
            
                $resultCreate = $woocommerce->post('products',  $data);
            
                if (!$resultCreate) {
                    echo ("❗Error al actualizar producto: ".$registro->{'N/Ref'}."\n <br>");
                } else {
                    // $tiempoEjecucion=microtime(true);
                    print("✔ producto Creado correctamente".$registro->{'N/Ref'}." \n <br>");            
                }
            }
        }catch(Exception $ex)
        {
            echo("Error capturado: " .$ex);           
        }        
    }
    $paginaDesde++;
}

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

    $paginaHasta= $datosClientes->totalRegistros;
    return $paginaHasta;
}



