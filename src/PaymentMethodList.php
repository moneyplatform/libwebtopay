<?php

namespace WebToPay;

use SimpleXMLElement;
use WebToPay\Exception\BaseException;

/**
 * Class with all information about available payment methods for some project, optionally filtered by some amount.
 */
class PaymentMethodList
{
    /**
     * Holds available payment countries
     *
     * @var PaymentMethodCountry[]
     */
    protected $countries;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Project ID, to which this method list is valid
     *
     * @var integer
     */
    protected $projectId;

    /**
     * Currency for min and max amounts in this list
     *
     * @var string
     */
    protected $currency;

    /**
     * If this list is filtered for some amount, this field defines it
     *
     * @var integer
     */
    protected $amount;

    /**
     * Constructs object
     *
     * @param integer $projectId
     * @param string $currency currency for min and max amounts in this list
     * @param string $defaultLanguage
     * @param integer $amount null if this list is not filtered by amount
     */
    public function __construct($projectId, $currency, $defaultLanguage = 'lt', $amount = null)
    {
        $this->projectId = $projectId;
        $this->countries = [];
        $this->defaultLanguage = $defaultLanguage;
        $this->currency = $currency;
        $this->amount = $amount;
    }

    /**
     * Gets default language for titles
     *
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * Sets default language for titles.
     * Returns itself for fluent interface
     *
     * @param string $language
     *
     * @return PaymentMethodList
     */
    public function setDefaultLanguage($language)
    {
        $this->defaultLanguage = $language;
        foreach ($this->countries as $country) {
            $country->setDefaultLanguage($language);
        }
        return $this;
    }

    /**
     * Gets project ID for this payment method list
     *
     * @return integer
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Gets currency for min and max amounts in this list
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Gets country object with specified country code. If no country with such country code is found, returns null.
     *
     * @param string $countryCode
     *
     * @return null|PaymentMethodCountry
     */
    public function getCountry($countryCode)
    {
        return isset($this->countries[$countryCode]) ? $this->countries[$countryCode] : null;
    }

    /**
     * Returns new payment method list instance with only those payment methods, which are available for provided
     * amount.
     * Returns itself, if list is already filtered and filter amount matches the given one.
     *
     * @param integer $amount
     * @param string $currency
     *
     * @return PaymentMethodList
     *
     * @throws BaseException    if this list is already filtered and not for provided amount
     */
    public function filterForAmount($amount, $currency)
    {
        if ($currency !== $this->currency) {
            throw new BaseException(
                'Currencies do not match. Given currency: ' . $currency . ', currency in list: ' . $this->currency
            );
        }
        if ($this->isFiltered()) {
            if ($this->amount === $amount) {
                return $this;
            } else {
                throw new BaseException('This list is already filtered, use unfiltered list instead');
            }
        } else {
            $list = new PaymentMethodList($this->projectId, $currency, $this->defaultLanguage, $amount);
            foreach ($this->getCountries() as $country) {
                $country = $country->filterForAmount($amount, $currency);
                if (!$country->isEmpty()) {
                    $list->addCountry($country);
                }
            }
            return $list;
        }
    }

    /**
     * Gets whether this list is already filtered for some amount
     *
     * @return boolean
     */
    public function isFiltered()
    {
        return $this->amount !== null;
    }

    /**
     * Returns available countries
     *
     * @return PaymentMethodCountry[]
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Adds new country to payment methods. If some other country with same code was registered earlier, overwrites it.
     * Returns added country instance
     *
     * @param PaymentMethodCountry $country
     *
     * @return PaymentMethodCountry
     */
    public function addCountry(PaymentMethodCountry $country)
    {
        return $this->countries[$country->getCode()] = $country;
    }

    /**
     * Loads countries from given XML node
     *
     * @param SimpleXMLElement $xmlNode
     */
    public function fromXmlNode($xmlNode)
    {
        foreach ($xmlNode->country as $countryNode) {
            $titleTranslations = [];
            foreach ($countryNode->title as $titleNode) {
                $titleTranslations[(string)$titleNode->attributes()->language] = (string)$titleNode;
            }
            $this->addCountry($this->createCountry((string)$countryNode->attributes()->code, $titleTranslations))
                ->fromXmlNode($countryNode);
        }
    }

    /**
     * Method to create new country instances. Overwrite if you have to use some other country subtype.
     *
     * @param string $countryCode
     * @param array $titleTranslations
     *
     * @return PaymentMethodCountry
     */
    protected function createCountry($countryCode, array $titleTranslations = [])
    {
        return new PaymentMethodCountry($countryCode, $titleTranslations, $this->defaultLanguage);
    }
}
