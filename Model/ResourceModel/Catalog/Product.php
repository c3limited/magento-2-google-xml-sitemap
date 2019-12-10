<?php

namespace Mageplaza\Sitemap\Model\ResourceModel\Catalog;

class Product extends \Magento\Sitemap\Model\ResourceModel\Catalog\Product
{
    protected $_storeId;

    public function prepareSelectStatement(\Magento\Framework\DB\Select $select)
    {
        $this->_addFilter($this->_storeId, 'mp_exclude_sitemap', 0);

        return $select;
    }

    public function getCollection($storeId)
    {
        $this->_storeId = $storeId;

        return parent::getCollection($storeId);
    }
}
