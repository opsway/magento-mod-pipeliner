<?php
class OpsWay_Pipeliner_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getUrl()
    {
        return Mage::getStoreConfig('OpsWay_Pipeliner/general/service_url');
    }

    public function getPipelineId()
    {
        return Mage::getStoreConfig('OpsWay_Pipeliner/general/team_id');
    }

    public function getToken()
    {
        return Mage::getStoreConfig('OpsWay_Pipeliner/general/token');
    }

    public function getPassword()
    {
        return Mage::getStoreConfig('OpsWay_Pipeliner/general/password');
    }
    
    public function getConnection()
    {
        return PipelinerSales_PipelinerClient::create(
                $this->getUrl(),
                $this->getPipelineId(),
                $this->getToken(),
                $this->getPassword()
        );
    }
}