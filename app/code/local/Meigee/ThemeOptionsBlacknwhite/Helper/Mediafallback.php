<?php
/**
 * Magento
 *
 * @author    Meigeeteam http://www.meaigeeteam.com <nick@meaigeeteam.com>
 * @copyright Copyright (C) 2010 - 2014 Meigeeteam
 *
 */
 
/**
 * Class implementing the media fallback layer for swatches
 */
class Meigee_ThemeOptionsBlacknwhite_Helper_Mediafallback extends Mage_ConfigurableSwatches_Helper_Mediafallback
{
    /**
     * For given product, get configurable images fallback array
     * Depends on following data available on product:
     * - product must have child attribute label mapping attached
     * - product must have media gallery attached which attaches and differentiates local images and child images
     * - product must have child products attached
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $imageTypes - image types to select for child products
     * @return array
     */
    public function getConfigurableImagesFallbackArray(Mage_Catalog_Model_Product $product, array $imageTypes,
        $keepFrame = false
    ) {
        if (!$product->hasConfigurableImagesFallbackArray()) {
            $mapping = $product->getChildAttributeLabelMapping();

            $mediaGallery = $product->getMediaGallery();

            if (!isset($mediaGallery['images'])) {
                return array(); //nothing to do here
            }

            // ensure we only attempt to process valid image types we know about
            $imageTypes = array_intersect(array('image', 'small_image'), $imageTypes);

            $imagesByLabel = array();
            $imageHaystack = array_map(function ($value) {
                return Mage_ConfigurableSwatches_Helper_Data::normalizeKey($value['label']);
            }, $mediaGallery['images']);

            // load images from the configurable product for swapping
            foreach ($mapping as $map) {
                $imagePath = null;

                //search by store-specific label and then default label if nothing is found
                $imageKey = array_search($map['label'], $imageHaystack);
                if ($imageKey === false) {
                    $imageKey = array_search($map['default_label'], $imageHaystack);
                }

                //assign proper image file if found
                if ($imageKey !== false) {
                    $imagePath = $mediaGallery['images'][$imageKey]['file'];
                }

                $imagesByLabel[$map['label']] = array(
                    'configurable_product' => array(
                        Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_SMALL => null,
                        Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE => null,
                    ),
                    'products' => $map['product_ids'],
                );

                if ($imagePath) {
                    $imagesByLabel[$map['label']]['configurable_product']
                        [Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_SMALL] =
                            $this->_resizeProductImage($product, 'small_image', $keepFrame, $imagePath, false, 900);

                    $imagesByLabel[$map['label']]['configurable_product']
                        [Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE] =
                            $this->_resizeProductImage($product, 'image', $keepFrame, $imagePath, false, 900);
                }
            }

            $imagesByType = array(
                'image' => array(),
                'small_image' => array(),
            );

            // iterate image types to build image array, normally one type is passed in at a time, but could be two
            foreach ($imageTypes as $imageType) {
                // load image from the configurable product's children for swapping
                /* @var $childProduct Mage_Catalog_Model_Product */
                if ($product->hasChildrenProducts()) {
                    foreach ($product->getChildrenProducts() as $childProduct) {
                        if ($image = $this->_resizeProductImage($childProduct, $imageType, $keepFrame, null, true, 900)) {
                            $imagesByType[$imageType][$childProduct->getId()] = $image;
                        }
                    }
                }

                // load image from configurable product for swapping fallback
                if ($image = $this->_resizeProductImage($product, $imageType, $keepFrame, null, true, 900)) {
                    $imagesByType[$imageType][$product->getId()] = $image;
                }
            }

            $array = array(
                'option_labels' => $imagesByLabel,
                Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_SMALL => $imagesByType['small_image'],
                Mage_ConfigurableSwatches_Helper_Productimg::MEDIA_IMAGE_TYPE_BASE => $imagesByType['image'],
            );

            $product->setConfigurableImagesFallbackArray($array);
        }

        return $product->getConfigurableImagesFallbackArray();
    }

    /**
     * Resize specified type of image on the product for use in the fallback and returns the image URL
     * or returns the image URL for the specified image path if present
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $type
     * @param bool $keepFrame
     * @param string $image
     * @param bool $placeholder
     * @return string|bool
     */
    protected function _resizeProductImage($product, $type, $keepFrame, $image = null, $placeholder = false)
    {
        $hasTypeData = $product->hasData($type) && $product->getData($type) != 'no_selection';
        if ($image == 'no_selection') {
            $image = null;
        }
        if ($hasTypeData || $placeholder || $image) {
			
            $helper = Mage::helper('catalog/image')
                ->init($product, $type, $image);
			$size = Mage::getStoreConfig(Mage_Catalog_Helper_Image::XML_NODE_PRODUCT_BASE_IMAGE_WIDTH);
            if ($type == 'small_image') {
                $size = Mage::getStoreConfig(Mage_Catalog_Helper_Image::XML_NODE_PRODUCT_SMALL_IMAGE_WIDTH);
            }
            $config = Mage::getStoreConfig('meigee_blacknwhite_general/productimages');
            if($config['customAspectRatio'] == 0 && $config['reallyCustomAspectRatio'] !== ''){
                $customAspectRatio = $config['reallyCustomAspectRatio'];
            }else $customAspectRatio = $config['customAspectRatio'];

            if ($customAspectRatio == 999) {
                $helper->keepAspectRatio(TRUE);
                $helper->keepFrame(FALSE);
                $height = NULL;
            } 
            elseif ($customAspectRatio == 333) {
                $helper->keepAspectRatio(TRUE);
                $helper->keepFrame(TRUE);
                $height = NULL;
            }
            else {
                $helper->keepAspectRatio(FALSE);
                $helper->keepFrame(FALSE);
                $height = $size*$customAspectRatio;
            }

            
            if (is_numeric($size)) {
                $helper->constrainOnly(true)->resize($size, $height);
            }
            return (string)$helper;
        }
        return false;
    }
}