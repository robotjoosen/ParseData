<?php
/**
 * Add snippets to build
 *
 * @package parsedata
 * @subpackage build
 */

$snippets = [];
$snippet_properties = [
    [
        'name' => 'ParseData',
        'description' => 'Parse JSON into a template',
        'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/parsedata.snippet.php')
    ],
    [
        'name' => 'ParseDataField',
        'description' => 'Get single field from 2D JSON string',
        'snippet' => getSnippetContent($sources['source_core'] . '/elements/snippets/parsedatafield.snippet.php')
    ]
];

$i = 1;
foreach($snippet_properties as $snippet_property) {
    $snippets[$i] = $modx->newObject('modSnippet');
    $snippets[$i]->fromArray($snippet_property);
//    $snippets[$i]->set('id', $i);
    if (isset($snippet_property['properties'])) {
        $properties = include $snippet_property['properties'];
        $snippets[$i]->setProperties($properties);
    }
    $i++;
}
unset($snippet_properties);

return $snippets;