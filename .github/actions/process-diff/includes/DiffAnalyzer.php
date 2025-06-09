<?php
require_once __DIR__ . 'ArrayUtils.php';

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

}
?>