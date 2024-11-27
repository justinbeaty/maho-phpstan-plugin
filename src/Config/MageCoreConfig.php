<?php declare(strict_types=1);

/**
 * @category   Maho
 * @package    PHPStanPlugin
 * @copyright  Maho Contributors https://mahocommerce.com
 * @license    https://opensource.org/license/mit
 */

namespace Maho\PHPStanPlugin\Config;

use Mage;
use Mage_Core_Model_Config;

final class MageCoreConfig
{
    public function getConfig(): Mage_Core_Model_Config
    {
        return Mage::app()->getConfig();
    }

    public function getClassNameConverterFunction(string $class, string $method): ?callable
    {
        switch ("$class::$method") {
        case 'Mage::getModel':
        case 'Mage::getSingleton':
            return fn ($alias) => $this->getConfig()->getModelClassName($alias);
        case 'Mage::getResourceModel':
        case 'Mage::getResourceSingleton':
            return fn ($alias) => $this->getConfig()->getResourceModelClassName($alias);
        case 'Mage_Core_Model_Layout::createBlock':
        case 'Mage_Core_Model_Layout::getBlockSingleton':
            return fn ($alias) => $this->getConfig()->getBlockClassName($alias);
        case 'Mage::helper':
        case 'Mage_Core_Model_Layout::helper':
        case 'Mage_Core_Block_Abstract::helper':
            return fn ($alias) => $this->getConfig()->getHelperClassName($alias);
        }
        return null;
    }
}
