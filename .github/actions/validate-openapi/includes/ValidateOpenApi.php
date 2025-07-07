<?php
require_once __DIR__ . '/ArrayUtils.php';

class ValidateOpenApi
{
    private ArrayUtils $arrayUtils;
    private $versionUrl;

    private const RESET = "\e[0m";

    public function __construct()
    {
        $this->arrayUtils = new ArrayUtils();
    }

    public function validateServerUrl($serverUrl, $openapiApiType, $repositoryApiType): array
    {
        $result = [
            'isValid' => true,
            'message' => '',
        ];

        if (empty($serverUrl)) {
            $result['message'] = "servers[0].url es requerido";
            $result['isValid'] = false;
            return $result;
        }
        
        switch ($openapiApiType) {
            case 'UX':
                $repositoryApiType = 'channel';
                $codeChannel = '';

                $patternOne = '/^\/channel\/([a-zA-Z0-9]{4})\/v(\d+)$/';
                $patternTwo = '#^/channel-([a-z0-9]{4})-([a-z0-9-]+)/v([0-9]+)$#';

                // Patrón 1: /channel/{app-code}/v{version}
                if(preg_match($patternOne, $serverUrl, $matches)){
                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[2]) ? $explode[2] : '';
                    $apiTyeServerUrl = !empty($explode[0]) ? $explode[0] : '';
                    
                    $codeChannel = $matches[1];
                    $versionMajor = $matches[2];

                    $result['message']  = "✅ URL válida (Patrón 1 - /channel/{app-code}/v1):" . self::RESET . "\n";
                    $result['message'] .= "  App Code:". $codeChannel. self::RESET . "\n";
                    $result['message'] .= "  Versión: v$versionMajor\n". self::RESET . "\n";;
                    
                    return $result;

                // Patrón 2: /channel-{app-code}-{application-context}/v{version}
                } elseif(preg_match($patternTwo, $serverUrl, $matches)){

                    $appCode = $matches[1];
                    $applicationContext = $matches[2];
                    $version = $matches[3];

                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[1]) ? $explode[1] : '';
                    $apiTyeServerUrl = !empty($explode[0]) ? $explode[0] : '';
                    
                    $result['message']  =  "✅ URL válida (Patrón 2 - Compuesto):" . self::RESET . "\n";
                    $result['message'] .=  "  App Code: $appCode". self::RESET . "\n";
                    $result['message'] .=  "  Application Context: $applicationContext". self::RESET . "\n";
                    $result['message'] .=  "  Versión: v$version". self::RESET . "\n";

                } else {

                    $result['message']  = "❌ El serverUrl '$serverUrl' no es válido para el tipo de API UX.". self::RESET . "\n";
                    $result['message'] .= "Debe seguir el patrón /channel/{app-code}/v1 o /channel-{app-code}-{application-context}/v{version}". self::RESET . "\n";
                    $result['isValid']  = false;
                    return $result;
                }

                
                break;
            case 'BS':
                $patternOne   = '#^((?:[a-z0-9]+)(?:-[a-z0-9]+)*)/((?:[a-z0-9]+)(?:-[a-z0-9]+)*)/v([1-9][0-9]*)$#';
                $patternTwo   = '#^/business-(([a-z0-9]+)(-[a-z0-9]+)*)/v([1-9][0-9]*)$#';
                $patternThree = '#^/business-([a-z0-9]{4})-(([a-z0-9]+)(-[a-z0-9]+)*)/v([1-9][0-9]*)$#';
                $patternFour  = '#^/business-sd(([a-z0-9]+)(-[a-z0-9]+)*)/v([1-9][0-9]*)$#';
                $patternFive  = '#^/business-sd(([a-z0-9]+)(-[a-z0-9]+)*)-fsd(([a-z0-9]+)(-[a-z0-9]+)*)/v([1-9][0-9]*)$#';
                
                // Patrón 1: /{business-domain}/{service-domain}/v{version}
                if(preg_match($patternOne, $serverUrl, $matches)){
                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[2]) ? $explode[2] : '';
                    
                    $versionMajor = $matches[2];

                    $businessDomain = $matches[1];
                    $serviceDomain  = $matches[2];
                    $versionMajor   = $matches[3];

                    $result['message']  = "✅ URL válida (Patrón 1 - /{business-domain}/{service-domain}/v{version}):" . self::RESET . "\n";
                    $result['message'] .= "  Business Domain: $businessDomain" . self::RESET . "\n";
                    $result['message'] .= "  Service Domain: $serviceDomain" . self::RESET . "\n";
                    $result['message'] .= "  Versión: v$versionMajor\n" . self::RESET . "\n";

                    return $result;

                // Patrón 2: /business-{service-domain}/v{version}
                } elseif(preg_match($patternTwo, $serverUrl, $matches)){
                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[1]) ? $explode[1] : '';
                    
                    $serviceDomain = $matches[1]; // Dominio completo
                    $versionMajor  = $matches[4]; // Versión numérica

                    $result['message']  = "✅ URL válida (Patrón 2 - /business-{service-domain}/v{version}):" . self::RESET . "\n";
                    $result['message'] .= "  Service Domain: $serviceDomain" . self::RESET . "\n";
                    $result['message'] .= "  Versión: v$versionMajor\n" . self::RESET . "\n";
                    return $result;

                // Patrón 3: /business-{app-code}-{service-domain}/v{version}
                } elseif(preg_match($patternThree, $serverUrl, $matches)){
                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[1]) ? $explode[1] : '';
                    
                    $serviceDomain = $matches[2]; // Dominio completo
                    $versionMajor  = $matches[5]; // Versión numérica
                    $appCode       = $matches[1]; // Código de la aplicación

                    $result['message']  = "✅ URL válida (Patrón 3 - /business-{app-code}-{service-domain}/v{version}):" . self::RESET . "\n";
                    $result['message'] .= "  App Code: {$matches[1]}" . self::RESET . "\n";
                    $result['message'] .= "  Service Domain: $serviceDomain" . self::RESET . "\n";
                    $result['message'] .= "  Versión: v$versionMajor\n" . self::RESET . "\n";

                } elseif(preg_match($patternFour, $serverUrl, $matches)){
                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[1]) ? $explode[1] : '';
                    
                    $serviceDomain = $matches[1]; // Dominio completo
                    $versionMajor  = $matches[4]; // Versión numérica

                    $result['message']  = "✅ URL válida (Patrón 4 - /business-sd-{service-domain}/v{version}):" . self::RESET . "\n";
                    $result['message'] .= "  Service Domain: $serviceDomain" . self::RESET . "\n";
                    $result['message'] .= "  Versión: v$versionMajor\n" . self::RESET . "\n";

                } elseif(preg_match($patternFive, $serverUrl, $matches)){
                    $explode = explode('/', $serverUrl);
                    $this->versionUrl = !empty($explode[1]) ? $explode[1] : '';
                    
                    $serviceDomain = "{$matches[2]}-{$matches[4]}"; // Dominio completo
                    $versionMajor  = $matches[7]; // Versión numérica

                    $result['message']  = "✅ URL válida (Patrón 5 - /business-sd-{service-domain}-fsd-{fsd-domain}/v{version}):" . self::RESET . "\n";
                }
                
                break;
            case 'CR':

                break;
            default:
                $result['message'] = "El tipo de Api '$openapiApiType' es inválido. Debe ser UX, BS o CR";
                $result['isValid'] = false;
                return $result;
        }
        

        return $result;
    }


    /**
     * Elimina claves generales y específicas de un array, incluyendo claves anidadas
     */
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