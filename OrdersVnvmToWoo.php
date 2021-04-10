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
$url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|PV|GET|jose@artipas.es|119|9|08-04-2021";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url_API);
curl_setopt($ch, CURLOPT_HEADER, 0);

echo "➜ Obteniendo datos origen Vnvm ... \n";
$items_origin = curl_exec($ch);
// print_r($woocommerce->get('orders'));
// die;
curl_close($ch);

if (!$items_origin) {
    exit('❗Error en API origen');
}

$getDecodedVnvm = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin));

print_r($items_origin);
die;
?>
<br>
<?php
 
    $data = [
        'payment_method' => 'bacs',
        'payment_method_title' => 'Direct Bank Transfer',
        'set_paid' => true,
        'billing' => [
            'first_name' => $obj->nombre,
            'last_name' =>  $obj->nombre,
            'address_1' => $obj->direccion,
            'address_2' => $obj->direccion,
            'city' => $obj->localidad,
            'state' =>$obj->provincia,
            'postcode' => $obj->codigoPostal,
            'country' => $obj->pais->siglas,
            'email' => $obj->email,
            'phone' => '-'
        ],
        'shipping' => [
            'first_name' => $obj->nombre,
            'last_name' => $obj->nombre,
            'address_1' => $obj->direccion,
            'address_2' => '',
            'city' => $obj->localidad,
            'state' => $obj->provincia,
            'postcode' =>$obj->codigoPostal,
            'country' => $obj->pais->siglas
        ],
        'line_items' => [
            [
                'product_id' =>  $obj->refArticulo,
                'quantity' => 2
            ],
            [
                'product_id' => 22,
                'variation_id' => 23,
                'quantity' => 1
            ]
        ],
        'shipping_lines' => [
            [
                'method_id' => 'flat_rate',
                'method_title' => 'Flat Rate',
                'total' => '10.00'
            ]
        ]
    ];


$result = $woocommerce->post('products',  $data);

if (!$result) {
    echo ("❗Error al actualizar productos \n");
} else {
    print("✔ Productos actualizados correctamente \n");
}
?>