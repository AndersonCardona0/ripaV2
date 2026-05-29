<?php
function cargarVariablesEntorno($ruta) {
    
    if (!file_exists($ruta)) {
        return;
    }

    
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        
        if (strpos(trim($linea), '#') === 0) continue;

        if (strpos($linea, '=') !== false) {
            list($nombre, $valor) = explode('=', $linea, 2);
            $nombre = trim($nombre);
            $valor = trim($valor);

            if (!array_key_exists($nombre, $_ENV)) {
                $_ENV[$nombre] = $valor;
                putenv("{$nombre}={$valor}"); 
            }
        }
    }
}

$rutaEnv = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
cargarVariablesEntorno($rutaEnv);