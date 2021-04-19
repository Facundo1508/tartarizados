<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Vnvm a WooCommerce</title>
</head>
<body>
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
$id = $_POST['id'];
$url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|ART_BUS|GET|100|1|1|1|Publicable|".$id."|".$id;

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
    exit('❗Ya existe el producto, sku = ' . $sku);

} else {
    
    $registros = $datosClientes->registros;
    // print_r($registros[0]);
    // die;

    $imgVisd = $registros[0]->imagenes;
    // print_r($imgVisd[0]->visd);
    // die;

    foreach ($imgVisd as $key => $value) {
        
        $images[$key] = [ 
            'src' => (string)'http://80.35.251.17/cgi-vel/pruebas/'.$value->visd,
        ];
    }

    $catFamilia = $registros[0]->familia;
    $familia = [];
    foreach ($catFamilia as $key1 => $value1) {
                   
        $familia[$key1] = $value1;         
      
    }
    $data = [
        'name' => $registros[0]->nombre,
        // Options: simple, grouped, external and variable. Default is simple. SOLO TIENE ESTOS TIPOS 
        'type' => 'simple',
        'regular_price' =>  (string)$registros[0]->{'tarifa-1'}->precio,
        'description' => $registros[0]->catalogo,
        'short_description' => $registros[0]->metaDescripcion,
        'sku' => (string)$sku,
        'dimensions' => [

            'length' => (string)$registros[0]-> largo,
            'width' => (string)$registros[0]-> ancho,
            'height' => (string)$registros[0]-> alto
        ],
        
        'stock_quantity' => $registros[0]->existencias->existencias,
        //stock_status Options: instock, outofstock, onbackorder. Default is instock.
        //aqui se podria solucionar mirando el stock de vnvm y eligiendo la opcion correcta 
        'stock_status' =>'instock',
        //Catalog visibility. Options: visible, catalog, search and hidden. Default is visible.
        'catalog_visibility' => 'visible',
        'sale_price' => (string)$registros[0]->oferta,
        //esto es un boolean por defecto false
        //'featured' => false,
        // 'date_on_sale_from' =>null,
        // 'date_on_sale_to' => null,
        
        //para la categoria , la misma tiene que estar creada previamente en WOOCOMERCE y el id es numerico 
        'categories' => [
            [
                'id' => '9'
            ],
        ],
        'images' => [
            [
                'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
            ]
        ]
    ];

    $resultCreate = $woocommerce->post('products',  $data);

    if (!$resultCreate) {
        echo ("❗Error al actualizar productos \n");
    } else {
        print("✔ Productos actualizados correctamente \n <br>");
        print_r($resultCreate);
    }
}
?>
    
</body>
</html>