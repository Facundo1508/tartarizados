<?php
header('Content-type: text/html; charset=utf-8');
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
// ================================
// Conexión API VNVM pedazo de loro origen!!!!!! Esto tenemos que postear 
// ===================
$mail = "jose@artipas.es";
$url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|1|1|1|Publicable|623|623";



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

if (is_array($getDecodedVnvm) || is_object($getDecodedVnvm)) {

    foreach ($getDecodedVnvm as $clientes => $datosClientes) {
        foreach ($datosClientes->registros as $key => $value) {
           
            $data = [
                'name' => $value->nombre,
                'type' =>  "simple",
                'regular_price' => '212',
                'description' => $value->metaDescripcion,
                'short_description' => 'asdasdasd',
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
    }

} else {

    echo ("no entro <br>");
}



$result = $woocommerce->post('products',  $data);

if (!$result) {
    echo ("❗Error al actualizar productos \n");
} else {
    print("✔ Productos actualizados correctamente \n");
}
?>