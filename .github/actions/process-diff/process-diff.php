<?php
    // Incluir archivos auxiliares
    require_once __DIR__ . '/includes/JsonProcessor.php';
    require_once __DIR__ . '/includes/DiffAnalyzer.php';

    $RED = "\033[31m";
    $GREEN = "\033[32m";
    $YELLOW = "\033[33m";
    $RESET = "\e[0m";

    // Obtener argumentos
    $diffFile = $argv[1] ?? 'diff-result.json';
    $apiType = $argv[2] ?? '';
    $openapiType = $argv[3] ?? '';

    echo "$YELLOW Procesando archivo: $diffFile$RESET\n";
    echo "$YELLOW Tipo de API: $apiType$RESET\n";
    echo "$YELLOW Tipo de API(Openapi): $openapiType$RESET\n";

    try {
        // 1. Procesar JSON
        $jsonProcessor = new JsonProcessor();
        $array = $jsonProcessor->loadAndCleanJson($diffFile);

        // 4. Analizar diferencias
        $analyzer = new DiffAnalyzer();
        //$result = $analyzer->removedKeysGeneral($array);
        //$result = $analyzer->removedKeysByApiType($result, $openapiType);
        $result = $analyzer->execute($array, $openapiType);
        echo json_encode($result['content-diff']) ." $RESET\n";

        // 5. Determinar resultado
        if (!empty($result['is_fix'])) {
            echo "$GREEN ✅ SOLICITUD TIPO FIX VÁLIDA$RESET\n";
            file_put_contents(getenv('GITHUB_OUTPUT'), "is_fix=true\n", FILE_APPEND);
            file_put_contents(getenv('GITHUB_OUTPUT'), "is_snapshot=false\n", FILE_APPEND);
        } elseif(!empty($result['is_snapshot'])) {
            echo "$GREEN ✅ SOLICITUD TIPO FIX SANPSHOT VÁLIDA$RESET\n";
            file_put_contents(getenv('GITHUB_OUTPUT'), "is_fix=false\n", FILE_APPEND);
            file_put_contents(getenv('GITHUB_OUTPUT'), "is_snapshot=true\n", FILE_APPEND);
        } else {
            echo "$RED ❌ SOLICITUD TIPO FIX INVÁLIDA$RESET\n";
            file_put_contents(getenv('GITHUB_OUTPUT'), "is_fix=false\n", FILE_APPEND);
            file_put_contents(getenv('GITHUB_OUTPUT'), "is_snapshot=false\n", FILE_APPEND);
        }
        
        echo "$GREEN 🎉 Procesamiento completado$RESET\n";
        
    } catch (Exception $e) {
        echo "$RED ❌ Error: " . $e->getMessage() . "$RESET\n";
        exit(1);
    }
    
?>