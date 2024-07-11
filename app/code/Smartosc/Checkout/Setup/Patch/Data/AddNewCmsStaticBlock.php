<?php

namespace Smartosc\Checkout\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;

/**
 * Class AddNewCmsStaticBlock
 * @package Smartosc\Checkout\Setup\Patch\Data
 */
class AddNewCmsStaticBlock implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * AddNewCmsStaticBlock constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $cmsContent = <<<EOD
<div class="promo-ads">
    <div class="promo-ads__title">ONE account MANY benefits</div>
    <div class="promo-ads__sub-title">Sign up for the FREE now to enjoy more rewards and savings!</div>
    
    <div class="details">
        <ul class="details__option">
            <li class="details__option__item"><span class="gift-ico"></span>Offer a discount on popular products</li>
            <li class="details__option__item"><span class="gift-ico"></span>Monthly customer giveaways</li>
            <li class="details__option__item"><span class="gift-ico"></span>Reduced delivery charge</li>
            <li class="details__option__item"><span class="gift-ico"></span>Weekly updates on promotions</li>
            <li class="details__option__item"><span class="gift-ico"></span>Order Tracking</li>
        </ul>
    </div>
</div>
EOD;

        $block = $this->blockFactory->create();
        $block->setData([
            'block_id' => 'promo_info_on_checkout',
            'title' => 'Promotion Information on Checkout',
            'identifier' => 'promo_info_on_checkout',
            'content' => $cmsContent
        ])->save();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
