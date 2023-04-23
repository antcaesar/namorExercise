<?php
/*
* El fin de esta clase es tratar con la base de datos, de forma que no se interactúe directamente con ella, 
* el fin es intentar evitar la inyección de código. 
 */
class database {
    // El objeto de comexión mysqli, para mantener la conexión y realizar las consultas
    private $dbconn;
    // Para almacenar los datos de conexión
    private $servername;
    private $username;
    private $password;
    private $database;

    // El constructor
    function __construct() {
        $this->servername = "mysql:3306";
        $this->username = "root";
        $this->password = "super-secret-password";
        $this->database = "my-wonderful-website";
    }

    /**
     * Función para conectar a la base de datos, devuelve True si tiene éxito y un mensaje de error en caso contrario.
     */
    function conecta() {
        $conn = mysqli_connect($this->servername, $this->username, $this->password, $this->database);

        if (!$conn) {
            die("Falló la conexión: " . mysqli_connect_error());
        }
        $this->dbconn = $conn;
        //echo "Conexión realizada correctamente</br>";
        return true;
    }

    /**
     * Función para verificar si la búsqueda ya ha sido realizada anteriormente, 
     * devuelve true en caso de que ya esté realizada esa búsqueda, y false en caso contrario. 
     */
    function checkSearched($rawquery) {
        $query = strtolower($rawquery);
        $sql = "SELECT * from searched where query = '$query'";
        $res = $this->dbconn->query($sql);
        if ($res->num_rows>0) {
            return true;
        } 
        else {
            return false;
        }
    }

    /**
     * Función para añadir una búsqueda al historial, y no realizala dos veces.
     * Devuelve true en caso de éxito, y false en caso contrario. 
     */
    function addSearch($rawquery) {
        $query = strtolower($rawquery);
        $sql = "INSERT INTO searched (query) VALUES ('$query') ";
        $res = $this->dbconn->query($sql);
        if ($res === TRUE) {
            return true;
        } 
        else {
            return false;
        }
    }

    /**
     * Función para obtener el número de veces que un dominio ha aparecido en las diferentes búsquedas.
     * Si no está, devuelve 0, en otro caso, devuelve un entero positivo. 
     * Parámetros:
     * - $domain : El dominio, sin https:// 
     */
    function getDomainRelevance($domain) {
        $sql = "SELECT * from linksRelevancy where urlDomain = '$domain'";
        $res = $this->dbconn->query($sql);
        if ($res->num_rows>0) {
            $response = "";
            while($row = mysqli_fetch_assoc($res)) {
                $response = $row["ocurrences"];
            }
            return $response;
        } 
        else {
            return 0;
        }
    }

    /**
     * Función para actualizar el número de veces que un dominio aparece en las distintas búsquedas, 
     * los parámetros son:
     * - $domain El dominio, sin https://
     * - $newRelevance: el número de veces que aparece en las búsquedas.
     * Devuelve:
     * - True en caso de éxito, false en otro caso. 
     */
    function setDomainRelevance($domain, $newRelevance) {
        // Primero buscamos si está ya en la tabla
        $sql = "SELECT * from linksRelevancy where urlDomain = '$domain'";
        $res = $this->dbconn->query($sql);
        //Si está lo actualizamos y si no lo insertamos.
        if ($res->num_rows>0) {
            $sql2 = "UPDATE linksRelevancy SET ocurrences=$newRelevance WHERE urlDomain = '$domain'";
        } else {
            $sql2 = "INSERT INTO linksRelevancy (urlDomain, ocurrences) VALUES ('$domain', $newRelevance)";
        }
        $res2 = $this->dbconn->query($sql2);
        if ($res2==TRUE) {
            return $res2;
        } 
        else {
            return false;
        }
    }

    /**
     * Función para obtener la lista completa de los dominios y las veces que aparecen en las búsquedas.
     * Devuelve:
     * - Un array, con los dominios como clave, y la cantidad de veces que aparen como valor.
     */
    function getAllDomains() {
        $sql = "SELECT * from linksRelevancy ORDER BY ocurrences DESC";
        $res = $this->dbconn->query($sql);
        if ($res->num_rows>0) {
            $response = array();
            while($row = mysqli_fetch_assoc($res)) {
                $response [$row["urlDomain"]] = $row["ocurrences"];
            }
            return $response;
        } 
        else {
            return false;
        }
    }

    /**
     * Función para cerrar las conexiones a la base de datos.
     */
    function desconecta() {
        $this->dbconn->close();
    }

    /**
     * Función para escapar una cadena y hacerla segura para incluírla en la base de datos.
     * Parámetros:
     *  - Una cadena sin escapar
     * Devuelve:
     *  - Una cadena segura con los símbolos especiales escapados
     */
    function escapeString($querySearch) {
        $resul = mysqli_real_escape_string($this->dbconn, $querySearch);
        return $resul;
    }

    /**
     * Función para obtener las búsquedas realizadas
     * Devuelve:
     *  - Un array con las búsquedas, y false en caso de que no exista ninguna.
     */
    function getSearched() {
        $sql = "SELECT * from searched";
        $res = $this->dbconn->query($sql);
        if ($res->num_rows>0) {
            $response = array();
            while($row = mysqli_fetch_assoc($res)) {
                $response [] = $row["query"];
            }
            return $response;
        } 
        else {
            return false;
        }
    }
}