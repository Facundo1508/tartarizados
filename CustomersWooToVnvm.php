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
$id = $_POST['id'];
$getClienteWoo = $woocommerce->get('customers/'.$id);

// $params = [
//     'clave' => (string)$clave
// ];

// $getClave = $woocommerce->get('customers', $params);


// if ($getClave) {
    
//     $idUpdate = $getClave[0]->id;
//     exit('❗Ya existe el producto, clave = ' . $clave);

// } else {

$objClientWoo = (object)$getClienteWoo;

    $billing = $objClientWoo->billing;

        $mail = $billing->email;
        $nombreFiscal = $billing -> first_name." ";$billing->last_name;
        $nombreComercial = $billing -> company === "" ? "sinCompany" : $billing -> company;
        $NIF = "123456";
        $contacto = "tusmuertos";
        $direccion = $billing->address_1;
        $codPostal = $billing->postcode;
        $localidad = $billing->city;
        $pais = $billing->country;
        $telefono = trim($billing->phone);
        $telefonoMovil = trim($billing->phone);
        $codFormaPago = "CT3";
        //Identificador de 2 caracteres, ejemplo: ES (españa) AR (argentina)
        $codIdioma = "ES";
        $regIva = "A";

        // // $regIvaList = [
        // //     "N" => "Nacional",
        // //     "R" => "Nacional con recargo",
        // //     "E" => "Nacional excento (Canarias, Ceuta, Melilla)",
        // //     "I" => "Intracomunitario",
        // //     "X" => "Exportaciones",
        // //     "1" => "Igic",
        // //     "2" => "Ipsi",
        // // ];
        
        
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