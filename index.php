<?php
include_once "lib/buscador.php";
include_once "lib/funciones.php";

$busca = new buscador();

//Si la variable POST está asignada, hemos realizado una búsqueda, y se llama a la función para obtener los datos
if (isset($_POST['busqueda'])) {
    $qString = $_POST['busqueda'];
    $resul = $busca->busca($qString);
    if ($resul === false) {
        //En caso de producirse un error, lo indicamos.
        echo "Error al realizar la búsqueda</br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Acumulative Trending Search</title>
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body>

    <form action="" method="post">
        <p>Búsqueda: </br><input type="text" name="busqueda" /></p>
        <p><input type="submit"/></p>
        <p>Nota: no se tendrán en cuenta en los cálculos las búsquedas ya realizadas</p>
    </form>
    <div class="container">
        <div class="izquierda">
            <!-- Tabla con los enlaces y las veces que aparece en las búsquedas en total -->
            <table>
                <tr>
                    <th>Url</th>
                    <th>Relevancia</th>
                </tr>
            <?php
                    echo $busca->getTabla();
            ?>
            </table> 
        </div>
        <div class="derecha">
            <!-- Tabla con las búsquedas realizadas -->
            <table>
                <tr>
                    <th>Búsquedas anteriores</th>
                </tr>
            <?php
                    echo $busca->getSearched();
            ?>
            </table> 
        </div>
    </div>
  </body>
</html>