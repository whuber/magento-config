<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2016 Walter Huber
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Magento Log Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Config extends Mage_Shell_Abstract
{

    /**
     * Additional initialize instruction
     *
     * @return Mage_Shell_Config
     */
    protected function _construct()
    {
        if ($this->getArg('developer')) {
            Mage::setIsDeveloperMode(true);
        }
        return $this;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        $mode = $this->getArg('mode');

        $files      = glob(Mage::getBaseDir('etc'). DS . 'config' . DS . '*.xml');
        $localFile  = Mage::getBaseDir('etc'). DS . 'config' . DS . 'local.xml';
        $modeFile   = Mage::getBaseDir('etc'). DS . 'config' . DS . 'mode-' . $mode . '.xml';

        if (!is_file($localFile) || !is_file($modeFile)) {
            die("Local file or mode file doesn't exist!\n");
        }

        $config = new Mage_Core_Model_Config_Base();
        $config->loadDom($this->_getBaseDoc($mode));

        foreach ($files as $file) {
            if ($file == $localFile
                || false !== strpos($file, 'mode-')
                || false !== strpos($file, 'local-')
            ) {
                continue;
            }
            echo("File: $file\n");
            $merge = clone $config;
            $merge->loadFile($file);
            $config->extend($merge);
        }
        $merge = clone $config;
        $merge->loadFile($localFile);
        $config->extend($merge);

        $merge = clone $config;
        $merge->loadFile($modeFile);
        $config->extend($merge);

        $source = Mage::getBaseDir('etc'). DS . 'local.xml';
        $config->getNode()->asNiceXml($source, false);
    }

    protected function _getBaseDoc($mode)
    {
        $xmlDoc = new DOMDocument('1.0');
        $conf = $xmlDoc->createElement('config');
        $xmlDoc->appendChild($conf);

        $modeNode = $xmlDoc->createElement('mode');
        $modeNode->appendChild($xmlDoc->createTextNode($mode));
        $conf->appendChild($modeNode);

        $dateNode = $xmlDoc->createAttribute('created_at');
        $dateNode->value = date('Y-m-d H:i:s');
        $modeNode->appendChild($dateNode);

        return $xmlDoc;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f config.php -- [options]

  developer         Flag for developer mode
  help              This help

USAGE;
    }
}

$shell = new Mage_Shell_Config();
$shell->run();
