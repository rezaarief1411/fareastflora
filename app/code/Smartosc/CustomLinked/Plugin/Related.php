<?php

namespace Smartosc\CustomLinked\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related as RelatedParent;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class Related
 *
 * Class Related extending \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related
 */
class Related extends RelatedParent
{
    const GROUP_RELATED = 'related';

    const DATA_SCOPE_CUSTOMLINKED = 'customlinked';

    private $priceModifier;

    protected $product;

    /**
     * @param $modify
     * @param $result
     * @return mixed
     */
    public function afterModifyMeta($modify, $result)
    {
        if (isset($result[static::GROUP_RELATED]['children'])) {
            $result[static::GROUP_RELATED]['children'][$modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED]
            = $this->getCustomlinkedFieldset($modify);
        }
        
        if (isset($result[static::GROUP_RELATED]['arguments']['data']['config']['label'])) {
            $label = __('Related Products, Up-Sells, Cross-Sells and Add-On');
            $result[static::GROUP_RELATED]['arguments']['data']['config']['label'] = $label;
        }
        
        return $result;
    }

    /**
     * @param $modify
     * @return \Magento\Catalog\Ui\Component\Listing\Columns\Price|mixed
     */
    private function getPriceModifier($modify)
    {
        
        if (!$this->priceModifier) {
            $this->priceModifier = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Catalog\Ui\Component\Listing\Columns\Price::class
            );
        }
        return $this->priceModifier;
    }

    /**
     * @param $modify
     * @return array
     */
    protected function getCustomlinkedFieldset($modify)
    {
        $content = __(
            'My add-on Products.'
        );
        return [
            'children' => [
                'button_set' => $modify->getButtonSet(
                    $content,
                    __('Add Add-On Products'),
                    $modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED
                ),
                'modal' => $this->getGenericModal(
                    __('Add Add-On Products'),
                    $modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED
                ),
                static::DATA_SCOPE_CUSTOMLINKED =>
                $this->getGrid($modify->scopePrefix . static::DATA_SCOPE_CUSTOMLINKED),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Add-On Products'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 11,
                    ],
                ],
            ]
        ];
    }

    /**
     * @param $modify
     * @param $data
     * @return mixed
     */
    public function afterModifyData($modify, $data)
    {
        $product = $modify->locator->getProduct();
        $productId = $product->getId();
        if (!$productId) {
            return $data;
        }
        $priceModifier = $this->getPriceModifier($modify);
       
        $priceModifier->setData('name', 'price');
        $dataScopes = $this->getDataScopes();
        $dataScopes[] = static::DATA_SCOPE_CUSTOMLINKED;
        foreach ($dataScopes as $dataScope) {
            if ($dataScope == static::DATA_SCOPE_CUSTOMLINKED) {
                $data[$productId]['links'][$dataScope] = [];
                foreach ($modify->productLinkRepository->getList($product) as $linkItem) {
                    if ($linkItem->getLinkType() !== $dataScope) {
                        continue;
                    }
                    /** @var \Magento\Catalog\Model\Product $linkedProduct */
                    $linkedProduct = $modify->productRepository->get(
                        $linkItem->getLinkedProductSku(),
                        false,
                        $modify->locator->getStore()->getId()
                    );
                    $data[$productId]['links'][$dataScope][] = $this->fillData($linkedProduct, $linkItem);
                }
                if (!empty($data[$productId]['links'][$dataScope])) {
                    $dataMap = $priceModifier-> prepareDataSource([
                        'data' => [
                            'items' => $data[$productId]['links'][$dataScope]
                        ]
                    ]);
                    $data[$productId]['links'][$dataScope] = $dataMap['data']['items'];
                }
            }
        }
        
        return $data;
    }

    /**
     * @param $provider
     * @param $product
     * @return array
     */
    public function beforeGetLinkedProducts($provider, $product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->product = $objectManager->create(\Smartosc\CustomLinked\Model\Product::class);
        $currentProduct = $this->product->load($product->getId());
        return [$currentProduct];
    }
}
