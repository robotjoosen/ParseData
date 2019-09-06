<?php
/**
 * Add chunks to build
 *
 * @package parsedata
 * @subpackage build
 */

$chunks = [];
//$chunk_properties = [
//    [
//        'name' => 'parsedata.rowtpl',
//        'description' => 'Parse JSON into a template',
//        'snippet' => getSnippetContent($sources['source_core'] . '/elements/chunks/parsedata.snippet.php')
//    ],
//    [
//        'name' => 'parsedata.outertpl',
//        'description' => 'Get single field from 2D JSON string',
//        'snippet' => getSnippetContent($sources['source_core'] . '/elements/chunks/parsefield.snippet.php')
//    ]
//];
//$properties_count = count($chunk_properties);
//for ($i = 1; $i <= $properties_count; $i++) {
//    $chunks[$i] = $modx->newObject('modSnippet');
//    $chunks[$i]->fromArray($chunk_properties[$i]);
//    if (isset($chunk_properties[$i]['properties'])) {
//        $properties = include $chunk_properties[$i]['properties'];
//        $chunks[$i]->setProperties($properties);
//    }
//}
//unset($properties_count);
//unset($chunk_properties);

return $chunks;