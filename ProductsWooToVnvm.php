<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos WooCommerce a Vnvm</title>
</head>
<body>
    
</body>
</html>
<?php

header('Content-type: text/html; charset=utf-8');
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
    ]
);
$id = $_POST['id'];
$getProductWoo = $woocommerce->get('products/'.$id);
// print_r($getProductWoo);
// die;

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
        $sku = $general->sku === "" ? exit("El articulo no tiene sku") : $general->sku;
        // $sku = $general->sku = "123";
        $price = $general->price;
        $desde = empty($general->date_on_sale_from) ? date("Y-m-d") : $general->date_on_sale_from;
        $hasta = empty($general->date_on_sale_to) ? date("Y-m-d", strtotime('+10 days')) : $general->date_on_sale_to;
        $stockStatus = $general->stock_status;
        $stockCantidad = empty($general->stock_quantity) ? "0" : $general->stock_quantity;
    
        // $dimensions = $objProdcutWoo->dimensions;
        //     $largo = $dimensions->length;
        //     $ancho = $dimensions->width;
        //     $altura = $dimensions->height;
        
        // $categories = $objProdcutWoo->categories;
        //     $idCategoria = $categories->id;
        //     $nombreCategoria = $categories->name;
        //     $slugCategoria = $categories->slug;

        // print_r($id. '\n' .$nombre. '\n');
        // print_r($slug. '\n' .$tipo. '\n');
        // print_r($status. '\n' .$visible. '\n');
        // print_r($descripcion. '\n' .$descripcionCorta. '\n');
        // print_r($sku. '\n' .$price. '\n');
        // print_r($desde. '\n' .$hasta. '\n');
        // print_r($stockStatus. '\n' .$stockCantidad. '\n');
        // die;
      
        $url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|ART_BUS|POST|".$sku."|".$nombre."|".$slug."|".$status."|".$visible."|".$descripcion."|".$descripcionCorta."|".$price."|".$desde."|".$hasta."|".$stockStatus."|".$stockCantidad."";
        
     
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        
        echo "➜ Cargando Produto \n";
        $items_origin = curl_exec($ch);
        
        // print_r($items_origin);
        // die;

        if (! $items_origin) {
            echo("❗Error al actualizar productos \n");
        } else {
            print("✔ Productos actualizados correctamente \n");
        }

        curl_close($ch);
        
        // if (!$items_origin) {
        //     exit('❗Error en API origen');
        // }
        
        // $getDecodedVnvm = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin));

        // print_r($getDecodedVnvm);

?>