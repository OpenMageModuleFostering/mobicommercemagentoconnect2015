<?php

class Mobicommerce_Mobiservices_Block_Connector extends Mage_Core_Block_Template {

	private $_connectorVersion = '';
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function _setConnectorVersion($connectorVersion)
    {
        $this->_connectorVersion = $connectorVersion;
    }

    public function _getConnectorVersion()
    {
    	return $this->_connectorVersion;
    }

    public function _getConnectorModel($model)
    {
        $modelpath = $this->connectorDefinition($this->_connectorVersion, 'mobiservices');
        if(empty($modelpath))
            return $model;
        else
            return str_replace('mobiservices/', 'mobiservices/'.$modelpath.'_', $model);
    }

    protected function connectorDefinition($connectorVersion = null, $module = 'mobiservices')
    {
        if(empty($connectorVersion))
            return false;

        $connector = array(
            '1x0x3' => array(
                'mobiadmin' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                'mobiservices' => array(
                    'version'   => '1.0.3',
                    'modelpath' => '1x0x3'
                    ),
                'mobipaypaloffline' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                'mobipayments' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                ),
            '1.3.1' => array(
                'mobiadmin' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                'mobiservices' => array(
                    'version'   => '1.3.1',
                    'modelpath' => '1x3x1'
                    ),
                'mobipaypaloffline' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                'mobipayments' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                ),
            '1.3.2' => array(
                'mobiadmin' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                'mobiservices' => array(
                    'version'   => '1.3.1',
                    'modelpath' => '1x3x1'
                    ),
                'mobipaypaloffline' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                'mobipayments' => array(
                    'version'   => '1.0.0',
                    'modelpath' => ''
                    ),
                ),
            );
        if(isset($connector[$connectorVersion][$module]['modelpath']) && !empty($connector[$connectorVersion][$module]['modelpath']))
            return $connector[$connectorVersion][$module]['modelpath'];
        return false;
    }
}