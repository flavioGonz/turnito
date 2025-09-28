<?php
// Docs/generate_docs.php
// Genera un .md por cada archivo del proyecto (no sobreescribe .md ya existentes en Docs/).
// Uso: php Docs/generate_docs.php

$docsDir = __DIR__;
$root = dirname($docsDir); // project root

$skip = [
  realpath($docsDir),
  realpath($root . DIRECTORY_SEPARATOR . '.git') ?: null,
  realpath($root . DIRECTORY_SEPARATOR . 'vendor') ?: null,
];
$skip = array_filter($skip);

$textExt = ['php','js','css','md','json','txt','sql','html','htm','xml','csv','scss','less','yml','yaml','ini','apache','bat','ps1'];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
$count = 0; $created = 0;
foreach ($it as $file) {
    $path = $file->getPathname();
    // skip directories inside Docs or the Docs folder itself
    foreach ($skip as $s) { if (strpos($path, $s) === 0) continue 2; }
    // relative path
    $rel = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
    // normalize doc name: replace non-alnum with underscore
    $docName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $rel) . '.md';
    $docPath = $docsDir . DIRECTORY_SEPARATOR . $docName;
    $count++;
    if (file_exists($docPath)) continue; // no sobreescribir

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $title = basename($rel);
    $content = "# $title\n\nRuta: $rel\nTipo: " . ($ext ?: 'binario/desconocido') . "\n\n";

    if (in_array($ext, $textExt)) {
        $content .= "Resumen:\n- Archivo de texto/código (se muestran las primeras líneas).\n\n";
        $head = '';
        $fh = @fopen($path, 'r');
        if ($fh) {
            $lines = 0;
            while (!feof($fh) && $lines < 200) { $head .= fgets($fh); $lines++; }
            fclose($fh);
        }
        // truncate head for safety
        if ($head !== '') {
            $preview = substr($head, 0, 8000);
            $content .= "Primeras líneas:\n\n```
" . $preview . "\n```
\n";
        } else {
            $content .= "(No se pudo leer el archivo o está vacío)\n\n";
        }
        $content .= "Notas y sugerencias:\n- Añadir descripción específica si hace falta.\n";
    } else {
        // binary
        $size = @filesize($path);
        $content .= "Resumen:\n- Archivo binario (no se muestra el contenido).\n\n";
        if ($size !== false) $content .= "Tamaño: $size bytes\n\n";
        $content .= "Notas:\n- Archivo media/backup. Documentar su propósito si es necesario.\n";
    }

    @file_put_contents($docPath, $content);
    $created++;
    echo "WROTE: $docName\n";
}

echo "\nDone. Scanned: $count files. Created: $created docs in $docsDir\n";

?>