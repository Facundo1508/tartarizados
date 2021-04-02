<?php
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

// Conexión WooCommerce API destino
// ================================
$url_API_woo = 'https://pruebas.tartarizados.com/';
$ck_API_woo = 'ck_41fcb94f0f50e0e1e8f67af0b649c387b62a5417';
$cs_API_woo = 'cs_96648b4e8944fea3016c07a2c7b110965edb1d94';

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

$url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|1|1|1|Publicable|555|555";

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
    
    $getDecodedVnvm = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin));
    
    
    //Este es el Objeto que trae Vnvm, sacamos el Id para mandarlo al insert como sku , y para comparar que no haya otro igual 
    $datosClientes = (object)$getDecodedVnvm->articulos;
    
    $registros = $datosClientes->registros;
    
    $sku = $registros[0]->id;
    
    //Este es el objeto que trae Woocommerce, por el sku. Si existe el objeto termina la ejecucion 
    $params = [
        'sku' => (string)$sku
    ];
    
    $getSku = $woocommerce->get('products', $params);
    
    
    if ($getSku) {
        
        $idUpdate = $getSku[0]->sku;
        exit('❗Ya existe el producto, sku = '.$sku);
    
    } else {
    
    
        foreach ($datosClientes->registros as $key => $value) {
    
            $data = [
                'name' => $value->nombre,
                'type' =>  "simple",
                'regular_price' => '212',
                'description' => $value->metaDescripcion,
                'short_description' => 'asdasdasd',
                'sku' => (string)$sku,
                'categories' => [
                    [
                        'id' => 9
                    ],
                    [
                        'id' => 14
                    ]
                ],
                'images' => [
                    [
                        'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
                    ],
                    [
                        'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
                    ]
                ]
            ];
        }
    
        $resultCreate = $woocommerce->post('products',  $data);
    
        if (!$resultCreate) {
            echo ("❗Error al actualizar productos \n");
        } else {
            print("✔ Productos actualizados correctamente \n <br>");
            print_r($resultCreate);
        }
    }
    



