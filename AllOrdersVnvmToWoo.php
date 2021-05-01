<?php
set_time_limit(1200);
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
$desde = (string)$_POST['desde'];
$newDesde = date("d-m-Y", strtotime($desde));

$hasta = $_POST['hasta'];
$newHasta = date("d-m-Y", strtotime($hasta));

$url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|PV_BUS|GET|500|1|0|0||" . $newDesde . "|" . $newHasta;

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
$datosClientes = (object)$getDecodedVnvm->pedidos;


$registros = $datosClientes->registros;

if ($datosClientes->totalRegistros <= 0) {
    echo ("No se encontraron registros con el Id proporcionado.");
    exit();
}

$int = 0;
foreach ($registros as $registros) {

    $obj = $registros->direccionEnvio;
    $objDetPed = $registros->detallePedido;

    foreach ($objDetPed as $key => $value) {
                
        $line_items[$key] = [ 
            'sku' =>  $value->refArticulo,
            'quantity' => $value->cantPedida
        ];
    }

    foreach ($objDetPed as $key1 => $value1) {
                
        $shipping_lines[$key1] = [ 
            'method_id' => 'flat_rate',
            'method_title' => 'Flat Rate',
            'total' => $value1->precio <= 0 ? '0.00' : (string)$value1->precio
        ];
    }

    $data = [
        //string id del metodo de pago
        'payment_method' => "bacs",
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
            'email' => $registros->cliente->email,
            'phone' => '-'
        ],
        'shipping' => [
            'first_name' => $obj->nombre,
            'last_name' => $obj->nombre,
            'address_1' => $obj->direccion,
            'address_2' => $obj->direccion,
            'city' => $obj->localidad,
            'state' => $obj->provincia,
            'postcode' =>$obj->codigoPostal,
            'country' => $obj->pais->siglas
        ],

        'line_items' => $line_items,

        'shipping_lines' => $shipping_lines
    ];

    // print_r($data);
    // die;
    
    $result = $woocommerce->post('orders',  $data);
    
    if (!$result) {
        echo ("❗Error al actualizar productos \n");
    } else {
        print("✔ Productos actualizados correctamente \n <br>");
        print_r($result);
        die;
    }
}
