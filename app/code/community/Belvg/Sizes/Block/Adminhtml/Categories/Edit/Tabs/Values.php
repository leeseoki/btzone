<?php
class Belvg_Sizes_Block_Adminhtml_Categories_Edit_Tabs_Values extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $cat_id = $this->getRequest()->getParam('cat_id');
        $dim_ids = Mage::getModel('sizes/cat')->getCollection()
                                              ->addFieldToFilter('cat_id', $cat_id)
                                              ->getLastItem()
                                              ->getDimIds();
        $dims = Mage::getModel('sizes/dem')->getCollection();
        $prefix = Mage::getConfig()->getTablePrefix();
        $dims->getSelect()
             ->joinLeft(array('labels' => $prefix . 'belvg_sizes_dem_labels'),
                              'labels.dem_id = main_table.dem_id AND labels.store_id=0')
             ->where('main_table.dem_id in (' . $dim_ids . ')');
        $standards = Mage::getModel('sizes/standards')->getCollection()->getData();
        $fieldset = array();
        foreach ($standards as $standard) {
            foreach ($dims as $dim) {
                $fieldset[] = $form->addFieldset($dim->getLabel() . '_' . $standard['name'], array( 'legend' => $dim->getLabel() . ' (' . $standard['name'] . ')'));        
                
                $values = Mage::getModel('sizes/standardsvalues')->getCollection()->setOrder('sort_order', 'ASC')
                                                                 ->addFieldToFilter('standard_id', $standard['standard_id'])
                                                                 ->getData();
                
                foreach ($values as $value) {
                    $main = Mage::getModel('sizes/main')->getCollection()
                                                        ->addFieldToFilter('cat_id', $cat_id)
                                                        ->addFieldToFilter('dem_id', $dim['dem_id'])
                                                        ->addFieldToFilter('value_id', $value['value_id'])
                                                        ->getLastItem();
                    $fieldset[count($fieldset)-1]->addType('range', 'Belvg_Sizes_Block_Adminhtml_Form_Element_Range')
                                                 ->addField('row_' . count($fieldset) . $value['value_id'], 'range', array(
                        'label'     => $value['value'],
                        'required'  => FALSE,
                        'name'      => $dim['dem_id'] . '[' . $value['value_id'] . ']',
                        'value'     => array( $main->getMin(), $main->getMax() ),
                        'disabled'  => FALSE,
                        'class'     => 'validate-digits',
                        'after_element_html' => '<span style="float:left;"> -</span>',
                        'tabindex'  => 1,
                        'width'    => '50px'
                    ));
                }
                
                $fieldset[count($fieldset)-1]->addField('note_' . count($fieldset), 'note', array(
                        'label'     => '* in mm',
                        'required'  => FALSE,
                        'name'      => '',
                        'value'     => '',
                        'disabled'  => FALSE,
                        'class'     => 'validate-digits',
                        'tabindex'  => 1,
                        'width'    => '50px'
                    ));
            }
        }
        
        $form->setFieldNameSuffix('main');
        $this->setForm($form);
    }
    
}