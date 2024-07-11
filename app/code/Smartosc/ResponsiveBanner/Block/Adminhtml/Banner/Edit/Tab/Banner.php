<?php

namespace Smartosc\ResponsiveBanner\Block\Adminhtml\Banner\Edit\Tab;

use Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner as MageplazaBanner;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Mageplaza\BannerSlider\Helper\Image as HelperImage;
use Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Render\Image as BannerImage;
use Mageplaza\BannerSlider\Helper\Data;
use Magento\Backend\Block\Widget\Button;
use Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Render\Slider;
use Smartosc\ResponsiveBanner\Model\Config\PositionOptions;

/**
 * Class Banner
 *
 * This class extends class \Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner
 */
class Banner extends MageplazaBanner
{
    /**
     *
     * @var \Smartosc\ResponsiveBanner\Model\Config\PositionOptions
     */
    protected $positionOptions;
    
    /**
     *
     * @var \Smartosc\ResponsiveBanner\Model\Config\VPositionOptions
     */
    protected $vPositionOptions;

    /**
     * Banner constructor.
     * @param \Mageplaza\BannerSlider\Model\Config\Source\Type $typeOptions
     * @param \Mageplaza\BannerSlider\Model\Config\Source\Template $template
     * @param \Magento\Config\Model\Config\Source\Enabledisable $statusOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param HelperImage $imageHelper
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param PositionOptions $positionOptions
     * @param \Smartosc\ResponsiveBanner\Model\Config\VPositionOptions $vPositionOptions
     * @param array $data
     */
    public function __construct(
        \Mageplaza\BannerSlider\Model\Config\Source\Type $typeOptions,
        \Mageplaza\BannerSlider\Model\Config\Source\Template $template,
        \Magento\Config\Model\Config\Source\Enabledisable $statusOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Mageplaza\BannerSlider\Helper\Image $imageHelper,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Smartosc\ResponsiveBanner\Model\Config\PositionOptions $positionOptions,
        \Smartosc\ResponsiveBanner\Model\Config\VPositionOptions $vPositionOptions,
        array $data = []
    ) {
        $this->positionOptions = $positionOptions;
        $this->vPositionOptions = $vPositionOptions;
        parent::__construct(
            $typeOptions,
            $template,
            $statusOptions,
            $context,
            $registry,
            $formFactory,
            $imageHelper,
            $fieldFactory,
            $objectConverter,
            $wysiwygConfig
        );
    }
  
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $position = $fieldset->addField('position', 'select', [
            'name' => 'position',
            'title' => __('Select horizontal position to display the text'),
            'label' => __('Select horizontal position to display the text'),
            'values' => $this->positionOptions->toOptionArray()
        ]);
        // Vertical position
        $vPosition = $fieldset->addField('vertical_position', 'select', [
            'name' => 'vertical_position',
            'title' => __('Select vertical position to display the text'),
            'label' => __('Select vertical position to display the text'),
            'values' => $this->vPositionOptions->toOptionArray()
        ]);
        $color = $fieldset->addField(
            'color',
            \Smartosc\ResponsiveBanner\Data\Form\Element\Color::class,
            [
                'name' => 'color',
                'label' => __('Color'),
                'title' => __('Color')
            ]
        );
        $color = $fieldset->addField(
            'background_color',
            \Smartosc\ResponsiveBanner\Data\Form\Element\Color::class,
            [
                'name' => 'background_color',
                'label' => __('Background Color'),
                'title' => __('Background Color')
            ]
        );

        $textLink = $fieldset->addField(
            'url_for_banner_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            [
                'name' => 'url_for_banner_title',
                'label' => __('URL For Banner Title'),
                'title' => __('URL For Banner Title')
            ]
        );

        $banner = $this->_coreRegistry->registry('mpbannerslider_banner');
        $data = [
            'color' => $banner->getColor(),
            'background_color' => $banner->getBackgroundColor(),
            'position' => $banner->getPosition(),
            'vertical_position' => $banner->getVerticalPosition(),
            'url_for_banner_title' => $banner->getUrlForBannerTitle()
        ];
        $form->addValues($data);
        
        return $this;
    }
}
