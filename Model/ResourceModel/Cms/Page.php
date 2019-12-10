<?php

namespace Mageplaza\Sitemap\Model\ResourceModel\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetUtilityPageIdentifiersInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Page extends \Magento\Sitemap\Model\ResourceModel\Cms\Page
{
    protected $getUtilityPageIdentifiers;

    /**
     * @param Context                            $context
     * @param MetadataPool                       $metadataPool
     * @param EntityManager                      $entityManager
     * @param string                             $connectionName
     * @param GetUtilityPageIdentifiersInterface $getUtilityPageIdentifiers
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        $connectionName = null,
        GetUtilityPageIdentifiersInterface $getUtilityPageIdentifiers = null
    ) {
        $this->metadataPool      = $metadataPool;
        $this->entityManager     = $entityManager;
        $this->getUtilityPageIdentifiers = $getUtilityPageIdentifiers ?:
            ObjectManager::getInstance()->get(GetUtilityPageIdentifiersInterface::class);
        parent::__construct($context, $metadataPool, $entityManager, $connectionName);
    }

    /**
     * Retrieve cms page collection array
     *
     * @param int $storeId
     * @return array
     */
    public function getCollection($storeId)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.identifier NOT IN (?)',
            $this->getUtilityPageIdentifiers->execute()
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        )->where(
            'mp_exclude_sitemap != ?',
            1
        );

        $pages = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }

        return $pages;
    }
}
