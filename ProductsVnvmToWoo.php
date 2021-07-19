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
$id = $_POST['id'];
$url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|1|1|1|1|Publicable|||".$id."|".$id;

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

//probar este decodificador
//utf8_decode()
//Este es el Objeto que trae Vnvm, sacamos el Id para mandarlo al insert como sku , y para comparar que no haya otro igual 
$datosClientes = (object)$getDecodedVnvm->articulos;

$registros = $datosClientes->registros;

if( $datosClientes->totalRegistros <= 0 ){
    echo("No se encontraron registros con el Id proporcionado.");
    exit();
}

$sku = $registros[0]->{'N/Ref'}; 

//Este es el objeto que trae Woocommerce, por el sku. Si existe el objeto termina la ejecucion 
$params = [
    'sku' => (string)$sku
];

$getSku = $woocommerce->get('products', $params);


if ($getSku) {
    
    $idUpdate = $getSku[0]->sku;
    exit('❗Ya existe el producto, sku = ' . $sku);

} else {
    
    $registros = $datosClientes->registros;
    // print_r($registros[0]);
    // die;

    $imgVisd = $registros[0]->imagenes;
    // print_r($imgVisd[0]->visd);
    // die;
    if(empty($imgVisd) || is_null($imgVisd)){
        
        $images[] = [ 
                
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg',
        ];              
                
    }else{
        
        foreach ($imgVisd as $key => $value) {
            
            $images[$key] = [ 
                
                'src' => (string)'http://80.35.251.17/cgi-vel/pruebas/'.$value->visd,
                'alt' => empty($registros[0]->nombreAlternativo) || is_null($registros[0]->nombreAlternativo)  ? $registros[0]->nombre : $registros[0]->nombreAlternativo
            ];
        }
    };    
        
    $catFamilia = $registros[0]->familia;
    $familia = [];    foreach ($catFamilia as $key1 => $value1) {
                   
        $familia[$key1] = $value1;         
      
    }

    $concepto=empty($registros[0]->concepto) || is_null($registros[0]->concepto) ?"Sin Concepto": $registros[0]->concepto ;
    $anchoDiametro= $registros[0]->ancho=== 0 || empty($registros[0]->ancho) ? $registros[0]->diametro : $registros[0]->ancho;
    $altura= $registros[0]->alto;
    $unidadesCaja=$registros[0]->unidadesCaja;
    $formatoVentaNombre= $registros[0]->formatoVenta->nombre;
    if($registros[0]->publicable==='3'){

        $visibilidad='visible';

    }elseif($registros[0]->publicable==='N'){

        $visibilidad='hidden';
    }else{
        $visibilidad='search';
    };
   


    $data = [        

        'name' => empty($registros[0]->nombreAlternativo) || is_null($registros[0]->nombreAlternativo)  ? $registros[0]->nombre : $registros[0]->nombreAlternativo ,
        //Options: simple, grouped, external and variable. Default is simple. SOLO TIENE ESTOS TIPOS 
        'type' => 'simple',
        'regular_price' =>  (string)$registros[0]->{'tarifa-9'}->precio,
        //concepto y referencia en un span y lo iconos en un div , y clases distintas para los span 
        // 'short_description' => '<div class="concepto_prod"> '.$concepto.'
        // <i class="fas fa-arrows-alt-h" aria-hidden="true"></i> '.$anchoDiametro.' mm  <i class="fas fa-arrows-alt-v" aria-hidden="true"></i> '.$altura.' mm
        // <i class="fas fa-box" aria-hidden="true"></i> Caja '.$unidadesCaja.' '.$formatoVentaNombre.'
        // Ref: ' .$registros[0]->{'N/Ref'}.'
        // <div></div>
        // </div>',
        'short_description' =>'<div class="concepto_prod">
  		<span class="span_concepto">'.$concepto.'</span>
 	    <div class="div_icons">
  		<i class="fas fa-arrows-alt-h" aria-hidden="true"></i> '.$anchoDiametro.'  
       	<i class="fas fa-arrows-alt-v" aria-hidden="true"></i> '.$altura.'
        <i class="fas fa-box" aria-hidden="true"></i> Caja '.$unidadesCaja.' '.$formatoVentaNombre.'
  	    </div>
        <span class="span_referencia">Ref: ' .$registros[0]->{'N/Ref'}.'</span>
        </div>',

        'sku' => (string)$sku,
        'dimensions' => [

            'length' => (string)$registros[0]-> largo,
            'width' => (string)$registros[0]-> ancho,
            'height' => (string)$registros[0]-> alto 
        ],
        
        'stock_quantity' => round($registros[0]->existencias->existencias),
        //stock_status Options: instock, outofstock, onbackorder. Default is instock.
        //aqui se podria solucionar mirando el stock de vnvm y eligiendo la opcion correcta 
        'stock_status' =>'instock',
        //Catalog visibility. Options: visible, catalog, search and hidden. Default is visible.
        'catalog_visibility' => $visibilidad,
        
        'images' => $images
       
    ];
    
   
    
    $resultCreate = $woocommerce->post('products',  $data);

    if (!$resultCreate) {
        echo ("❗Error al actualizar productos \n");
    } else {
        print("✔ Productos actualizados correctamente \n <br>");
        print_r($resultCreate);
    }
}
?>
    
</body>
</html>