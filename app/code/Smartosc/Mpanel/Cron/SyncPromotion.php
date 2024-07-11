<?php

namespace Smartosc\Mpanel\Cron;

use Psr\Log\LoggerInterface;
use Smartosc\Mpanel\Model\Sync\Promotion\SyncPromotion as SyncPromotionRule;
use Smartosc\Mpanel\Model\Logger;
use Smartosc\Mpanel\Model\LoggerFactory;

/**
 * Class SyncPromotion
 * @package Smartosc\Mpanel\Cron
 */
class SyncPromotion
{
    const LOGGER_NAME = 'Smart';
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var SyncPromotionRule
     */
    protected $syncPromotionRule;

    /**
     * SyncPromotion constructor.
     * @param SyncPromotionRule $syncPromotionRule
     * @param LoggerInterface $logger
     * @param LoggerFactory $loggerFactory
     */
    public function __construct(
        SyncPromotionRule $syncPromotionRule,
        LoggerInterface $logger,
        \Smartosc\Mpanel\Model\LoggerFactory $loggerFactory
    ) {
        $this->syncPromotionRule = $syncPromotionRule;
        $this->logger = $logger;
        $this->logger = $loggerFactory->create(['name' => self::LOGGER_NAME]);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
            $this->syncPromotionRule->execute();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        return $this;
    }
}
