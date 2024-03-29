<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARTARIZADOS</title>
</head>


<!-- PARTE DE VNUM A WOO -->
<header>
    <h1 style="text-align: center;">
        MIGRAR DE VNVM A WOOCOMMERCE
    </h1>
</header>

<body>

    <!-- PRODUCTS VNVMTOWOO-->
    <h1>
        Migrar Productos
        <h3>
            <small>
                De vnvm a woocommerce
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="ProductsVnvmToWoo.php">
        <input type="text" name="id" value="" placeholder="Id productos">
        <input type="submit" />
    </form>
    <h1>
        Update Productos
        <h3>
            <small>
                Este actualizador recibe un N-Ref, o una cadena de N-ref separados por ",". </br> Ej: PS34537,BC3N,BC3T   
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="UploadProductsVnvmtoWoo.php">
        <input type="text" name="id" value="" placeholder="Id productos">
        <input type="submit" />
    </form>
    <h1>
        Update Productos por Familia
        <h3>
            <small>
                Este actualizador recibe una familia. </br> Ej: JR0103
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="UpdateProductsByFamily.php">
        <input type="text" name="familia" value="" placeholder="Id familia">
        <input type="submit" />
    </form>
    <!-- ALL PRODUCTS VNVMTOWOO-->
    <h1>
        Migrar todos los Productos
        <h3>
            <small>
                De vnvm a woocommerce
            </small>
        </h3>
    </h1>

    <form name="formulario" method="post" action="AllProductsVnumToWoo.php">
        <input type="text" name="desde" value="" placeholder="Desde">
        <input type="text" name="hasta" value="" placeholder="Hasta">
        <br>
        <br>
        <input type="submit" />
    </form>
<!-- Actualizar Cantidades de Producto-->
<h1>
        Actualizar Cantidades de Producto
        <h3>
            <small>
                De woocommerce a vnvm
            </small>
        </h3>
    </h1>

    <form name="formulario" method="post" action="UploadALL.php">        
        <br>
        <br>
        <input type="submit"/>
    </form>


    <!-- ORDERS VNVMTOWOO-->
    <h1>
        Migrar Pedidos
        <h3>
            <small>
                De vnvm a woocommerce
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="OrdersVnvmToWoo.php">
        <input type="email" name="email" value="" placeholder="Email">
        <input type="number" name="serie" value="" placeholder="Serie">
        <input type="number" name="num" value="" placeholder="Número de pedido">
        <input type="date" name="fch" value="" placeholder="fecha">
        <input type="submit" />
    </form>
    <br>
    <br>
    <br>
  <!-- ALL ORDERS VNVMTOWOO-->
  <h1>
        Migrar todos los Pedidos
        <h3>
            <small>
                De vnvm a woocommerce
            </small>
        </h3>
    </h1>

    <form name="formulario" method="post" action="AllOrdersVnvmToWoo.php">
        <input type="date" name="desde" value="">
        <input type="date" name="hasta" value="">
        <br>
        <br>
        <input type="submit" />
    </form>

       <!-- CLIENTES VNVMTOWOO-->
       <h1>
        Migrar Clientes
        <h3>
            <small>
                De vnvm a woocommerce
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="OrdersVnvmToWoo.php" >
        <input type="text" name="mail" value="" placeholder="Mail de clientes" disabled>
        <input type="submit" / disabled>
    </form>
    <br>
    <br>
    <br>

    <hr class="solid">


    <!-- PARTE DE WOO A VNVM -->
    <h1 style="text-align: center;">
        MIGRAR DE WOOCOMMERCE A VNVM
    </h1>

    <!-- PRODUCTS WOOTOVNVM-->
    <h1>
        Migrar Productos
        <h3>
            <small>
                De woocommerce a vnvm
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="ProductsWooToVnvm.php">
        <input type="text" name="id" value="" placeholder="Id productos" disabled>
        <input type="submit" disabled/>
    </form>

    <!-- ORDERS WOOTOVNVM-->
    <h1>
        Migrar Pedidos
        <h3>
            <small>
                De woocommerce a vnvm
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="OrdersWooToVnvm.php">
        <input type="text" name="id" value="" placeholder="Id pedidos">
        <input type="submit" />
    </form>

    <!-- CUSTOMER WOOTOVNVM-->
    <h1>
        Migrar clientes
        <h3>
            <small>
                De woocommerce a vnvm
            </small>
        </h3>
    </h1>
    <form name="formulario" method="post" action="CustomersWooToVnvm.php">
        <input type="text" name="id" value="" placeholder="Id clientes">
        <input type="submit" />
    </form>
    <br>
    <br>
    <br>
</body>

</html>