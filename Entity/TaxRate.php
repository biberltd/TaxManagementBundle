<?php
/**
 * @name        TaxRate
 * @package		BiberLtd\Bundle\CoreBundle\TaxManagementBundle
 *
 * @author      Can Berkol
 * @author		Murat Ünal
 *
 * @version     1.0.1
 * @date        20.05.2014
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Bundle\TaxManagementBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Bundle\CoreBundle\CoreLocalizableEntity;

/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="tax_rate",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_tax_rate_id", columns={"id"})}
 * )
 */
class TaxRate extends CoreLocalizableEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="decimal", length=3, nullable=false)
     */
    private $rate;

    /** 
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Bundle\TaxManagementBundle\Entity\TaxRateLocalization",
     *     mappedBy="tax_rate"
     * )
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\LocationManagementBundle\Entity\Country")
     * @ORM\JoinColumn(name="country", referencedColumnName="id", onDelete="CASCADE")
     */
    private $country;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\LocationManagementBundle\Entity\City")
     * @ORM\JoinColumn(name="city", referencedColumnName="id", onDelete="CASCADE")
     */
    private $city;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\LocationManagementBundle\Entity\State")
     * @ORM\JoinColumn(name="state", referencedColumnName="id", onDelete="CASCADE")
     */
    private $state;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\ProductManagementBundle\Entity\ProductCategory")
     * @ORM\JoinColumn(name="product_category", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product_category;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\ProductManagementBundle\Entity\Product")
     * @ORM\JoinColumn(name="product", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
     *  				Gets $id property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name            setCity ()
     *                  Sets the city property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $city
     *
     * @return          object                $this
     */
    public function setCity($city) {
        if(!$this->setModified('city', $city)->isModified()) {
            return $this;
        }
		$this->city = $city;
		return $this;
    }

    /**
     * @name            getCity ()
     *                  Returns the value of city property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->city
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @name            setCountry ()
     *                  Sets the country property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $country
     *
     * @return          object                $this
     */
    public function setCountry($country) {
        if(!$this->setModified('country', $country)->isModified()) {
            return $this;
        }
		$this->country = $country;
		return $this;
    }

    /**
     * @name            getCountry ()
     *                  Returns the value of country property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->country
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @name            setProductCategory ()
     *                  Sets the product_category property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $product_category
     *
     * @return          object                $this
     */
    public function setProductCategory($product_category) {
        if(!$this->setModified('product_category', $product_category)->isModified()) {
            return $this;
        }
		$this->product_category = $product_category;
		return $this;
    }

    /**
     * @name            getProductCategory ()
     *                  Returns the value of product_category property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->product_category
     */
    public function getProductCategory() {
        return $this->product_category;
    }

    /**
     * @name            setRate ()
     *                  Sets the rate property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $rate
     *
     * @return          object                $this
     */
    public function setRate($rate) {
        if(!$this->setModified('rate', $rate)->isModified()) {
            return $this;
        }
		$this->rate = $rate;
		return $this;
    }

    /**
     * @name            getRate ()
     *                  Returns the value of rate property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->rate
     */
    public function getRate() {
        return $this->rate;
    }

    /**
     * @name            setSite ()
     *                  Sets the site property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $site
     *
     * @return          object                $this
     */
    public function setSite($site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

    /**
     * @name            getSite ()
     *                  Returns the value of site property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->site
     */
    public function getSite() {
        return $this->site;
    }

    /**
     * @name            setState ()
     *                  Sets the state property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $state
     *
     * @return          object                $this
     */
    public function setState($state) {
        if(!$this->setModified('state', $state)->isModified()) {
            return $this;
        }
		$this->state = $state;
		return $this;
    }

    /**
     * @name            getState ()
     *                  Returns the value of state property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->state
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @name            setProduct ()
     *                  Sets the product property.
     *                  Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.1
     * @version         1.0.1
     *
     * @use             $this->setModified()
     *
     * @param           mixed $product
     *
     * @return          object                $this
     */
    public function setProduct($product) {
        if($this->setModified('product', $product)->isModified()) {
            $this->product = $product;
        }

        return $this;
    }

    /**
     * @name            getProduct ()
     *                  Returns the value of product property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.1
     * @version         1.0.1
     *
     * @return          mixed           $this->product
     */
    public function getProduct() {
        return $this->product;
    }

}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Murat Ünal
 * 24.09.2013
 * **************************************
 * A getProduct()
 * A setProduct()
 *
 * **************************************
 * v1.0.0                      Murat Ünal
 * 24.09.2013
 * **************************************
 * A getCountry()
 * A getId()
 * A getLocalizations()
 * A getProductCategory()
 * A getRate()
 * A getSite()
 * A getState()
 * A setCountry()
 * A setLocalizations()
 * A setProductCategory()
 * A setRate()
 * A setSite()
 * A setState()
 *
 */