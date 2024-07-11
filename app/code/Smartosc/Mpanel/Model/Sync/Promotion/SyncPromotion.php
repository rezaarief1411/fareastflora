<?php

namespace Smartosc\Mpanel\Model\Sync\Promotion;

use Smartosc\Mpanel\Model\IndexManager;
use Smartosc\Mpanel\Model\Logger;
use Smartosc\Mpanel\Model\Sync\Promotion\SyncPromotion as SyncPromotionRule;
use Psr\Log\LoggerInterface;

/**
 * Class SyncPromotion
 * @package Smartosc\Mpanel\Model\Sync\Promotion
 */
class SyncPromotion
{
    const INDEXER_IDS = [
        'catalog_category_product',
        'catalog_product_attribute',
        'catalogrule_rule',
        'catalog_product_price',
        'catalogsearch_fulltext'
    ];
    const LOGGER_NAME = 'Smart';
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var SyncPromotionRule
    /**
     * @var IndexManager
     */
    protected $indexManager;

    /**
     * SyncPromotion constructor.
     * @param IndexManager $indexManager
     */
    public function __construct(
        IndexManager $indexManager,
        LoggerInterface $logger,
        \Smartosc\Mpanel\Model\LoggerFactory $loggerFactory
    ) {
        $this->indexManager = $indexManager;
        $this->logger = $logger;
        $this->logger = $loggerFactory->create(['name' => self::LOGGER_NAME]);
    }

    /**
     * @throws \Throwable
     */
    public function execute()
    {
        $needReindex = false;
        try {
            if ($needReindex) {
                $this->indexManager->reindex(self::INDEXER_IDS);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        return $this;
    }
}
