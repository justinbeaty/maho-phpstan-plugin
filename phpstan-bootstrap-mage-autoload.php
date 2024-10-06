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

(new ModuleControllerAutoloader('local'))->register();
(new ModuleControllerAutoloader('core'))->register();
(new ModuleControllerAutoloader('community'))->register();
