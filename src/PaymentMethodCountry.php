<?php

namespace WebToPay;
use SimpleXMLElement;
use WebToPay\Exception\BaseException;

/**
 * Payment method configuration for some country
 */
class PaymentMethodCountry
{
    /**
     * @var string
     */
    protected $countryCode;

    /**
     * Holds available payment types for this country
     *
     * @var PaymentMethodGroup[]
     */
    protected $groups;

    /**
     * Default language for titles
     *
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Translations array for this country. Holds associative array of country title by language codes.
     *
     * @var array
     */
    protected $titleTranslations;

    /**
     * Constructs object
     *
     * @param string $countryCode
     * @param array $titleTranslations
     * @param string $defaultLanguage
     */
    public function __construct($countryCode, $titleTranslations, $defaultLanguage = 'lt')
    {
        $this->countryCode = $countryCode;
        $this->defaultLanguage = $defaultLanguage;
        $this->titleTranslations = $titleTranslations;
        $this->groups = [];
    }

    /**
     * Gets title of the group. Tries to get title in specified language. If it is not found or if language is not
     * specified, uses default language, given to constructor.
     *
     * @param string [Optional] $languageCode
     *
     * @return string
     */
    public function getTitle($languageCode = null)
    {
        if ($languageCode !== null && isset($this->titleTranslations[$languageCode])) {
            return $this->titleTranslations[$languageCode];
        } elseif (isset($this->titleTranslations[$this->defaultLanguage])) {
            return $this->titleTranslations[$this->defaultLanguage];
        } else {
            return $this->countryCode;
        }
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
     * @return PaymentMethodCountry
     */
    public function setDefaultLanguage($language)
    {
        $this->defaultLanguage = $language;
        foreach ($this->groups as $group) {
            $group->setDefaultLanguage($language);
        }
        return $this;
    }

    /**
     * Gets country code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->countryCode;
    }

    /**
     * Gets group object with specified group key. If no group with such key is found, returns null.
     *
     * @param string $groupKey
     *
     * @return null|PaymentMethodGroup
     */
    public function getGroup($groupKey)
    {
        return isset($this->groups[$groupKey]) ? $this->groups[$groupKey] : null;
    }

    /**
     * Gets payment methods in all groups
     *
     * @return PaymentMethod[]
     */
    public function getPaymentMethods()
    {
        $paymentMethods = [];
        foreach ($this->groups as $group) {
            $paymentMethods = array_merge($paymentMethods, $group->getPaymentMethods());
        }
        return $paymentMethods;
    }

    /**
     * Returns new country instance with only those payment methods, which are available for provided amount.
     *
     * @param integer $amount
     * @param string $currency
     *
     * @return PaymentMethodCountry
     * @throws BaseException
     */
    public function filterForAmount($amount, $currency)
    {
        $country = new PaymentMethodCountry($this->countryCode, $this->titleTranslations, $this->defaultLanguage);
        foreach ($this->getGroups() as $group) {
            $group = $group->filterForAmount($amount, $currency);
            if (!$group->isEmpty()) {
                $country->addGroup($group);
            }
        }
        return $country;
    }

    /**
     * Returns payment method groups registered for this country.
     *
     * @return PaymentMethodGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Adds new group to payment methods for this country.
     * If some other group was registered earlier with same key, overwrites it.
     * Returns given group
     *
     * @param PaymentMethodGroup $group
     *
     * @return PaymentMethodGroup
     */
    public function addGroup(PaymentMethodGroup $group)
    {
        return $this->groups[$group->getKey()] = $group;
    }

    /**
     * Returns new country instance with only those payment methods, which are returns or not iban number after payment
     *
     * @param boolean $isIban
     *
     * @return PaymentMethodCountry
     */
    public function filterForIban($isIban = true)
    {
        $country = new PaymentMethodCountry($this->countryCode, $this->titleTranslations, $this->defaultLanguage);
        foreach ($this->getGroups() as $group) {
            $group = $group->filterForIban($isIban);
            if (!$group->isEmpty()) {
                $country->addGroup($group);
            }
        }
        return $country;
    }

    /**
     * Returns whether this country has no groups
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->groups) === 0;
    }

    /**
     * Loads groups from given XML node
     *
     * @param SimpleXMLElement $countryNode
     */
    public function fromXmlNode($countryNode)
    {
        foreach ($countryNode->payment_group as $groupNode) {
            $key = (string)$groupNode->attributes()->key;
            $titleTranslations = [];
            foreach ($groupNode->title as $titleNode) {
                $titleTranslations[(string)$titleNode->attributes()->language] = (string)$titleNode;
            }
            $this->addGroup($this->createGroup($key, $titleTranslations))->fromXmlNode($groupNode);
        }
    }

    /**
     * Method to create new group instances. Overwrite if you have to use some other group subtype.
     *
     * @param string $groupKey
     * @param array $translations
     *
     * @return PaymentMethodGroup
     */
    protected function createGroup($groupKey, array $translations = [])
    {
        return new PaymentMethodGroup($groupKey, $translations, $this->defaultLanguage);
    }
}
