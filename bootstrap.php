<?php
/**
 * Maho
 *
 * @category   Maho
 * @package    PHPStanPlugin
 * @copyright  Copyright © Maho (https://mahocommerce.com)
 * @license    https://opensource.org/license/mit The MIT License
 */

declare(strict_types=1);

/** @var \PHPStan\DependencyInjection\MemoizingContainer $container */
if (!empty($container->getParameter('mahoRootDir'))) {
    define('MAHO_ROOT_DIR', $container->getParameter('mahoRootDir'));
} elseif (!empty($container->getParameter('magentoRootPath'))) {
    define('MAHO_ROOT_DIR', $container->getParameter('magentoRootPath'));
} else {
    define('MAHO_ROOT_DIR', getcwd());
}

if (file_exists(MAHO_ROOT_DIR . '/app/bootstrap.php')) {
    require_once MAHO_ROOT_DIR . '/app/bootstrap.php';
    require_once MAHO_ROOT_DIR . '/app/Mage.php';
} else {
    require_once MAHO_ROOT_DIR . '/vendor/mahocommerce/maho/app/bootstrap.php';
    require_once MAHO_ROOT_DIR . '/vendor/mahocommerce/maho/app/Mage.php';
}
