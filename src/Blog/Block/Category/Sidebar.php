<?php

namespace Mirasvit\Blog\Block\Category;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class Sidebar extends Template
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param CategoryCollectionFactory $tagCollectionFactory
     * @param Registry                  $registry
     * @param Context                   $context
     * @param array                     $data
     */
    public function __construct(
        CategoryCollectionFactory $tagCollectionFactory,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $tagCollectionFactory;
        $this->registry = $registry;
        $this->context = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Blog\Model\Category[]
     */
    public function getTree()
    {
        return $this->categoryCollectionFactory->create()
            ->getTree();
    }

    /**
     * @return \Mirasvit\Blog\Model\Category|false
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_blog_category');
    }

    /**
     * @param \Mirasvit\Blog\Model\Category $category
     * @return bool
     */
    public function isCurrent($category)
    {
        if ($this->getCurrentCategory() && $this->getCurrentCategory()->getId() == $category->getId()) {
            return true;
        }

        return false;
    }
}
