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

$getProductWoo = $woocommerce->get('products/1743');

$objProdcutWoo = (object)$getProductWoo;


        $general = $objProdcutWoo;

        $id = $general->id;
        $nombre = $general->name;
        $slug = $general->slug;
        $tipo = $general->type;
        $status = $general->status;
        $visible = $general->catalog_visibility;
        $descripcion = $general->description;
        $descripcionCorta = $general->short_description;
        $sku = $general->sku;
        $price = $general->price;
        $desde = $general->date_on_sale_from;
        $hasta = $general->date_on_sale_to;
        $stockStatus = $general->stock_status;
        $stockCantidad = $general->stock_quantity;
    
        // $dimensions = $objProdcutWoo->dimensions;
        //     $largo = $dimensions->length;
        //     $ancho = $dimensions->width;
        //     $altura = $dimensions->height;
        
        // $categories = $objProdcutWoo->categories;
        //     $idCategoria = $categories->id;
        //     $nombreCategoria = $categories->name;
        //     $slugCategoria = $categories->slug;

        //sugar tu tu tu tu tu tu oh honey honey tu tu tu tu tu tu https://youtu.be/M03hVvwEuuk?t=118
      
        $url_API = "80.35.251.17/cgi-vel/pruebas/vnvm/api.pro?w_as=5684|ART_BUS|POST|".$sku."|".$nombre."|".$slug."|".$status."|".$visible."|".$descripcion."|".$descripcionCorta."|".$price."|".$desde."|".$hasta."|".$stockStatus."|".$stockCantidad."";
        
     
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        
        echo "➜ Cargando Cliente \n";
        $items_origin = curl_exec($ch);
        print_r($items_origin);
        die;
        if (! $items_origin) {
            echo("❗Error al actualizar productos \n");
        } else {
            print("✔ Productos actualizados correctamente \n");
        }
        curl_close($ch);
        
        if (!$items_origin) {
            exit('❗Error en API origen');
        }
        
        $getDecodedVnvm = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin));

        print_r($getDecodedVnvm);

?>