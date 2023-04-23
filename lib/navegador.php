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
    
    
        // Para los distintos datos de la cabecera de la conexión
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");

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
