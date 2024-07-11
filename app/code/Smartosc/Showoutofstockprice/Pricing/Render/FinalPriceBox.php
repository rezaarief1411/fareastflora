<?php

namespace Smartosc\Showoutofstockprice\Pricing\Render;

use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;

/**
 * Class FinalPriceBox
 * @package Smartosc\Showoutofstockprice\Pricing\Render
 */
class FinalPriceBox extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{
    /**
     * @return string
     */
    protected function _toHtml()
    {
        $result = BasePriceBox::_toHtml();
        //Renders MSRP in case it is enabled
        if ($this->isMsrpPriceApplicable()) {
            /** @var BasePriceBox $msrpBlock */
            $msrpBlock = $this->rendererPool->createPriceRender(
                MsrpPrice::PRICE_CODE,
                $this->getSaleableItem(),
                [
                    'real_price_html' => $result,
                    'zone' => $this->getZone(),
                ]
            );
            $result = $msrpBlock->toHtml();
        }

        return $this->wrapResult($result);
    }
}
