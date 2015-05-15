<?php

class Liip_ConfigSearch_Block_Adminhtml_Focus extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->_initData();
    }

    protected function _initData()
    {
        $focusFieldSet = $this->getRequest()->getParam('fieldset');
        $focusElement = $this->getRequest()->getParam('element');

        if ($focusFieldSet) {
            $this->setFocusFieldSet($focusFieldSet . '-head');
        }

        if ($focusFieldSet && $focusElement) {
            $this->setFocusElementId($focusFieldSet . '_' . $focusElement);
        }
    }
}
