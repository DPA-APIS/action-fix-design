<?php
require_once __DIR__ . '/ArrayUtils.php';

class DiffAnalyzer
{
    private ArrayUtils $arrayUtils;

    public function __construct()
    {
        $this->arrayUtils = new ArrayUtils();
    }

    public function removedKeysGeneral(array $array): array
    {
        // Eliminar claves generales
        $generalKeys = explode("\n", trim(getenv('OPENAPI_IGNORE_KEYS')));
        foreach ($generalKeys as $key) {
            if (trim($key)) {
                $array = $this->arrayUtils->removeKeysRecursive($array, trim($key));
            }
        }
        
        $array = $this->arrayUtils->cleanEmptyArraysRec($array);

        return $array;
    }

    public function removedKeysByApiType(array $array, String $apiType): array
    {
        // Eliminar claves específicas por tipo de API
        if ($this->apiType) {
            $apiKeys = explode("\n", trim(getenv('OPENAPI_IGNORE_KEYS_' . strtoupper($this->apiType))));
            foreach ($apiKeys as $key) {
                if (trim($key)) {
                    $array = $this->arrayUtils->removeKeysRecursive($array, trim($key));
                }
            }
        }

        $array = $this->arrayUtils->cleanEmptyArraysRec($array);

        return $array;
    }

}
?>