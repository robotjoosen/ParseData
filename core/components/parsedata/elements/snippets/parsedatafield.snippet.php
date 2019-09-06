<?php
/**
 * ParseDataField
 * Select field from json array
 * @package parsedata
 * @author Roald Joosen <robotjoosen@gmail.com>
 */

$basePath = $modx->getOption(
    'parsedata.core_path',
    null,
    $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/parsedata/'
);
require_once $basePath . 'vendor/autoload.php';


use RobotJoosen\ParseData;

$parseData = new ParseData($modx, $scriptProperties);
if(isset($scriptProperties['searchKey']) && $scriptProperties['searchValue']) {
    print_r($parseData->findField($scriptProperties['searchKey'], $scriptProperties['searchValue']));
    return;
}
$modx->log(modX::LOG_LEVEL_ERROR, "[ParseDataField] Missing search key or value");
return;