<?php declare(strict_types=1);

namespace Maho\PHPStanPlugin\Config;

use Mage;
use Mage_Core_Model_Config;

final class MageCoreConfig
{
    public function getConfig(): Mage_Core_Model_Config
    {
        return Mage::app()->getConfig();
    }

    /**
     * @return ?callable(string): (string|false)
     */
    public function getClassNameConverterFunction(string $class, string $method): ?callable
    {
        switch ("$class::$method") {
        case 'Mage::getModel':
        case 'Mage::getSingleton':
            return fn (string $alias) => $this->getConfig()->getModelClassName($alias);
        case 'Mage::getResourceModel':
        case 'Mage::getResourceSingleton':
            return fn (string $alias) => $this->getConfig()->getResourceModelClassName($alias);
        case 'Mage_Core_Model_Layout::createBlock':
        case 'Mage_Core_Model_Layout::getBlockSingleton':
            return fn (string $alias) => $this->getConfig()->getBlockClassName($alias);
        case 'Mage::helper':
        case 'Mage_Core_Model_Layout::helper':
        case 'Mage_Core_Block_Abstract::helper':
            return fn (string $alias) => $this->getConfig()->getHelperClassName($alias);
        case 'Mage_Admin_Model_User::_helper':
        case 'Mage_Adminhtml_Controller_Rss_Abstract::_helper':
        case 'Mage_Api_Model_User::_helper':
        case 'Mage_Customer_AccountController::_helper':
        case 'Mage_Customer_Model_Customer::_helper':
        case 'Mage_Rss_Controller_Abstract::_helper':
        case 'Mage_SalesRule_Model_Validator::_helper':
        case 'Mage_Weee_Helper_Data::_helper':
        case 'Mage_Weee_Model_Config_Source_Fpt_Tax::_helper':
            // Deprecated _helper calls
            return fn (string $alias) => $this->getConfig()->getHelperClassName($alias);
        }
        return null;
    }
}
