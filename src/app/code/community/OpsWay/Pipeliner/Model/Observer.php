<?php

class OpsWay_Pipeliner_Model_Observer
{
    const PIPELINER_ROLLOUT_STAGE = 'PY-7FFFFFFF-363E1EB8-B973-46AF-BBFD-DD3CDF3C4BBC';
    const COMPANY_SALES_UNIT = '0';
	
	private $methods = array(
		'Mage_Catalog_Model_Category'	=>	'saveCatalogCategoryBefore',
		'Mage_Catalog_Model_Product'	=>	'saveCatalogProductBefore',
		'Mage_Customer_Model_Customer'	=>	'saveCustomerBeforeCommit',
		'Mage_Sales_Model_Order'		=>	'saveOrderAfterCommit'
	);
	
    /**
     *
     * @throws Exception
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveCatalogProductBefore(Varien_Event_Observer $observer)
    {
        try {
            $_product = $observer->getEvent()->getObject();

            $cats = $_product->getCategoryIds();
            $categoryArray = array();
            $_cat = Mage::getModel('catalog/category')->load($cats[count($cats) - 1]);
            $pipeliner = Mage::helper('pipeliner')->getConnection();

            $products = $pipeliner->products->get(PipelinerSales_Query_Filter::equals('ID', $_product->getData('pipeliner_api_id')));
            $accountIterator = $pipeliner->products->getEntireRangeIterator($products);
            foreach ($accountIterator as $account) {
                $sku = $account->getId();
            }
            if (!$sku) {
                $products = $pipeliner->products->create();
            } else {
                $products = $pipeliner->products->getById($sku);
            }
            $products->setDescription($_product->getDescription());
            $products->setName($_product->getName());
            $products->setSku($_product->getSku());
            $products->setProductCategoryId($_cat->getData('pipeliner_api_id'));
            $products->setUnitSymbol('ps');
            $pipeliner->products->save($products);
            $observer->getProduct()->setData('pipeliner_api_id', $products->getId());
        } catch (PipelinerSales_Http_PipelinerHttpException $e) {
            Mage::log($e->getErrorMessage(), null, 'pipeliner.log');
        }

    }

    /**
     *
     * @throws Exception
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveCatalogCategoryBefore(Varien_Event_Observer $observer)
    {
        try {
            $_category = $observer->getEvent()->getObject();
            $categoryString = $_category->getName();
            $pipeliner = Mage::helper('pipeliner')->getConnection();

            $filter = new PipelinerSales_Query_Filter();
            $filter->equals('ID', $_category->getData('pipeliner_api_id'));
            if ($_category->getParentCategory()->getId() != 1) {
                $filter->equals('PARENT_ID', $_category->getParentCategory()->getData('pipeliner_api_id'));
            }

            $categories = $pipeliner->productCategories->get($filter);
            $accountIterator = $pipeliner->productCategories->getEntireRangeIterator($categories);
            foreach ($accountIterator as $account) {
                $categoryId = $account->getId();
            }

            if (empty($categoryId)) {
                $categories = $pipeliner->productCategories->create();
            } else {
                $categories = $pipeliner->productCategories->getById($categoryId);
            }


            $categories->setName($categoryString);
            if ($_category->getParentCategory()->getId() != 2)
                $categories->setParentId($_category->getParentCategory()->getData('pipeliner_api_id'));
            $pipeliner->productCategories->save($categories);
            $observer->getEvent()->getObject()->setData('pipeliner_api_id', $categories->getId());
        } catch (PipelinerSales_Http_PipelinerHttpException $e) {
            Mage::log($e->getErrorMessage(), null, 'pipeliner.log');
        }
    }

    /**
     * Save Attribute Set Identifier as changed resource
     *
     * @throws Exception
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveCustomerBeforeCommit(Varien_Event_Observer $observer)
    {
        try {
            $_customer = $observer->getEvent()->getObject();
			
			
            $pipeliner = Mage::helper('pipeliner')->getConnection();
            $contacts = $pipeliner->contacts->get(PipelinerSales_Query_Filter::equals('ID', $_customer->getData('pipeliner_api_id')));
            $accountIterator = $pipeliner->contacts->getEntireRangeIterator($contacts);
            foreach ($accountIterator as $account) {
                $id = $account->getId();
            }
            if (!$id) {
                $contacts = $pipeliner->contacts->create();
            } else {
                $contacts = $pipeliner->contacts->getById($id);
            }

            
			$contacts->setEmail1($_customer->getEmail());
            $contacts->setFirstName($_customer->getFirstname());
            $contacts->setSurname($_customer->getLastname());
            $contacts->setGender($_customer->getGender());
            $contacts->setMiddleName($_customer->getMiddlename());
            $contacts->setOwnerId($this->getOwnerId($pipeliner));
            $contacts->setSalesUnitId(self::COMPANY_SALES_UNIT);
            $pipeliner->contacts->save($contacts);
            $observer->getEvent()->getObject()->setData('pipeliner_api_id', $contacts->getId());
        } catch (PipelinerSales_Http_PipelinerHttpException $e) {
            Mage::log($e->getErrorMessage(), null, 'pipeliner.log');
        }

    }

    /**
     * Save Attribute Set Identifier as changed resource
     *
     * @throws Exception
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveOrderAfterCommit(Varien_Event_Observer $observer)
    {
        $_order = $observer->getEvent()->getObject();

        try {
            $pipeliner = Mage::helper('pipeliner')->getConnection();

            $opportunities = $pipeliner->opportunities->create();

            $accounts = $pipeliner->accounts->get();
            $accountIterator = $pipeliner->accounts->getEntireRangeIterator($accounts);
            foreach ($accountIterator as $account) {
                $id = $account->getId();
                break;
            }

            $opportunities->setAccountRelations(array(array("ACCOUNT_ID" => $id, "IS_PRIMARY" => 1)));
            $opportunities->setClosingDate(date('Y-m-d H:i:s', time() + 3600*24*365));

            $contacts = $pipeliner->contacts->get(PipelinerSales_Query_Filter::equals('EMAIL1', $_order->getCustomerEmail()));
            $accountIterator = $pipeliner->contacts->getEntireRangeIterator($contacts);
            foreach ($accountIterator as $account) {
                $contactId = $account->getId();
            }

            $opportunities->setContactRelations(array(array("CONTACT_ID" => $contactId, "IS_PRIMARY" => 1)));
            $opportunities->setOpportunityName('Purchase number #'.$_order->getIncrementId());
            $opportunities->setOpportunityValue(number_format($_order->getGrandTotal(), 2, '.', ''));
            $opportunities->setOpportunityValueForegin(number_format($_order->getGrandTotal(), 2, '.', ''));
            $opportunities->setOwnerId($this->getOwnerId($pipeliner));
            $opportunities->setQuickContactName($_order->getCustomerLastname().' '.$_order->getCustomerFirstname());
            $opportunities->setQuickEmail($_order->getCustomerEmail());
            $opportunities->setQuickPhone($_order->getCustomerPhone());
            $opportunities->setSalesUnitId(self::COMPANY_SALES_UNIT);
            $opportunities->setStage(self::PIPELINER_ROLLOUT_STAGE);
            $pipeliner->opportunities->save($opportunities);


            $opportunities = $pipeliner->opportunities->get(PipelinerSales_Query_Filter::equals('OPPORTUNITY_NAME', 'Purchase number #'.$_order->getIncrementId()));
            $accountIterator = $pipeliner->opportunities->getEntireRangeIterator($opportunities);
            foreach ($accountIterator as $account) {
                $opportunityId = $account->getId();
            }

            foreach ($_order->getAllVisibleItems() as $item) {
                $opptyProductRelations = $pipeliner->opptyProductRelations->create();

                $products = $pipeliner->products->get(PipelinerSales_Query_Filter::equals('SKU', $item->getSku()));
                $accountIterator = $pipeliner->products->getEntireRangeIterator($products);
                foreach ($accountIterator as $account) {
                    $productId = $account->getId();
                }

                $opptyProductRelations->setOpptyId($opportunityId);
                $opptyProductRelations->setProductId($productId);
                $opptyProductRelations->setPrice($item->getPrice());
                $opptyProductRelations->setQuantity($item->getSimpleQtyToShip());
                $pipeliner->opptyProductRelations->save($opptyProductRelations);
            }
			
			$messages = $pipeliner->messages->create();
			$messages->setAddressbookId($contactId);
			$messages->setDelegatedClientId($this->getOwnerId($pipeliner));
			$messages->setOwnerId($this->getOwnerId($pipeliner));
			$messages->setOpptyId($this->getOwnerId($opportunityId));
			$messages->setSubject('Order number #'.$_order->getIncrementId());
			$pipeliner->messages->save($messages);
			
        } catch (PipelinerSales_Http_PipelinerHttpException $e) {
            Mage::log($e->getErrorMessage(), null, 'pipeliner.log');
        }
    }

    public function syncCategories()
    {
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $pipeliner = Mage::helper('pipeliner')->getConnection();
            $categories = $pipeliner->productCategories->get();
            $accountIterator = $pipeliner->productCategories->getEntireRangeIterator($categories);
            foreach ($accountIterator as $account) {
                $category = $this->getCategoryByPipelinerId($account->getId());
                if ($category) {
                    $categoryModel = Mage::getModel('catalog/category')->load($category->getId());
                    $categoryModel->setName($account->getName());
                    $parentId = $account->getParentId();
                    if (!empty($parentId)) {
                        $parentCategory = $this->getCategoryByPipelinerId($parentId);
                        $categoryModel->setPath($parentCategory->getPath());
                    }
                    $categoryModel->save();
                    unset($categoryModel);
                } else {
                    $categoryModel = Mage::getModel('catalog/category');
                    $categoryModel->setName($account->getName());
                    $categoryModel->setIsActive(1);
                    $categoryModel->setDisplayMode('PRODUCTS');
                    $categoryModel->setIsAnchor(0);
                    $parentId = $account->getParentId();
                    if (!empty($parentId)) {
                        $parentCategory = $this->getCategoryByPipelinerId($parentId);
                        $categoryModel->setPath($parentCategory->getPath());
                    }
                    $categoryModel->save();
                    unset($categoryModel);
                }
            }
        } catch (PipelinerSales_Http_PipelinerHttpException $e) {
            Mage::log($e->getErrorMessage(), null, 'pipeliner.log');
        }
    }

    private function syncProducts()
    {
        try {
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $pipeliner = Mage::helper('pipeliner')->getConnection();

            $products = $pipeliner->products->get();
            $accountIterator = $pipeliner->products->getEntireRangeIterator($products);
            foreach ($accountIterator as $account) {
                $_product = getProductByPipelinerId($account->getId());
                if ($_product->getId()) {
                    $product = Mage::getModel('catalog/product')->load($_product->getId());
                    $_category = getCategoryByPipelinerId($account->getProductCategoryId());
                    $product->setStoreId(1)
                        ->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()))
                        ->setSku($account->getSku())
                        ->setAttributeSetId(4)
                        ->setName($account->getName())
                        ->setDescription($account->getDescription())
                        ->setShortDescription($account->getDescription())
                        ->setCategoryIds(array($_category->getId()))
                        ->setData('pipeliner_api_id', $account->getId());
                    $product->save();
                    unset($product);
                } else {
                    $product = Mage::getModel('catalog/product');
                    $_category = getCategoryByPipelinerId($account->getProductCategoryId());
                    $product->setStoreId(1)
                        ->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()))
                        ->setAttributeSetId(4)
                        ->setTypeId('simple')
                        ->setCreatedAt(strtotime('now'))
                        ->setSku($account->getSku())
                        ->setName($account->getName())
                        ->setWeight(0.0000)
                        ->setStatus(1)
                        ->setTaxClassId(1)
                        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                        ->setDescription($account->getDescription())
                        ->setShortDescription($account->getDescription())
                        ->setCategoryIds(array($_category->getId()))
                        ->setData('pipeliner_api_id', $account->getId());
                    $product->save();
                    unset($product);
                }
            }
        } catch (PipelinerSales_Http_PipelinerHttpException $e) {
            Mage::log($e->getErrorMessage(), null, 'pipeliner.log');
        }
    }

    private function getCategoryByPipelinerId($id)
    {
        return Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('pipeliner_api_id',$id)
            ->load()
            ->getFirstItem();
    }

    private function getProductByPipelinerId($id)
    {
        return Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('pipeliner_api_id',$id)
            ->load()
            ->getFirstItem();
    }

    private function getOwnerId($pipeliner) {
        $clients = $pipeliner->clients->get();
        $accountIterator = $pipeliner->clients->getEntireRangeIterator($clients);
        foreach ($accountIterator as $account) {
            return $account->getId();
        }
    }
	
	public function modelSaveAfter(Varien_Event_Observer $observer)
	{
		if(isset($this->methods[get_class($observer->getEvent()->getObject())]))
		{
			$method = $this->methods[get_class($observer->getEvent()->getObject())];
			$this->$method($observer);
		}
	}
}
