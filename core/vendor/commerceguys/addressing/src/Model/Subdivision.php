<?php

namespace CommerceGuys\Addressing\Model;

use CommerceGuys\Addressing\Exception\UnexpectedTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Subdivision implements SubdivisionInterface
{
    /**
     * The parent.
     *
     * @var SubdivisionInterface
     */
    protected $parent;

    /**
     * The country code.
     *
     * @var string
     */
    protected $countryCode;

    /**
     * The subdivision id.
     *
     * @var string
     */
    protected $id;

    /**
     * The subdivision code.
     *
     * @var string
     */
    protected $code;

    /**
     * The subdivision name.
     *
     * @var string
     */
    protected $name;

    /**
     * The postal code pattern.
     *
     * @var string
     */
    protected $postalCodePattern;

    /**
     * The children.
     *
     * @param SubdivisionInterface[]
     */
    protected $children;

    /**
     * The locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Creates a Subdivision instance.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(SubdivisionInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostalCodePattern()
    {
        return $this->postalCodePattern;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostalCodePattern($postalCodePattern)
    {
        $this->postalCodePattern = $postalCodePattern;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren($children)
    {
        // The interface doesn't typehint $children to allow other
        // implementations to avoid using Doctrine Collections if desired.
        if (!($children instanceof Collection)) {
            throw new UnexpectedTypeException($children, 'Collection');
        }

        $this->children = $children;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return !$this->children->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(SubdivisionInterface $child)
    {
        if (!$this->hasChild($child)) {
            $child->setParent($this);
            $this->children->add($child);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(SubdivisionInterface $child)
    {
        if ($this->hasChild($child)) {
            $child->setParent(null);
            $this->children->removeElement($child);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild(SubdivisionInterface $child)
    {
        return $this->children->contains($child);
    }

    /**
     * Gets the locale.
     *
     * @return string The locale.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale.
     *
     * @param string $locale The locale.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
