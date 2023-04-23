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
        $this->nav = new navegador("https://google.com");
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
            // Creamos un array con el contenido de la respuesta, haciendo que cada línea sea un valor del array
            $lineas = explode("\n", $resp);
            //Para almacenar los enlaces y el número de veces que ocurren
            $linksInSearch = array();

            foreach ($lineas as $numLinea => $linea) {
                //Busca la línea en la que está el resultado
                if ((strpos($linea, '<a class=') !== false) && strpos($lineas[$numLinea+1], '<span class=') !== false) {
                    //Extrae la url del resultado. 
                    if (strpos($linea, "url?q=https://") !== false) {
                        $domin = extractUrl('url?q=https://','/', $linea);
                    } 
                    else {
                        $domin = extractUrl('url?q=http://','/', $linea);
                    }
                    /**
                     * Si el dominio ya está como key en el array, le añade uno al valor actual, si no está,
                     *  y es distinto a "", lo pone a uno usando como key del array el dominio.
                     */
                    if ($domin != "" && strpos($domin, ".") !== false) {
                        if(array_key_exists($domin, $linksInSearch)) {
                            $linksInSearch[$domin] += 1;
                        }
                        else {
                            $linksInSearch[$domin] = 1;
                        }
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