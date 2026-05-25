<?php

namespace Joomla\CMS\Categories;

defined('JPATH_PLATFORM') or die;
use Joomla\Registry\Registry;
class CategoryNode extends \JObject
{
    public $id = null;
    public $asset_id = null;
    public $parent_id = null;
    public $lft = null;
    public $rgt = null;
    public $level = null;
    public $extension = null;
    public $title = null;
    public $alias = null;
    public $description = null;
    public $published = null;
    public $checked_out = 0;
    public $checked_out_time = 0;
    public $access = null;
    public $params = null;
    public $metadesc = null;
    public $metakey = null;
    public $metadata = null;
    public $created_user_id = null;
    public $created_time = null;
    public $modified_user_id = null;
    public $modified_time = null;
    public $hits = null;
    public $language = null;
    public $numitems = null;
    public $childrennumitems = null;
    public $slug = null;
    public $assets = null;
    protected $_parent = null;
    protected $_children = array();
    protected $_path = array();
    protected $_leftSibling = null;
    protected $_rightSibling = null;
    protected $_allChildrenloaded = false;
    protected $_constructor = null;
    public function __construct($category = null, $constructor = null)
    {
        if ($category) {
            $this->setProperties($category);
            if ($constructor) {
                $this->_constructor = $constructor;
            }
            return true;
        }
        return false;
    }
    public function setParent($parent)
    {
        if ($parent instanceof CategoryNode || is_null($parent)) {
            if (!is_null($this->_parent)) {
                $key = array_search($this, $this->_parent->_children);
                unset($this->_parent->_children[$key]);
            }
            if (!is_null($parent)) {
                $parent->_children[] =& $this;
            }
            $this->_parent = $parent;
            if ($this->id != 'root') {
                if ($this->parent_id != 1) {
                    $this->_path = $parent->getPath();
                }
                $this->_path[$this->id] = $this->id . ':' . $this->alias;
            }
            if (count($parent->_children) > 1) {
                end($parent->_children);
                $this->_leftSibling = prev($parent->_children);
                $this->_leftSibling->_rightsibling =& $this;
            }
        }
    }
    public function addChild($child)
    {
        if ($child instanceof CategoryNode) {
            $child->setParent($this);
        }
    }
    public function removeChild($id)
    {
        $key = array_search($this, $this->_parent->_children);
        unset($this->_parent->_children[$key]);
    }
    public function &getChildren($recursive = false)
    {
        if (!$this->_allChildrenloaded) {
            $temp = $this->_constructor->get($this->id, true);
            if ($temp) {
                $this->_children = $temp->getChildren();
                $this->_leftSibling = $temp->getSibling(false);
                $this->_rightSibling = $temp->getSibling(true);
                $this->setAllLoaded();
            }
        }
        if ($recursive) {
            $items = array();
            foreach ($this->_children as $child) {
                $items[] = $child;
                $items = array_merge($items, $child->getChildren(true));
            }
            return $items;
        }
        return $this->_children;
    }
    public function getParent()
    {
        return $this->_parent;
    }
    public function hasChildren()
    {
        return count($this->_children);
    }
    public function hasParent()
    {
        return $this->getParent() != null;
    }
    public function setSibling($sibling, $right = true)
    {
        if ($right) {
            $this->_rightSibling = $sibling;
        } else {
            $this->_leftSibling = $sibling;
        }
    }
    public function getSibling($right = true)
    {
        if (!$this->_allChildrenloaded) {
            $temp = $this->_constructor->get($this->id, true);
            $this->_children = $temp->getChildren();
            $this->_leftSibling = $temp->getSibling(false);
            $this->_rightSibling = $temp->getSibling(true);
            $this->setAllLoaded();
        }
        if ($right) {
            return $this->_rightSibling;
        } else {
            return $this->_leftSibling;
        }
    }
    public function getParams()
    {
        if (!$this->params instanceof Registry) {
            $this->params = new Registry($this->params);
        }
        return $this->params;
    }
    public function getMetadata()
    {
        if (!$this->metadata instanceof Registry) {
            $this->metadata = new Registry($this->metadata);
        }
        return $this->metadata;
    }
    public function getPath()
    {
        return $this->_path;
    }
    public function getAuthor($modifiedUser = false)
    {
        if ($modifiedUser) {
            return \JFactory::getUser($this->modified_user_id);
        }
        return \JFactory::getUser($this->created_user_id);
    }
    public function setAllLoaded()
    {
        $this->_allChildrenloaded = true;
        foreach ($this->_children as $child) {
            $child->setAllLoaded();
        }
    }
    public function getNumItems($recursive = false)
    {
        if ($recursive) {
            $count = $this->numitems;
            foreach ($this->getChildren() as $child) {
                $count = $count + $child->getNumItems(true);
            }
            return $count;
        }
        return $this->numitems;
    }
}
