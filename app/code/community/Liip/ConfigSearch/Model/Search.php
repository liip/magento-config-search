<?php

class Liip_ConfigSearch_Model_Search extends Varien_Object
{
    protected $_tabs;
    protected $_result = array();

    public function __construct()
    {
        parent::__construct();
        $this->setResults(array());
    }

    public function load()
    {
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections = (array)$configFields->getSections();

        foreach ($sections as $section) {
            if(!$this->_isSectionAllowed($section->getName())) {
                continue;
            }
            $helperName = $configFields->getAttributeModule($section);
            $helper = Mage::helper($helperName);
            $sectionName = $helper->__((string)$section->label);
            if (stripos($sectionName, $this->getQuery()) !== false) {
                $this->addElementToResult($section);
            }

            foreach ((array)$section->groups as $group) {
                $groupName = $helper->__((string)$group->label);
                if (stripos($groupName, $this->getQuery()) !== false) {
                    $this->addElementToResult($section, $group);
                }

                foreach ((array)$group->fields as $field) {
                    $fieldName = $helper->__((string)$field->label);
                    if (stripos($fieldName, $this->getQuery()) !== false) {
                        $this->addElementToResult($section, $group, $field);
                    }
                }
            }
        }

        return $this;
    }

    public function addElementToResult($section, $group = null, $field = null)
    {
        $configFields = Mage::getSingleton('adminhtml/config');
        $helperName = $configFields->getAttributeModule($section);
        $helper = Mage::helper($helperName);

        $tabs = $this->getTabsAsArray();
        $path = array($tabs[(string)$section->tab]);
        $path[] = $label = $helper->__((string)$section->label);
        $type = Mage::helper('liip_configsearch')->__('Config Section');

        $urlParams = array('section' => $section->getName());

        if (null !== $group) {
            $path[] = $label = $helper->__((string)$group->label);
            $urlParams['fieldset'] = $section->getName() . '_' . $group->getName();
            $type = Mage::helper('liip_configsearch')->__('Config Group');
        }
        if (null !== $field) {
            $label = $helper->__((string)$field->label);
            $urlParams['fieldset'] = $section->getName() . '_' . $group->getName();
            $urlParams['element'] = $field->getName();
            $type = Mage::helper('liip_configsearch')->__('Config Field');
        }

        $this->_result[] = array(
            'id'            => md5($type . $label),
            'type'          => $type,
            'name'          => $label,
            'description'   => implode('/' , $path),
            'url' => Mage::helper('adminhtml')->getUrl('*/system_config/edit', $urlParams)
        );
        $this->setResults($this->_result);
    }

    /**
     * Retrieve tabs as array
     *
     * @return array
     */
    public function getTabsAsArray()
    {
        if (null === $this->_tabs) {
            $configFields = Mage::getSingleton('adminhtml/config');
            foreach ((array)$configFields->getTabs()->children() as $tab) {
                $helperName = $configFields->getAttributeModule($tab);
                $this->_tabs[$tab->getName()] = Mage::helper($helperName)->__((string)$tab->label);
            }
        }
        return $this->_tabs;
    }

    /**
     * Check whether specified section is allowed for current user
     *
     * @param string $sectionCode
     * @return bool
     */
    protected function _isSectionAllowed($sectionCode)
    {
        if (!$sectionCode || trim($sectionCode) == "") {
            return false;
        }
        $permissions = Mage::getSingleton('admin/session');
        return $permissions->isAllowed('system/config/' . $sectionCode);
    }
}
