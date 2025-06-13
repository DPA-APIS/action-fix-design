<?php
require_once __DIR__ . '/ArrayUtils.php';

class DiffAnalyzer
{
    private ArrayUtils $arrayUtils;

    public function __construct()
    {
        $this->arrayUtils = new ArrayUtils();
    }

    /**
     * Elimina claves generales y específicas de un array, incluyendo claves anidadas
     */
    public function removedKeysGeneral(array $array): array
    {
        // Eliminar claves generales
        $generalKeys = explode("\n", trim(getenv('OPENAPI_IGNORE_KEYS')));
        foreach ($generalKeys as $key) {
            $array = $this->arrayUtils->removeKeysRecursive($array, trim($key));
        }
        
        $array = $this->arrayUtils->cleanEmptyArraysRec($array);

        return $array;
    }
    /**
     * Elimina claves específicas por tipo de API y limpia arrays vacíos
     *
     * @param array $array Array a procesar
     * @param string $openapiType Tipo de API (por ejemplo, "UX", "BS", "CR")
     * @return array Array procesado
     */
    public function removedKeysByApiType(array $array, String $openapiType): array
    {
        // Eliminar claves específicas por tipo de API
        if ($openapiType) {
            $apiKeys = explode("\n", trim(getenv('OPENAPI_IGNORE_KEYS_' . strtoupper($openapiType))));
            foreach ($apiKeys as $key) {
                $array = $this->arrayUtils->removeKeysRecursive($array, trim($key));
            }
        }

        $array = $this->arrayUtils->cleanEmptyArraysRec($array);

        return $array;
    }
    /**
     * Elimina claves específicas por tipo de API y versión, y limpia arrays vacíos
     *
     * @param array $array Array a procesar
     * @param string $openapiType Tipo de API (por ejemplo, "UX", "BS", "CR")
     * @return array Array procesado
     */
    public function removedKeysSnapshot(array $array, String $openapiType): array
    {
        // Eliminar claves específicas por tipo de API y versión
        if ($openapiType) {
            $apiKeys = explode("\n", trim(getenv('OPENAPI_REMOVE_KEYS_SNAPSHOT_' . strtoupper($openapiType))));
            foreach ($apiKeys as $key) {
                $array = $this->arrayUtils->removeKeysRecursive($array, trim($key));
            }
        }

        $array = $this->arrayUtils->cleanEmptyArraysRec($array);

        return $array;
    }

    public function execute(array $array, String $openapiType): array
    {
        $result = [
            'is_fix' => false,
            'is_snapshot' => false,
            'content-diff' => [],
        ];
        // Eliminar claves generales
        $array = $this->removedKeysGeneral($array);
        
        // Eliminar claves específicas por tipo de API
        $array = $this->removedKeysByApiType($array, $openapiType);

        if(empty($array)){
            $result['is_fix'] = true;
            return $result;
        } 
        
        // Eliminar claves específicas por tipo de API y versión
        $array = $this->removedKeysSnapshot($array, $openapiType);
        if(empty($array)){
            $result['is_snapshot'] = true;
            return $result;
        } 
        $result['content-diff'] = $array;

        return $result;
    }
}
?>