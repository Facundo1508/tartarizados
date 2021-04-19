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
    ]
);

$getPedidosWoo = $woocommerce->get('orders/1739');

// print_r($getPedidosWoo);
// die;

// $params = [
//     'clave' => (string)$clave
// ];

// $getClave = $woocommerce->get('customers', $params);


// if ($getClave) {
    
//     $idUpdate = $getClave[0]->id;
//     exit('❗Ya existe el producto, clave = ' . $clave);

// } else {

$objOrderWoo = (object)$getPedidosWoo;




        $billing = $objOrderWoo->billing;

        $mail = $billing->email;
        $serie = $objOrderWoo->parent_id;
        $fecha = $objOrderWoo->date_created;
        $suRef = $objOrderWoo->id;
        $nombreFiscal = $billing -> first_name." ";$billing->last_name;
        $nombreComercial = $billing -> company === "" ? "sinCompany" : $billing -> company;
        $telefono = trim($billing->phone);
        $direccion = $billing->address_1;
        $codPostal = $billing->postcode;
        $localidad = $billing->city;
        $pais = $billing->country;
        $observacionesEnvio = "";
        $observaciones = $objOrderWoo->customer_note;
        $codFormaPago = "CT3";
        $confirmado = $objOrderWoo->status;
        
        $line_items =(Array)[
            [
                'product_id' => 93,
                'name' => "Loro",
                'quantity' => 1,
                'total' => 20
            ],
            [
                'product_id' => 94,
                'name' => "Loro1",
                'quantity' => 2,
                'total' => 25
            ],
            [
                'product_id' => 95,
                'name' => "Loro2",
                'quantity' => 3,
                'total' => 30
            ]
        ];
        
        $salidaCompleta="";
        foreach($line_items as $valor) {

            $pv_det = "PV-DET";

            $refArticulo = $valor['product_id'];
            $nombreArticulo = $valor['name'];
            $cantidadArticulo = $valor['quantity'];
            $precioArticulo = $valor['total'];
            $desc1 = "0";
            $desc2 = "0";
            $desc3 = "0";
            
            $salida= $pv_det."|".$refArticulo."|". $nombreArticulo."|".$cantidadArticulo."|". $precioArticulo."|".$desc1."|".$desc2."|".$desc3."|";
            
            $salidaCompleta =+ $salida;
            
        }

        print_r($salidaCompleta);
        die;
   
        
        //$url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|PV|POST|info@reloga.net|1|326296|06-04-2021|SU REF|Nombre dir envio|telefono dir envio|dirección dir envio|08940|Cornella de Llobregat|ES|Observaciones dir envio|Observaciones|CF1|0|PV-DET|31010170|Descripcion articulo|5|10|0|0|0|PV-DET|31010376|Descripcion articulo 2|15|30|0|0|0|PV-DET|895248|Descripcion articulo 3|25|35|0|0|0";
        $url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|CLI|POST|".$mail;"|".$nombreFiscal;"|".$nombreComercial;"|".$NIF;"|".$contacto;"|".$direccion;"|".$codPostal;"|".$localidad;"|".$pais;"|".$telefono;"||CT3||R";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        echo "➜ Cargando Cliente \n";
        $items_origin = curl_exec($ch);

        if (! $items_origin) {
            echo("❗Error al actualizar clientes \n");
        } else {
            print("✔ Clientes actualizados correctamente \n");
        }
        curl_close($ch);

?>