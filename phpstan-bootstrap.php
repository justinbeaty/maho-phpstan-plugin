<?php

declare(strict_types=1);

/**
 * Read parameters from .phpstan.neon
 *
 * mahoUseStaticReflection: use static reflection instead of executing app/Mage.php, defaults to true
 * mahoRootDir: path to your project's root dir, i.e. the directory containing the app folder, defaults to cwd
 * magentoRootPath (deprecated): alias of mahoRootDir
 *
 * @var \PHPStan\DependencyInjection\MemoizingContainer $container
*/

define('MAHO_USE_STATIC_REFLECTION', $container->getParameter('mahoUseStaticReflection'));

if (!empty($container->getParameter('mahoRootDir'))) {
    define('MAHO_ROOT_DIR', $container->getParameter('mahoRootDir'));
} elseif (!empty($container->getParameter('magentoRootPath'))) {
    define('MAHO_ROOT_DIR', $container->getParameter('magentoRootPath'));
} else {
    define('MAHO_ROOT_DIR', getcwd());
}

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PS') || define('PS', PATH_SEPARATOR);
defined('BP') || define('BP', MAHO_ROOT_DIR);

if (MAHO_USE_STATIC_REFLECTION) {
    if (file_exists(MAHO_ROOT_DIR . '/app/code/core/Mage/Core/functions.php')) {
        require_once MAHO_ROOT_DIR . '/app/code/core/Mage/Core/functions.php';
    } else {
        require_once MAHO_ROOT_DIR . '/vendor/mahocommerce/maho/app/code/core/Mage/Core/functions.php';
    }
} else {
    if (file_exists(MAHO_ROOT_DIR . '/app/Mage.php')) {
        require_once MAHO_ROOT_DIR . '/app/Mage.php';
    } else {
        require_once MAHO_ROOT_DIR . '/vendor/mahocommerce/maho/app/Mage.php';
    }
}
