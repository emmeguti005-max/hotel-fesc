<?php

// Umbral de calidad de cobertura para el pipeline de CI.
// Uso: php scripts/umbral-cobertura.php build/coverage/clover.xml 30 [build/junit.xml]
// Lee el reporte Clover de PHPUnit, calcula la cobertura de líneas y termina con
// código 1 (build fallido) si queda por debajo del mínimo.

if ($argc < 3) {
    fwrite(STDERR, "Uso: php scripts/umbral-cobertura.php <clover.xml> <minimo>\n");
    exit(2);
}

[$archivo, $minimo] = [$argv[1], (float) $argv[2]];

if (! is_file($archivo)) {
    fwrite(STDERR, "No existe el reporte de cobertura: {$archivo}\n");
    exit(1);
}

$metricas = simplexml_load_file($archivo)->project->metrics;
$totales = (int) $metricas['statements'];
$cubiertas = (int) $metricas['coveredstatements'];
$cobertura = $totales > 0 ? round($cubiertas / $totales * 100, 2) : 0.0;

$aprobado = $cobertura >= $minimo;
$linea = sprintf(
    'Cobertura de líneas: %d / %d = %.2f %% (mínimo %.2f %%) -> %s',
    $cubiertas,
    $totales,
    $cobertura,
    $minimo,
    $aprobado ? 'APROBADO' : 'FALLA EL UMBRAL'
);

echo $linea.PHP_EOL;

// Anotación de GitHub Actions: queda visible en el resumen de la ejecución.
if (getenv('GITHUB_ACTIONS') === 'true') {
    echo ($aprobado ? '::notice' : '::error').' title=Umbral de cobertura::'.$linea.PHP_EOL;
}

// Resumen visible en la página de la ejecución de GitHub Actions.
if ($resumen = getenv('GITHUB_STEP_SUMMARY')) {
    file_put_contents($resumen, "### Umbral de cobertura\n\n{$linea}\n", FILE_APPEND);
}

// Opcional: resumen de PHPUnit a partir del reporte JUnit (tercer argumento).
if (isset($argv[3]) && is_file($argv[3]) && getenv('GITHUB_ACTIONS') === 'true') {
    $suite = simplexml_load_file($argv[3])->testsuite;
    printf(
        '::notice title=Resumen de PHPUnit::%d pruebas, %d aserciones, %d fallos, %d errores, %d omitidas, %.2f s'.PHP_EOL,
        (int) $suite['tests'],
        (int) $suite['assertions'],
        (int) $suite['failures'],
        (int) $suite['errors'],
        (int) $suite['skipped'],
        (float) $suite['time']
    );
}

exit($aprobado ? 0 : 1);
