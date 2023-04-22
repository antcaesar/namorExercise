<?php
/**
 * Clase para realizar las navegaciones necesarias para obtener datos de una página web.
 */
class navegador {
    // Url del buscador
    private $url;

    /**
     * El contructor, admite un parámetro, la url base del buscador
     */
    function __construct(string $urlBase) {
        $this->url = $urlBase;
    }

    /**
     * Seters y geters para la url del buscador
     */
    function setUrlBase($urlBase) {
        $this->url = $urlBase;
    }

    function getUrlBase() {
        return $this->url;
    }

    /**
     * Función para realizar un curl que permita obtner los datos de la web del buscador.
     */
    function navega($url) {
        //Para gestionar las cookies
        $cookieFile = "cookies.txt";
        if(!file_exists($cookieFile)) {
            $fh = fopen($cookieFile, "w");
            fwrite($fh, "");
            fclose($fh);
        }
    
    
        // Para los distintos datos de la cabecera de la conexión
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $url);
        curl_setopt($ch, CURLOPT_POST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); 
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        //Ejecutamos el curl
        if(!curl_exec($ch)){
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
        else{
            $response = curl_exec($ch); 
        }
        curl_close($ch);
        // Quitamos los carácteres de escape y devolvemos 
        $response = stripslashes($response);
        $response = str_ireplace('\"', '"', $response);
        $result = $response;
        return $result;
    }
}
