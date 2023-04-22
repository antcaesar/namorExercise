<?php
/**
 * Función para extraer un fragmento de una línea de texto
 * Parámetros:
 *  - Caracter o String que delimita el comienzo de la subcadena 
 *  - Caracter o String que delimita el final, si es ninguno ha de ser '' 
 *  - Cadena de la que se quiere extraer el fragmento. 
 */
function extractUrl($strIni, $strFin, $line) {
    $strLen = 0;
    $first  = strpos($line, $strIni);
    if ($first !== false) {
        if ($strFin !== '') {
            //Se le añade dos en este caso para que se salte el // 
            $last   = strpos($line, $strFin, $first+2);
            if($last !== false) {
                $strLen = $last - $first;
                $result = substr($line, $first+2, $strLen-2);
                //$result = substr($result, strpos($result, ".")+1);
                return $result;
            }
        }
        else {
            $result = substr($line, $first);
            //$result = substr($result, strpos($result, ".")+1);
            return $result;
        }
        
    }
}
/**
 * Función que, dado un array de valores $dominio => valor, lo adapta para ser el contenido de una tabla HTML
 * Parámetros:
 * - El array devuelto por la función getAllDomains de la clase database
 * Devuelve:
 * - Un string con formato HTML en forma de contenido de una tabla para mostrar los datos,
 *   una cadena vacía en caso de no tener datos que mostrar
 */
function addaptToTable($arrayValues) {
    $rows = "";
    if(is_array($arrayValues)) {
        foreach($arrayValues as $urlDomain => $value) {
            $rows .= "<tr>
            <td>$urlDomain</td>
            <td>$value</td>
            </tr>";
        }
        return $rows;
    }
    return "";
}

/**
 * Función para adaptar un array con las búsquedas realizadas a una tabla HTML.
 * Parámetros:
 * - El array devuelto por la función getSearched de la clase database
 * Devuelve:
 * - Un string con formato HTML en forma de contenido de una tabla para mostrar los datos
 *   una cadena vacía en caso de no tener datos que mostrar.
 */
function addaptToTableSearched($arrayValues) {
    $rows = "";
    if(is_array($arrayValues)) {
        foreach($arrayValues as $value) {
            $rows .= "<tr>
            <td>$value</td>
            </tr>";
        }
        return $rows;
    }
    return "";
}
