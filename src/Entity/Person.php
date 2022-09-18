<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A person (alive, dead, undead, or fictional).
 *
 * @see https://schema.org/Person Documentation on Schema.org
 *
 * @ORM\Entity
 *
 */
class Person
{
    protected static $genderMap = [
        'F' => 'female',
        'M' => 'male',
    ];

    protected static $iconMap = [
        'JABGB' => 'location-pin',
        'JCB' => 'book-skull',
        'QGJ' => 'box-archive',
        'MBNR' => 'newspaper',
        'SSWD' => 'square',
        'Buber' => 'envelope-open-text',
        'DBIO' => 'book',
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $status = 0;

    /**
     * @var string Additional name forms.
     *
     * @ORM\Column(name="alternate_name", nullable=true)
     */
    public $alternateName;

    /**
     * @var string A short description of the item.
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     *
     */
    protected $description;

    /**
     * @var string A description of the item.
     *
     * @ORM\Column(name="disambiguating_description", type="string", length=4096, nullable=true)
     *
     */
    public $disambiguatingDescription;

    /**
     * @var string An additional name for a Person, can be used for a middle name.
     *
     */
    protected $additionalName;

    /**
     * @var string Date of birth.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    public $birthDate;

    /**
     * @var string Date of death.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    public $deathDate;

    /**
     * @var string Family name. In the U.S., the last name of an Person. This can be used along with givenName instead of the name property.
     *
     * @ORM\Column(name="family_name", nullable=true)
     */
    public $familyName;

    /**
     * @var string The name of the item.
     *
     * @ORM\Column(name="name", nullable=false)
     */
    protected $name;

    /**
     * @var string Gender of the person.
     *
     * @ORM\Column(name="gender", nullable=true)
     */
    public $gender;

    /**
     * @var string Given name. In the U.S., the first name of a Person. This can be used along with familyName instead of the name property.
     *
     * @ORM\Column(name="given_name", nullable=true)
     */
    public $givenName;

    /**
     * @var string Name of the birthPlace.
     *
     * @ORM\Column(nullable=true,name="birth_place")
     */
    public $birthPlaceLabel;

    /**
     * @var string Name of the deathPlace.
     *
     * @ORM\Column(nullable=true,name="death_place")
     */
    public $deathPlaceLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="honorific_prefix", nullable=true)
     */
    public $honorificPrefix;

    /**
     * @var string
     *
     * @ORM\Column(name="honorific_suffix", nullable=true)
     */
    public $honorificSuffix;

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", nullable=true)
     */
    public $hasOccupation;

    /**
     * @var int The data provider property
     *
     * @ORM\Column(name="data_provider", nullable=false)
     */
    public $dataProvider;

    /**
     * @var string The identifier property (external ID)
     *
     * @ORM\Column(name="identifier", nullable=false)
     */
    public $identifier;

    /**
     * @var string URL of the item
     *
     * @ORM\Column(nullable=true)
     */
    public $url;

    /**
     *  @var array external identifiers for this person
     * @ORM\Column(name="same_as", type="json")
     */
    public $sameAs = [];

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets additionalName.
     *
     * @param string $additionalName
     *
     * @return $this
     */
    public function setAdditionalName($additionalName)
    {
        $this->additionalName = $additionalName;

        return $this;
    }

    /**
     * Gets additionalName.
     *
     * @return string
     */
    public function getAdditionalName()
    {
        return $this->additionalName;
    }

    /**
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        if (empty($name)) {
            $this->name = $this->getFullname();
        }

        return $this->name;
    }

    /**
     * Sets birthDate.
     *
     * @param string $birthDate
     *
     * @return $this
     */
    public function setBirthDate($birthDate = null)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Gets birthDate.
     *
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Sets deathDate.
     *
     * @param string $deathDate
     *
     * @return $this
     */
    public function setDeathDate($deathDate = null)
    {
        $this->deathDate = $deathDate;

        return $this;
    }

    /**
     * Gets deathDate.
     *
     * @return string
     */
    public function getDeathDate()
    {
        if (is_null($this->deathDate) || '0000-00-00' == $this->deathDate) {
            return null;
        }

        return $this->deathDate;
    }

    /**
     * Gets deathCause.
     *
     * @return string
     */
    public function getDeathCause()
    {
        return $this->deathCause;
    }

    /**
     * Sets familyName.
     *
     * @param string $familyName
     *
     * @return $this
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Gets familyName.
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * Sets gender.
     *
     * @param string $gender
     *
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = mb_strtoupper($gender);

        return $this;
    }

    /**
     * Gets gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    public function getGenderLabel()
    {
        $ret = $this->gender;

        if (!is_null($ret) && array_key_exists($ret, self::$genderMap)) {
            $ret = self::$genderMap[$ret];
        }

        return $ret;
    }

    /**
     * Sets givenName.
     *
     * @param string $givenName
     *
     * @return $this
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Gets givenName.
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * Sets nationality.
     *
     * @param string $nationality
     *
     * @return $this
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;

        return $this;
    }

    /**
     * Gets nationality.
     *
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * Sets birthPlace.
     *
     * @param Place $birthPlace
     *
     * @return $this
     */
    public function setBirthPlace(/* Place */ $birthPlace = null)
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    /**
     * Gets birthPlace.
     *
     * @return Place
     */
    public function getBirthPlace()
    {
        return $this->birthPlace;
    }

