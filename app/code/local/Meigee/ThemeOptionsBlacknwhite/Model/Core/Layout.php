<?php
class Meigee_ThemeOptionsBlacknwhite_Model_Core_Layout extends Mage_Core_Model_Layout
{
    protected static $checked_versions = array();

	function __construct()
	{
		$_helper = $this->helper('ajax');
		parent::__construct();
	}
	
	
	
    function checkVersion($version)
    {
        if (!isset(self::$checked_versions[$version]))
        {
            $magento_version =  preg_replace('/[^0-9,]/', '', Mage::getVersion());
            $version_to_check = preg_replace('/[^0-9,]/', '', (string)$version);
            self::$checked_versions[$version] = ($version_to_check == substr($magento_version, 0, strlen($version_to_check)));
        }
        return self::$checked_versions[$version];
    }

	
    function checkVersionUse($node)
    {
        if (isset($node['if_version']))
        {
            $if_version = (string)$node['if_version'];
            if (!$this->checkVersion($if_version))
            {
                return array('is_return'=>true, 'node'=>$this);
            }
        }

        if (isset($node['check_version']) && $node['check_version'] && !empty($node->if_version))
        {
            $is_use_default = true;
            foreach ($node->if_version AS $version_node)
            {
                if ($this->checkVersion((string)$version_node['is']))
                {
                    $node = $version_node;
                    $is_use_default = false;
                    break;
                }
            }
            if ($is_use_default && !empty($node->default))
            {
                $node = $node->default;
            }
        }
        return array('is_return'=>false, 'node'=>$node);
    }
	
	
    protected function _generateAction($node, $parent)
    {
        $version_checker = $this->checkVersionUse($node);
        if ($version_checker['is_return'])
        {
            return $version_checker['node'];
        }
		
		//echo "<pre>"; print_r($node); echo "</pre>";
		
		
        $node = $version_checker['node'];
	 	return parent::_generateAction($node, $parent);
    }


    protected function _generateBlock($node, $parent)
    {
        $version_checker = $this->checkVersionUse($node);
        if ($version_checker['is_return'])
        {
            return $version_checker['node'];
        }
        $node = $version_checker['node'];
		return parent::_generateBlock($node, $parent);
    }

}


























