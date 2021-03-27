<?php

require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

// Conexión WooCommerce API destino
// ================================
$url_API_woo = 'https://tartarizados.com/';
$ck_API_woo = 'ck_1b5c123e34750a82fd7887ced57b0f5eac5c44b6';
$cs_API_woo = 'cs_5c48d1a30bf8198d0e2cbe9974516081715565dd';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    ['version' => 'wc/v3',
    'query_string_auth'=>true]    
);
// ================================



// Conexión API VNVM pedazo de loro origen!!!!!! Esto tenemos que postear 
// ===================
$mail="jose@artipas.es";
$url_API="80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|CLI|GET|".$mail;"";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_API);
curl_setopt($ch, CURLOPT_HEADER, 0);

echo "➜ Obteniendo datos origen Vnvm ... \n";
$items_origin = curl_exec($ch);
curl_close($ch);

if ( ! $items_origin ) {
    exit('❗Error en API origen');
}

// Obtenemos datos de la API de origen
print_r($items_origin);


// ===================
// Construimos la data en base a los productos recuperados
$item_data = [];
foreach($vnvmClient as $vnvmClient){
    
    // Formamos el array a actualizar
    $item_data[] = [
        
        'mail'  => $vnvmClient->email,
        'nombreFiscal'  => $vnvmClient->nombreFiscal,
        'nombreComercial'  =>$vnvmClient->nombreComercial,
        'nif' => $vnvmClient->nif,
        'contacto'  => $vnvmClient->contacto,
        'direccion' => $vnvmClient->direccion,
        'codPostal'  => $vnvmClient->codigoPostal,
        'localidad'  => $vnvmClient->localidad,
        //Identificador de 2 caracteres, ejemplo: ES (españa) AR (argentina)
        'pais'  => $vnvmClient->pais,
        'telefono'  => $vnvmClient->telefono,
        'telefonoMovil' => $vnvmClient->telefonoMovil,
        'codFormaPago'  => $vnvmClient->formaPago,
        'codIdioma' => $vnvmClient->idioma,
        'regIva'  => $vnvmClient->regimenIVA,
        
    ];
    
}

print_r($vnvmClient);
print_r($item_data);

?>

<br>

<?php
echo "➜ Obteniendo datos origen Woocomerce ... \n";
// AQUI QUIERO PROBAR SI FUNCIONA EL GET , PEDAZO DE LORIBIO
$clientes = json_encode($woocommerce->get('customers/1'));

print_r($clientes);






// Actualización en lotes
$result = $woocommerce->post('products/batch', $data);

if (! $result) {
    echo("❗Error al actualizar productos \n");
} else {
    print("✔ Productos actualizados correctamente \n");
}

?>