<?php

namespace Smartosc\Blog\Model;

/**
 * Class Post
 * @package Smartosc\Blog\Model
 */
class Post extends \MGS\Blog\Model\Post
{
	/**
	 * @return mixed
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	public function getCatetories(){
		$catetories = $this->category->getCollection()
			->addStoreFilter($this->storeManager->getStore()->getId());
		return $catetories;
	}
}
