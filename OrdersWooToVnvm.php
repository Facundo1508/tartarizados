<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Woocommerce a Vnvm</title>
</head>
<body>
<?php

header('Content-type: text/html; charset=utf-8');
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

// Conexión WooCommerce API destino
// ================================
//PRUEBAS
// $url_API_woo = 'https://pruebas.tartarizados.com/';
// $ck_API_woo = 'ck_41fcb94f0f50e0e1e8f67af0b649c387b62a5417';
// $cs_API_woo = 'cs_96648b4e8944fea3016c07a2c7b110965edb1d94';
//PRODUCCION
$url_API_woo = 'https://tartarizados.com/';
$ck_API_woo = 'ck_7136b22f816dc374f4955631b762fb33db03ef8b';
$cs_API_woo ='cs_c71bded97e67e40719225d5992d6ac4570ce7294';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    [
        'version' => 'wc/v3',
        'query_string_auth' => true,
    ]
);
if($_POST){

    $idOrder= $_POST['id'];
    
}else if($_GET){

    $idOrder= $_GET["id"];

}else{
    echo("No se encontraron datos de entradas para generar el pedido.");
}

$getPedidosWoo = $woocommerce->get('orders/'.$idOrder);
$serie="120";
$objOrderWoo = (object)$getPedidosWoo;

        $billing = $objOrderWoo->billing;
        $shipping=$objOrderWoo->shipping;
        $mail = $billing->email;
        $numero = $objOrderWoo->number;
        $serie = $serie;
        $fecha =date("d-m-Y", strtotime($objOrderWoo->date_created));
        $suRef = $objOrderWoo->id;
        $nombreFiscal = $billing -> first_name." ";$billing->last_name;
        $nombreComercial = $billing -> company === "" ? "sinCompany" : $billing -> company;
        $telefono = empty($shipping->phone) || is_null($shipping->phone) ? trim($billing->phone) :trim($shipping->phone) ;
        $direccion = empty($shipping->address_1) || is_null($shipping->address_1) ? $billing->address_1 : $shipping->address_1 ;
        $codPostal = empty($shipping->postcode) || is_null($shipping->postcode) ? $billing->postcode : $shipping->postcode ;
        $localidad = empty($shipping->city) || is_null($shipping->city) ? $billing->city : $shipping->city ;
        $pais =empty($shipping->country) || is_null($shipping->country) ? $billing->country : $shipping->country ;
        $observacionesEnvio = " ";
        $observaciones = $objOrderWoo->customer_note;
        $codFormaPago = "CT3";
        //se manda 0/1 por defecto al ser una lista de string y esto un booleano $objOrderWoo->status
        $confirmado = "1";
        
        $articulosList = $objOrderWoo->line_items;
  
        foreach ($articulosList as $key => $value) {
        
        $articulos[$key] = [ 
            
                'product_id' => $value->sku,
                'name' => $value->name,
                'quantity' => $value->quantity,
                'total' => $value->total
            ];
        }

        
        $line_items =(Array)$articulos;

        $salidaCompleta="";
        foreach($line_items as $valor) {

            $pv_det = "PV-DET";

            $refArticulo = $valor['product_id'];
            $nombreArticulo = preg_replace("/[^a-zA-Z0-9\_\-]+/", "", utf8_encode($valor['name']));
            $cantidadArticulo = $valor['quantity'];
            $precioArticulo = $valor['total'];
            $desc1 = "0";
            $desc2 = "0";
            $desc3 = "0";
            
            $salida= $pv_det."|".$refArticulo."|". $nombreArticulo."|".$cantidadArticulo."|". $precioArticulo."|".$desc1."|".$desc2."|".$desc3."|";
            
            $salidaCompleta = $salidaCompleta.$salida;
            
        }

        // $url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|PV|POST|".$mail."|".$serie."|".$numero."|".$fecha."|".$suRef."|".$nombreFiscal."|".$telefono."|".$direccion."|".$codPostal."|".$localidad."|".$pais."|".$observacionesEnvio."|".$observaciones."|".$codFormaPago."|".$confirmado."|".$salidaCompleta;
        $url_API = "80.35.251.17/cgi-vel/pruebas/api.pro?w_as=5684|PV|POST||".$serie."|".$numero."|".$fecha."|".$suRef."|".$nombreFiscal."|".$telefono."|".$direccion."|".$codPostal."|".$localidad."|".$pais."|".$observacionesEnvio."|".$observaciones."|".$codFormaPago."|".$confirmado."|".$salidaCompleta;
        $url_API=trim($url_API,'|');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url_API);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        $items_origin = curl_exec($ch);

        $objResponseVNVM = json_decode(utf8_encode($items_origin));
             
        if ($objResponseVNVM->operaciones->numeroErrores>0) {

            echo("❗Error al actualizar pedidos \n");
            echo("Codigo Error: ".$objResponseVNVM->operaciones->transacciones[0]->codigoError."\n");
            echo("Descripcion Error: ".$objResponseVNVM->operaciones->transacciones[0]->descripcionError."\n");

        } else if($objResponseVNVM->operaciones->operacionesRealizadas>0){

            print("✔ Pedidos actualizados correctamente \n");

        }
        curl_close($ch);
?>
    
</body>
</html>