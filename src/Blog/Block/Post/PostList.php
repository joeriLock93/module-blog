<?php

namespace Mirasvit\Blog\Block\Post;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Mirasvit\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class PostList extends Template implements IdentityInterface
{
    /**
     * @var string
     */
    protected $defaultToolbarBlock = 'Mirasvit\Blog\Block\Post\PostList\Toolbar';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var PostCollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @var \Mirasvit\Blog\Model\ResourceModel\Post\Collection
     */
    protected $collection;

    /**
     * @param PostCollectionFactory $tagCollectionFactory
     * @param Registry              $registry
     * @param Context               $context
     */
    public function __construct(
        PostCollectionFactory $tagCollectionFactory,
        Registry $registry,
        Context $context
    ) {
        $this->postCollectionFactory = $tagCollectionFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getPostCollection();

        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }

        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }

        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }

        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);

        $this->setCollection($toolbar->getCollection());

        $this->getPostCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * @return PostList\Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }

        $block = $this->getLayout()->createBlock($this->defaultToolbarBlock, uniqid(microtime()));

        return $block;
    }

    /**
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @param \Mirasvit\Blog\Model\ResourceModel\Post\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Return identifiers for post content.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [];
        //        die(__METHOD__);
        //        $identities = [];
        //        foreach ($this->getPostCollection() as $item) {
        //            $identities = array_merge($identities, $item->getIdentities());
        //        }
        //        $category = $this->getCategory();
        //        if ($category) {
        //            $identities[] = Article::CACHE_KB_ARTICLE_CATEGORY . '_' . $category->getId();
        //        }
        //
        //        return $identities;
    }

    /**
     * Retrieve current category model object.
     *
     * @return \Mirasvit\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->registry->registry('current_blog_category');
    }

    /**
     * @return \Mirasvit\Kb\Model\Tag
     */
    public function getTag()
    {
        return $this->registry->registry('current_tag');
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->registry->registry('search_query');
    }

    /**
     * @return \Mirasvit\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection()
    {
        $toolbar = $this->getToolbarBlock();

        if (empty($this->collection)) {
            $collection = $this->postCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addVisibilityFilter();

            if ($category = $this->getCategory()) {
                $collection->addCategoryFilter($category);
            } elseif ($tag = $this->getTag()) {
                $collection->addTagFilter($tag->getId());
            } elseif ($q = $this->getSearchQuery()) {
                $this->kbData->addSearchFilter($collection, $q);
            }

            $collection->setCurPage($this->getCurrentPage());

            $limit = (int)$toolbar->getLimit();
            if ($limit) {
                $collection->setPageSize($limit);
            }

            $page = (int)$toolbar->getCurrentPage();
            if ($page) {
                $collection->setCurPage($page);
            }

            if ($order = $toolbar->getCurrentOrder()) {
                $collection->setOrder($order, $toolbar->getCurrentDirection());
            }

            $this->collection = $collection;
        }

        return $this->collection;
    }
}
