<?php
include_once "lib/navegador.php";
include_once "lib/database.php";
include_once "lib/funciones.php";
/**
 * Clase para realizar las búsquedas
 */
class buscador {
    //Objeto navegador para realizar las búsquedas
    private $nav;

    /**
     * Contructor
     */
    function __construct() {
        $this->nav = new navegador("https://www.bing.com");
    }

    /**
     * Función para buscar una cadena de texto.
     * Parámetros:
     * - la cadena a buscar
     * Devuelve:
     * - El contenido de la web tras realizar la consulta, false en caso de error, 0 si ya estaba hecha.
     */
    function search($rawString) {
        $dbCon = new database();
        $dbCon->conecta();
        //Escapamos caracteres especiales, por si acaso y comprobamos que no se ha realizado la búsqueda antes.
        $querySearch = $dbCon->escapeString($rawString);
        if (!$dbCon->checkSearched($querySearch)) {
            //Añadimos al historial de búsqueda si no estaba, y realizamos la búsqueda
            if($dbCon->addSearch($querySearch)) {
                $queryToSearch = urlencode($querySearch);
                $queryS = '/search?q=' . $queryToSearch . "&ia=web";
                $answer = $this->nav->navega($queryS);
                //Añadimos un cambio de línea tras cada carácter ">" para que sea más sencillo analizar los datos
                $answer =  str_replace(">", ">\n", $answer);
                $dbCon->desconecta();
                return $answer;
            }
            else {
                $dbCon->desconecta();
                return false;
            }
        }
        $dbCon->desconecta();
        return 0;
    }

    /**
     * Función busca, ecapsula la anterior, y trata los datos una vez obtenidos.
     * Parámetros:
     *  - La cadena a buscar
     * Devuelve
     */
    function busca($rawString) {
        // Verificamos que la búsqueda no esté realizada previamente
        $resp = $this->search($rawString);
        if ($resp != false) {
            //Almacenamos el contenido en un fichero, para análisis en caso de fallo
            $fichero = "tmp/" . strtotime("now") . ".txt";

            file_put_contents($fichero, $resp);

            $aArray = file($fichero, FILE_IGNORE_NEW_LINES);

            // Creamos un array con el contenido de la respuesta, haciendo que cada línea sea un valor del array
            $lineas = explode("\n", $resp);
            //Para almacenar los enlaces y el número de veces que ocurren
            $linksInSearch = array();
            //Para controlar el flujo dentro del bucle.
            $resultados = false;
            $elemento = false;

            foreach($lineas as $linea) {
                /**
                 * Verificamos las líneas hasta que aparece el marcardor de que después vendrán los resultados.
                 * si aparece, permitimos que se busque si hay elementos de resultados en esa línea
                 */
                if (strpos($linea, '<ol id="b_results"')  !== false) {
                    $resultados = true;
                }  
                //Para que no busque más allá de donde no hay más datos.
                if (strpos($linea, '</ol>')  !== false) {
                    $resultados = false;
                }  
                /**
                 * Si encontramos un elemento de resultado de la búsqueda, sabremos que las siguientes líneas
                 * hasta que se cierre el elemento, serán los datos del resultado.
                 */
                if (($resultados == true) && (strpos($linea, '<li class="b_algo"') !== false)) {
                    $elemento = true;
                }
                //Para saber cuándo termina el elemento
                if (($elemento == true) && (strpos($linea, '</li>') !== false)) {
                    $elemento = false;
                }
                //Si estamos buscando un elemento, y aparace una línea de enlace, será la url que buscamos
                if (($elemento == true) && (strpos($linea, '<a href=')!== false)) {
                    $elem = explode(" ", $linea);
                    /**
                     * Separamos los elementos de la línea, nos quedamos con el que contiene el href
                     * y eliminamos el href= y las comillas dobles, después nos quedamos sólo con la parte 
                     * del enlace que corresponde al dominio
                     */
                    $hLink = str_replace(array('href=', '"'), array("", ""), $elem[1]);
                    $domin = extractUrl("//", "/", $hLink);
                    /**
                     * Si el dominio ya está como clave del array, añadimos uno al valor que tenga, si no está, 
                     * lo añadimos como clave, y le damos valor 1.
                     */
                    if(array_key_exists($domin, $linksInSearch)) {
                        $linksInSearch[$domin] += 1;
                    }
                    else {
                        $linksInSearch[$domin] = 1;
                    }
                }
                
            }

            $db = new database();

            if(!$db->conecta()) {
                echo "Fallo al conectar a la base de datos </br>";
                exit(0);
            }

            //Metemos los datos en la base de datos, obtenemos el valor previo de cada dominio, y lo añadimos al actual
            foreach($linksInSearch as $urlKey => $relevance) {
                $newRel = $db->getDomainRelevance($urlKey) + $relevance;
                if (!$db->setDomainRelevance($urlKey, $newRel)) {
                    echo "Error al insertar la relevancia en la base de datos. ($urlKey, $newRel) </br>";
                    exit(0);
                }   
            }
            
            $db->desconecta();
            return $linksInSearch;
        }
        else {
            if($resp === false) {
                echo "Fallo al insertar la búsqueda en la tabla. </br>";
                exit(0);
            }
            else {
                echo "La búsqueda ya se ha realizado previanmente, no se añadirán datos.</br>";
            }
            
        }
    }

    /**
     * Función para obtener una tabla con los datos de url y número de veces que aparece en las búsquedas,
     * devuelve el contenido de una tabla HTML
     * Devuelve:
     * - Un string con el contenido de una tabla HTML
     */
    function getTabla() {
        $db = new database();

        if(!$db->conecta()) {
            echo "Fallo al conectar a la base de datos </br>";
            exit(0);
        }

        //Toma los datos de la base de datos y los adapta al contenido de una tabla HTML
        $arrayDomains = $db->getAllDomains();
        $stringTabla = addaptToTable($arrayDomains);
        $db->desconecta();
        return $stringTabla;
    }


    /**
     * Función para obtener un string con el contenido de una tabla HTML con las búsquedas realizadas previamente
     * Devuelve:
     * - Un string con el contenido de una tabla HTML
     */
    function getSearched() {
        $db = new database();

        if(!$db->conecta()) {
            echo "Fallo al conectar a la base de datos </br>";
            exit(0);
        }

        //Toma los datos de la base de datos y los adapta al contenido de una tabla HTML
        $arrayDomains = $db->getSearched();
        $stringTabla = addaptToTableSearched($arrayDomains);
        $db->desconecta();
        return $stringTabla;
    }

}