    /**
     * Gets birthPlace.
     *
     * @return string
     */
    public function getBirthPlaceLabel()
    {
        return $this->birthPlaceLabel;
    }

    /**
     * Gets indivdual addresses.
     *
     * @return array
     */
    public function getAddressesSeparated($filterExhibition = null, $linkPlace = false, $returnStructure = false)
    {
        $addresses = $this->buildAddresses($this->addresses, false, $filterExhibition, $linkPlace, $returnStructure);

        if (!$returnStructure) {
            // lookup exhibitions
            for ($i = 0; $i < count($addresses); $i++) {
                $addresses[$i]['exhibitions'] = !empty($addresses[$i]['id_exhibitions'])
                    ? $this->getExhibitions(-1, $addresses[$i]['id_exhibitions'])
                    : [];
            }
        }

        return $addresses;
    }

    /**
     * Gets birthPlace info
     *
     */
    public function getBirthPlaceInfo($locale = 'en')
    {
        if (false && !is_null($this->birthPlace)) {
            return self::buildPlaceInfo($this->birthPlace, $locale);
        }

        /*
        $placeInfo = self::buildPlaceInfoFromEntityfacts($this->getEntityfacts($locale), 'placeOfBirth');
        if (!empty($placeInfo)) {
            return $placeInfo;
        }
        */

        if (!empty($this->birthPlaceLabel)) {
            return [
                'name' => $this->birthPlaceLabel,
            ];
        }
    }

    /**
     * Sets deathPlace.
     *
     * @param Place $deathPlace
     *
     * @return $this
     */
    public function setDeathPlace(/* Place */ $deathPlace = null)
    {
        $this->deathPlace = $deathPlace;

        return $this;
    }

    /**
     * Gets deathPlace.
     *
     * @return Place
     */
    public function getDeathPlace()
    {
        return $this->deathPlace;
    }

    /**
     * Gets deathPlace.
     *
     * @return string
     */
    public function getDeathPlaceLabel()
    {
        return $this->deathPlaceLabel;
    }

    /**
     * Gets deathPlace info
     *
     */
    public function getDeathPlaceInfo($locale = 'en')
    {
        if (false && !is_null($this->deathPlace)) {
            return self::buildPlaceInfo($this->deathPlace, $locale);
        }

        /*
        $placeInfo = self::buildPlaceInfoFromEntityfacts($this->getEntityfacts($locale), 'placeOfDeath');
        if (!empty($placeInfo)) {
            return $placeInfo;
        }
        */

        if (!empty($this->deathPlaceLabel)) {
            return [
                'name' => $this->deathPlaceLabel,
            ];
        }
    }

    /**
     * Sets honorificPrefix.
     *
     * @param string $honorificPrefix
     *
     * @return $this
     */
    public function setHonorificPrefix($honorificPrefix)
    {
        $this->honorificPrefix = $honorificPrefix;

        return $this;
    }

    /**
     * Gets honorificPrefix.
     *
     * @return string
     */
    public function getHonorificPrefix()
    {
        return $this->honorificPrefix;
    }

    /**
     * Sets honorificSuffix.
     *
     * @param string $honorificSuffix
     *
     * @return $this
     */
    public function setHonorificSuffix($honorificSuffix)
    {
        $this->honorificSuffix = $honorificSuffix;

        return $this;
    }

    /**
     * Gets honorificSuffix.
     *
     * @return string
     */
    public function getHonorificSuffix()
    {
        return $this->honorificSuffix;
    }

    /**
     * Returns
     *  familyName, givenName
     * or
     *  givenName familyName
     * depending on $givenNameFirst
     *
     * @return string
     */
    public function getFullname($givenNameFirst = false)
    {
        $parts = [];
        foreach ([ 'familyName', 'givenName' ] as $key) {
            if (!empty($this->$key)) {
                $parts[] = $this->$key;
            }
        }

        if (empty($parts)) {
            return '';
        }

        return $givenNameFirst
            ? implode(' ', array_reverse($parts))
            : implode(', ', $parts);
    }

    /**
     * We prefer person-by-gnd
     */
    public function getRouteInfo()
    {
        $route = 'person';
        $routeParams = [ 'id' => $this->id ];

        foreach ([ 'gnd', 'ulan' ] as $key) {
            if (!empty($this->$key)) {
                $route = 'person-by-' . $key;
                $routeParams = [ $key => $this->$key ];
                break;
            }
        }

        return [ $route, $routeParams ];
    }

