<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 *******************************************************************
 * @category   Belvg
 * @package    Belvg_Sizes
 * @copyright  Copyright (c) 2010 - 2014 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
class Belvg_Sizes_Helper_Data extends Mage_Core_Helper_Data 
{
    
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('sizes/settings/enabled');
    }    
    
    public function getAttrToAttach()
    {
        $tmp = explode(',', Mage::getStoreConfig('sizes/settings/attributes'));
        return json_encode($tmp);
    }
    
    public function getOptToAttach()
    {
        $tmp = explode(',', Mage::getStoreConfig('sizes/settings/options'));
        return json_encode($tmp);
    }
    
    public function getUnitSizes()
    {
        return Mage::getStoreConfig('sizes/settings/unit_sizes');
    }
    
    public function getPopupConf()
    {   
        $result = '{';
        
        //settings for margin
        $tmp = Mage::getStoreConfig('sizes/popup/margin');
        $result .= 'margin:[' . $tmp . ',' . $tmp . ',' . $tmp . ',' . $tmp . '],';
        
        //settings for padding
        $tmp = Mage::getStoreConfig('sizes/popup/padding');
        $result .= 'padding:[' . $tmp . ',' . $tmp . ',' . $tmp . ',' . $tmp . '],';
        
        //settings for close button
        $result .= 'closeBtn:';
        if (Mage::getStoreConfig('sizes/popup/close_btn')) {
            $result .= 'true,';
        } else {
            $result .= 'false,';
        }
        
        //settings events effect
        $tmp = Mage::getStoreConfig('sizes/popup/events_effect');
        $result .= "openEffect:'" . $tmp . "',closeEffect:'" . $tmp . "',prevEffect:'" . $tmp . "',nextEffect:'" . $tmp . "',";
        //settings events speed
        $tmp = Mage::getStoreConfig('sizes/popup/events_speed');
        $result .= "openSpeed:'" . $tmp . "',closeSpeed:'" . $tmp . "',prevSpeed:'" . $tmp . "',nextSpeed:'" . $tmp . "',";
        
        $result .= 'helpers:{overlay:{locked:false}}';
        
        $result .= '}';
        return $result;
    }
    
    public function getMessage($mess = 'start')
    {   
        return $tmp = Mage::getStoreConfig('sizes/messages/' . $mess);
    }
    
    public function getCatImageUrl($catId, $store = FALSE)
    {
        $extensions = $this->getImageExtensions();
        if (!$store) {
            $stores = array(Mage::app()->getStore()->getStoreId(), 0);
        } else {
            $stores = array((int)$store);
        }
        
        $url = FALSE;
        foreach ($stores as $store) {
            foreach ($extensions as $extension) {
                if (is_file(Mage::getBaseDir('media') . DS . $this->getMediaDir() . DS . $catId . '-' . $store . '.' . $extension)) {
                    $url = Mage::getBaseUrl('media') . $this->getMediaDir() . '/' . $catId . '-' . $store . '.' . $extension;
                }
             
                if ($url) {
                    break;
                }
            }
        
            if ($url) {
                break;
            }
        }
        
        if ($url) {
            return $url;
        } else {
            return Mage::getBaseUrl('media') . $this->getMediaDir() . '/default.png';
        }

    }
    
    public function getImageWidth()
    {
        return 230;
    }
    
    public function getImageHeight()
    {
        return 300;
    }
    
    public function getMediaDir()
    {
        return 'sizes';
    }
    
    public function getImageExtensions()
    {
        return array('png', 'gif', 'jpg');
    }
    
    public function isChartsTab()
    {
        return Mage::getStoreConfigFlag('sizes/popup/charts_tab');
    }
    
    public function getChartsDirection()
    {
        return Mage::getStoreConfig('sizes/popup/charts_direction');
    }
    
    public function saveSizeInSession($dim_id, $value)
    {
        if (Mage::getStoreConfigFlag('sizes/settings/save_in_session')) {
            $values = Mage::getSingleton('core/session')->getSizesValue();
            $values[$dim_id] = $value;
            Mage::getSingleton('core/session')->setSizesValue($values);
        } else {
            Mage::getSingleton('core/session')->setSizesValue(NULL);
        }
    }
   
    public function getStoredValue($dim_id, $limits)
    {
        $val = Mage::getSingleton('core/session')->getSizesValue();
        if (isset($val[$dim_id])) {
            return $val[$dim_id];
        }
        
        return (int)($limits['min'] + $limits['max'])/2;
    }
    
}