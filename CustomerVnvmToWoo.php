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
$url_API = "80.35.251.17/cgi-vel/vnvm/api.pro?w_as=5684|CLI|GET|" . $mail;


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
// Obtenemos datos de la API de origen

$getDecodedVnvm = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $items_origin));

// ===================
// Construimos la data en base a los productos recuperados
$item_data = [];
$email;

if (is_array($getDecodedVnvm) || is_object($getDecodedVnvm)) {

    foreach ($getDecodedVnvm as $clientes => $datosClientes) {

         //var_dump($datosClientes->registros);

        foreach ($datosClientes->registros as $key => $value) {

            $data = [
                'email' => $value->email,
                'first_name' => $value->nombreFiscal,
                'last_name' => $value->nombreFiscal,
                'username' => 'john.doe',
                'billing' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'company' => $value->nombreComercial,
                    'address_1' => $value->direccion,
                    'address_2' => $value->direccion,
                    'city' => $value->localidad,
                    'state' => $value->provincia,
                    'postcode' => $value->codigoPostal,
                    'country' => 'US',
                    'email' =>  $value->email,
                    'phone' => $value->telefono
                ],
                'shipping' => [
                    'first_name' => $value->nombreFiscal,
                    'last_name' => $value->nombreFiscal,
                    'company' => $value->nombreComercial,
                    'address_1' => $value->direccion,
                    'address_2' => $value->direccion,
                    'city' => $value->localidad,
                    'state' => $value->provincia,
                    'postcode' => $value->codigoPostal,
                    'country' => 'US'
                ]
            ];
            // $idWoo=$value->id;
            // $usernameWoo=$value->nombreFiscal;
            // $companyWoo=;
            // $firstNameWoo=;
            // $lastNameWoo=;
            // $billing_dniWoo=$value->nif;
            // $billing_vatWoo=$value->nif;
            // $adrees1Woo=;
            // $postCodeWoo=;
            // $cityWoo=;

            // $countryWoo=$value->pais;//obj

            // $phoneWoo=$value->telefono;
            // $emailWoo=$value->email;
            // $lenguajeWoo=$value->idioma;//obj
            // $roleWoo=$value->grupo;//obj
            // $fec_nacWoo="";
            // $newsletterWoo=$value->aceptaRGPD;
            // $passwordWoo=$value->contrasea;
            // $dateCreateWoo="";
            // $billingWoo="";
        }
    }
} else {

    echo ("no entro <br>");
}
?>

<br>

<?php
// error_reporting(E_ERROR);
// ini_set("display_errors", 0);
// Actualización en lotes

$result = $woocommerce->post('customers',  $data);

if (!$result) {
    echo ("❗Error al actualizar productos \n");
} else {
    print("✔ Productos actualizados correctamente \n");
}

?>