    public function setGnd($gnd)
    {
        return $this->setExternalIdentifier('gnd', $gnd);
    }

    public function getGnd()
    {
        return $this->getExternalIdentifier('gnd');
    }

    public function getWikidata()
    {
        return $this->getExternalIdentifier('wikidata');
    }

    public function setWikidata($wikidata)
    {
        return $this->setExternalIdentifier('wikidata', $wikidata);
    }

    public function setExternalIdentifier($key, $value)
    {
        if (is_null($this->sameAs)) {
            $this->sameAs = [];
        }

        if (is_null($value)) {
            unset($this->sameAs[$key]);
        }
        else {
            $this->sameAs[$key] = $value;
        }

        return $this;
    }

    public function getExternalIdentifier($key)
    {
        if (empty($this->sameAs) || !array_key_exists($key, $this->sameAs)) {
            return null;
        }

        return $this->sameAs[$key];
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'fullname' => $this->getFullname(),
            'honorificPrefix' => $this->getHonorificPrefix(),
            'description' => $this->getDescription(),
            'gender' => $this->getGender(),
            'gnd' => $this->getGnd(),
            'slug' => $this->slug,
        ];
    }

    public function getDataProviderIcon(): string
    {
        if (empty($this->identifier)) {
            return '';
        }

        $parts = explode('.', $this->identifier);

        $key = $parts[0];

        if (array_key_exists($key, self::$iconMap)) {
            return self::$iconMap[$key];
        }

        return '';
    }

    public function jsonLdSerialize($locale, $omitContext = false)
    {
        static $genderMap = [
            'F' => 'http://schema.org/Female',
            'M' => 'http://schema.org/Male',
        ];

        $ret = [
            '@context' => 'http://schema.org',
            '@type' => 'Person',
            'name' => $this->getFullname(true),
        ];
        if ($omitContext) {
            unset($ret['@context']);
        }

        foreach ([ 'birth', 'death'] as $lifespan) {
            $property = $lifespan . 'Date';
            if (!empty($this->$property)) {
                $ret[$property] = \App\Utils\JsonLd::formatDate8601($this->$property);
            }

            $property = $lifespan . 'Place';
            if (!is_null($this->$property)) {
                $ret[$property] = $this->$property->jsonLdSerialize($locale, true);
            }
        }

        $description = $this->getDescriptionLocalized($locale);
        if (!empty($description)) {
            $ret['description'] = $description;
        }

        foreach ([ 'givenName', 'familyName', 'url' ] as $property) {
            if (!empty($this->$property)) {
                $ret[$property] = $this->$property;

            }
        }

        if (!empty($this->honorificPrefix)) {
            $ret['honorificPrefix'] = $this->honorificPrefix;
        }

        if (!is_null($this->gender) && array_key_exists($this->gender, $genderMap)) {
            $ret['gender'] = $genderMap[$this->gender];
        }

        $sameAs = [];
        if (!empty($this->ulan)) {
            $sameAs[] = 'http://vocab.getty.edu/ulan/' . $this->ulan;
        }

        if (!empty($this->gnd)) {
            $sameAs[] = 'https://d-nb.info/gnd/' . $this->gnd;
        }

        if (!empty($this->wikidata)) {
            $sameAs[] = 'http://www.wikidata.org/entity/' . $this->wikidata;
        }

        if (count($sameAs) > 0) {
            $ret['sameAs'] = (1 == count($sameAs)) ? $sameAs[0] : $sameAs;
        }

        return $ret;
    }

    /**
     * See https://developers.facebook.com/docs/reference/opengraph/object-type/profile/
     *
     */
    public function ogSerialize($locale, $baseUrl)
    {
        $ret = [
            'og:type' => 'profile',
            'og:title' => $this->getFullname(true),
        ];

        $parts = [];

        $description = $this->getDescriptionLocalized($locale);
        if (!empty($description)) {
            $parts[] = $description;
        }

        $datesOfLiving = '';
        if (!empty($this->birthDate)) {
            $datesOfLiving = \App\Utils\Formatter::dateIncomplete($this->birthDate, $locale);
        }

        if (!empty($this->deathDate)) {
            $datesOfLiving .= ' - ' . \App\Utils\Formatter::dateIncomplete($this->deathDate, $locale);
        }

        if (!empty($datesOfLiving)) {
            $parts[] = '[' . $datesOfLiving . ']';
        }

        if (!empty($parts)) {
            $ret['og:description'] = join(' ', $parts);
        }

        // TODO: maybe get og:image

        if (!empty($this->givenName)) {
            $ret['profile:first_name'] = $this->givenName;
        }

        if (!empty($this->familyName)) {
            $ret['profile:last_name'] = $this->familyName;
        }

        if (!is_null($this->gender) && array_key_exists($this->gender, self::$genderMap)) {
            $ret['profile:gender'] = self::$genderMap[$this->gender];
        }

        return $ret;
    }
}
