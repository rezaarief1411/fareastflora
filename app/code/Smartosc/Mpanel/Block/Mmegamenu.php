<?php

namespace Smartosc\Mpanel\Block;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Mmegamenu
 * @package Smartosc\Mpanel\Block
 */
class Mmegamenu extends \MGS\Mmegamenu\Block\Mmegamenu
{
    /**
     * {@inheritdoc}
     */
    public function drawList($category, $item, $level = 1)
    {
        $maxLevel = 10;

        $children = $this->getSubCategoryAccepp($category->getId(), $item);
        $childrenCount = count($children);

        $htmlLi = '<li';

        $htmlLi .= ' class="level' . $level . '';
        if ($childrenCount > 0 && $item->getColumns() > 0) {
            $htmlLi .= ' dropdown-submenu';
        }

        $htmlLi .= '">';

        $html[] = $htmlLi;
        $html[] = '<a href="' . $this->getCategoryUrl($category) . '">';
        if ($item->getColumns() > 1 && $level == 1) {
            $html[] = '<span class="mega-menu-sub-title" data-megaimage="'.$this->_getCategoryAsNavItemImageHtml($category).'">';
        }

        $html[] = $category->getName();
//        if ($level == 1) {
//
//            $html[] = '<span class="data-image"><img src="';
//            $html[] = $this->_getCategoryAsNavItemImageHtml($category);
//            $html[] = '" alt="" /></span>';
//        }

        if ($item->getColumns() > 1 && $level == 1) {
            $html[] = '</span>';
        }
        $html[] = '</a>';

        if ($level < $maxLevel) {

            $maxSub = 50;
            $htmlChildren = '';

            if ($childrenCount > 0) {
                $i = 0;
                foreach ($children as $child) {
                    $i++;
                    if ($i <= $maxSub) {
                        $_child = $this->getModel('Magento\Catalog\Model\Category')->load($child);
                        $htmlChildren .= $this->drawList($_child, $item, ($level + 1));
                    }
                }
            }
            if (!empty($htmlChildren)) {
                $html[] = '<span class="toggle-menu visible-xs-block visible-sm-block"><a onclick="toggleEl(this,\'mobile-menu-cat-' . $category->getId() . '-' . $item->getParentId() . '\')" href="javascript:void(0)" class=""><em class="fa fa-plus"></em><em class="fa fa-minus"></em></a></span>';

                $html[] = '<ul id="mobile-menu-cat-' . $category->getId() . '-' . $item->getParentId() . '"';
                if ($item->getColumns() > 2) {
                    $html[] = ' class="sub-menu"';
                } else {
                    $html[] = ' class="dropdown-menu"';
                }
                $html[] = '>';
                $html[] = $htmlChildren;
                $html[] = '</ul>';
            }
        }
        $html[] = '</li>';
        $html = implode("\n", $html);

        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    private function _getCategoryAsNavItemImageHtml($category)
    {
        if ($category->hasData('cat_menu_item_image')) {
            try {
                $imageUrl = $category->getImageUrl('cat_menu_item_image');
                return $imageUrl;
            } catch (LocalizedException $e) {
            }
        }

        return 'no-image';
    }
}
