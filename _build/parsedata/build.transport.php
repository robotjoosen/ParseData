<?php
/**
 * ParseData build script
 *
 * @package parsedata
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define package */
define('PKG_NAME', 'ParseData');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '0.1.0');
define('PKG_RELEASE', 'pl');

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources = array(
    'root' => $root,
    'build' => $root . PKG_NAME_LOWER . '/',
    'data' => $root . PKG_NAME_LOWER . '/data/',
    'resolvers' => $root . PKG_NAME_LOWER . '/resolvers/'
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once $sources['build'] . 'build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

$sources = array_merge($sources, [
    'chunks' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/elements/chunks/',
    'snippets' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/elements/snippets/',
    'plugins' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/elements/plugins/',
    'lexicon' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/lexicon/',
    'docs' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/docs/',
    'pages' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER . '/elements/pages/',
    'source_assets' => MODX_ASSETS_PATH . 'components/' . PKG_NAME_LOWER,
    'source_core' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER,
]);

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');
$modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

/** create category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

/** add snippets */
$snippets = include $sources['data'] . 'transport.snippets.php';
if (!is_array($snippets)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in snippets.');
} else {
    $category->addMany($snippets);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippets) . ' snippets.');
}
unset($snippets);

/** add chunks */
$chunks = include $sources['data'] . 'transport.chunks.php';
if (!is_array($chunks)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in chunks.');
} else {
    $category->addMany($chunks);
    $modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippets) . ' chunks.');
}
unset($chunks);

/** create category vehicle */
$attr = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Children' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'category',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'Snippets' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                ),
                'Chunks' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                ),
            ),
        ),
        'Snippets' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
        'Chunks' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
        ),
    ),
);
$vehicle = $builder->createVehicle($category, $attr);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to category...');
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$builder->putVehicle($vehicle);

/** Add docs */
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.md'),
    'readme' => file_get_contents($sources['docs'] . 'readme.md'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.md')
));
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

/** Start Packing */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
$builder->pack();

/** Clock time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\nPackage Built. \nExecution time: {$totalTime}\n");

exit ();