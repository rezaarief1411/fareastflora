<?php

namespace Smartosc\Mpanel\Model;

use Magento\Indexer\Model\IndexerFactory;

/**
 * Class IndexManager
 * @package Smartosc\Mpanel\Model
 */
class IndexManager
{
    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @param IndexerFactory $indexerFactory
     */
    public function __construct(
        IndexerFactory $indexerFactory
    ) {
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @param string[] $IndexerIds
     * @throws \Throwable
     */
    public function reindex($IndexerIds)
    {
        foreach ($IndexerIds as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }
}
