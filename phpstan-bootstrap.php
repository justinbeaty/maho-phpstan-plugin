<?php
declare(strict_types=1);

use PHPStanMagento1\Autoload\Magento\ModuleControllerAutoloader;

/**
 * @var $container \PHPStan\DependencyInjection\MemoizingContainer
 */
$magentoRootPath = $container->getParameter('magentoRootPath');
if (empty($magentoRootPath)) {
    throw new \Exception('Please set "magentoRootPath" in your phpstan.neon.');
}

if (!defined('BP')) {
    define('BP', $magentoRootPath);
}
if (!defined('MAHO_IS_CHILD_PROJECT')) {
    define('MAHO_IS_CHILD_PROJECT', false);
}

define('staticReflection', true);

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('PS')) {
    define('PS', PATH_SEPARATOR);
}

/**
 * Set include path
 */
$paths = [];
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'core';
$paths[] = BP . DS . 'lib';

$appPath = implode(PS, $paths);
set_include_path($appPath . PS . get_include_path());
include_once "Mage/Core/functions.php";

(new ModuleControllerAutoloader('local'))->register();
(new ModuleControllerAutoloader('core'))->register();
(new ModuleControllerAutoloader('community'))->register();
