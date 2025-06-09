<?php

class JsonProcessor
{
    private $RED = "\033[31m";
    private $YELLOW = "\033[33m";
    private $RESET = "\e[0m";
    
    /**
     * Carga y limpia un archivo JSON
     */
    public function loadAndCleanJson(string $filePath): array
    {
        $json = file_get_contents($filePath);
        if (!$json) {
            throw new Exception("No se pudo leer $filePath o está vacío.");
        }
        
        return $this->cleanAndDecode($json);
    }
    
    /**
     * Limpia y decodifica JSON
     */
    private function cleanAndDecode(string $json): array
    {
        try {
            // Eliminar posibles caracteres BOM u otros caracteres invisibles
            $json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json);
            
            // Manejar expresiones regulares y escapes
            $json = preg_replace('/\\\\\d/', '\\\\\\\\d', $json);
            
            // Intentar decodificar
            $array = json_decode($json, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            
            if ($array === null) {
                throw new Exception(json_last_error_msg());
            }
            
            return $array;
            
        } catch (Exception $e) {
            echo $this->RED . "Error procesando JSON: " . $e->getMessage() . $this->RESET . "\n";
            echo $this->YELLOW . "Contenido del JSON:\n$json\n" . $this->RESET;
            throw $e;
        }
    }
}