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
$desde = $_POST['desde'];
$hasta = $_POST['hasta'];
$url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|ART_BUS|GET|100|1|1|1|Publicable|".$desde."|".$hasta;

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

    $getDecodedVnvm = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin);
	$getDecodedVnvm = json_decode(utf8_encode($getDecodedVnvm));
    //Este es el Objeto que trae Vnvm, sacamos el Id para mandarlo al insert como sku , y para comparar que no haya otro igual 
    $datosClientes = (object)$getDecodedVnvm->articulos;
    
    $registros = $datosClientes->registros;
    if( $datosClientes->totalRegistros <= 0 ){
        echo("No se encontraron registros con el Id proporcionado.");
        exit();
    }
    
    $int=0;
    foreach($registros as $registros) {

        $sku = $registros->{'N/Ref'}; 
        //Este es el objeto que trae Woocommerce, por el sku. Si existe el objeto termina la ejecucion 
        $params = [
            'sku' => (string)$sku
        ];
        
        $getSku = $woocommerce->get('products', $params);
        
        
        if ($getSku) {
            
            $idUpdate = $getSku[0]->sku;
            echo('❗Ya existe el producto, sku = '.$sku);
            continue;
        
        } else {
           
            $registros = $datosClientes->registros;
            // print_r($registros[0]);
            // die;
        
            $imgVisd = $registros[$int]->imagenes;
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
                    ];
                }
            };    
        
            $catFamilia = $registros[$int]->familia;
            $familia = [];
            foreach ($catFamilia as $key1 => $value1) {
                           
                $familia[$key1] = $value1;         
                
                $data = [
                    'name' => empty($registros[$int]->nombreAlternativo) || is_null($registros[$int]->nombreAlternativo)  ? $registros[$int]->nombre : $registros[$int]->nombreAlternativo ,
                    // Options: simple, grouped, external and variable. Default is simple. SOLO TIENE ESTOS TIPOS 
                    'type' => 'simple',
                    'regular_price' =>  (string)$registros[$int]->{'tarifa-9'}->precio,
                    'description' => $registros[$int]->catalogo,
                    'short_description' => $registros[$int]->metaDescripcion,
                    'sku' => (string)$sku,
                    'dimensions' => [
            
                        'length' => (string)$registros[$int]-> largo,
                        'width' => (string)$registros[$int]-> ancho,
                        'height' => (string)$registros[$int]-> alto
                    ],
                    
                    'stock_quantity' => round($registros[$int]->existencias->existencias),
                    'stock_status' =>'instock',
                    //Catalog visibility. Options: visible, catalog, search and hidden. Default is visible.
                    'catalog_visibility' => 'visible',
                    'sale_price' => (string)$registros[$int]->oferta,
                  
                    //para la categoria , la misma tiene que estar creada previamente en WOOCOMERCE y el id es numerico 
                    'categories' => [
                        [
                            'id' => '9'
                        ],
                    ],
                    'images' => $images

                    //'images' => $images
                ];
            }
            
            $resultCreate = $woocommerce->post('products',  $data);
        
            if (!$resultCreate) {
                echo ("❗Error al actualizar productos \n");
            } else {
                print("✔ Productos actualizados correctamente \n <br>");
                print_r($resultCreate);
            }
         
            $int++;
        }
      
    }
 
