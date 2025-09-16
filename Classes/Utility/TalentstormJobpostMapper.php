<?php
declare(strict_types=1);

namespace HauerHeinrich\HhTalentstormJobPosts\Utility;

/**
 *
 * This file is part of the "hh_talentstorm_job_posts" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Christian Hackl <web@hauer-heinrich.de>, www.Hauer-Heinrich.de
 */

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Database\ConnectionPool;
use \HauerHeinrich\HhSimpleJobPosts\Domain\Model\Jobpost;
use \HauerHeinrich\HhTtAddressPlaces\Domain\Model\Place;

final class TalentstormJobpostMapper {
    protected Jobpost $jobpost;

    private array $dataArray = [];

    private int $pid = 0;
    private int $pidOrganizations = 0;
    private int $pidContactPointAddresses = 0;

    public function __construct() {
        $this->jobpost = GeneralUtility::makeInstance(Jobpost::class);
    }

    /**
     * setDataArray
     */
    public function setDataArray(array $dataArray): void {
        if(!empty($dataArray['hydra:member'])) {
            $this->dataArray = $dataArray['hydra:member'];
        }
    }

    public function setPid(int $pid): void {
        $this->pid = $pid;
    }

    public function setPidOrganizations(int $pid): void {
        $this->pidOrganizations = $pid;
    }

    public function setPidContactPointAddresses(int $pid): void {
        $this->pidContactPointAddresses = $pid;
    }

    /**
     * mapMultipleArrayToObject
     */
    public function mapMultipleArrayToObject(): array {
        $result = [];
        foreach ($this->dataArray as $key => $job) {
            self::__construct();
            $job['pid'] = $this->pid;
            $this->setProperties($job);
            $result[] = $this->getModel();
        }

        return $result;
    }

    /**
     * setProperties
     */
    public function setProperties(array $properties): void {
        if(isset($properties['isPublished']) && $properties['isPublished'] == true) {
            $this->jobpost->setApiUid($properties['id']);
            $this->jobpost->setPid($properties['pid']);
            $this->jobpost->setTitle($properties['label']);
            $this->jobpost->setDescription($properties['description']);

            if(isset($properties['lastModificationDate']) && !empty($properties['lastModificationDate'])) {
                try {
                    $endDate = new \DateTime($properties['lastModificationDate']);
                } catch (\Exception $e) {
                    $endDate = new \DateTime();
                }

                $endDate->modify('+1 year');
                $this->jobpost->setEndtime($endDate);
            }

            if(isset($properties['descJobProfile']) && !empty($properties['descJobProfile'])) {
                $mainTasks = '';
                if(isset($properties['descJobProfileTitle']) && !empty($properties['descJobProfileTitle'])) {
                    $mainTasks .= '<header><h3>'.$properties['descJobProfileTitle'].'</h3></header>';
                }
                $mainTasks .= $properties['descJobProfile'];
                $this->jobpost->setMaintasks($mainTasks);
            }

            if(isset($properties['descApplicantProfile']) && !empty($properties['descApplicantProfile'])) {
                $profile = '';
                if(isset($properties['descApplicantProfileTitle']) && !empty($properties['descApplicantProfileTitle'])) {
                    $profile .= '<header><h3>'.$properties['descApplicantProfileTitle'].'</h3></header>';
                }
                $profile .= $properties['descApplicantProfile'];
                $this->jobpost->setProfile($profile);
            }

            // $this->jobpost->setSkills($properties['descApplicantProfile']);

            if(isset($properties['descOffer']) && !empty($properties['descOffer'])) {
                $weProvide = '';
                if(isset($properties['descOfferTitle']) && !empty($properties['descOfferTitle'])) {
                    $weProvide .= '<header><h3>'.$properties['descOfferTitle'].'</h3></header>';
                }
                $weProvide .= $properties['descOffer'];
                $this->jobpost->setWeprovide($weProvide);
            }

            if(!empty($properties['creationDate']) && is_string($properties['creationDate'])) {
                $this->jobpost->setCrdate(date_create($properties['creationDate']));
            }

            if(!empty($properties['lastModificationDate']) && is_string($properties['lastModificationDate'])) {
                $this->jobpost->setTstamp(date_create($properties['lastModificationDate']));
            }

            if(!empty($properties['additional']['applicationFormUrl']) && is_string($properties['additional']['applicationFormUrl'])) {
                $this->jobpost->setApplicationForm($properties['additional']['applicationFormUrl']);
            }

            // TODO:
            // all available Jobtypes = https://api.talentstorm.de/api/v1/jobtypes

            if(isset($properties['employment']['id'])) {
                $existingEmploymentTypes = [ 'FULL_TIME', 'PART_TIME', 'CONTRACTOR', 'TEMPORARY', 'INTERN', 'VOLUNTEER', 'PER_DIEM', 'OTHER' ];
                $jobEmploymentType = $existingEmploymentTypes[\intval($properties['employment']['id'])] ?? '';
                $this->jobpost->setEmploymentType($jobEmploymentType);
            }

            $additionalData = [];
            if(isset($properties['additional']) && !empty($properties['additional'])) {
                if(\is_array($properties['additional'])) {
                    $additionalData = $properties['additional'];
                } else {
                    $additionalData = [$properties['additional']];
                }
            }

            if(isset($properties['id']) && !empty($properties['id'])) {
                $additionalData['id'] = \intval($properties['id']);
            }

            if(!empty($additionalData)) {
                $this->jobpost->setAdditionalJsonData($additionalData);
            }

            if(isset($properties['jobofferLocations']) && \is_array($properties['jobofferLocations'])) {
                $persistenceManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface::class);
                $addressStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
                $placeRepository = GeneralUtility::makeInstance(\HauerHeinrich\HhTtAddressPlaces\Domain\Repository\PlaceRepository::class);

                foreach ($properties['jobofferLocations'] as $location) {
                    $locationData = $location['location'];

                    if(isset($locationData) && \is_array($locationData)) {
                        $place = new Place;

                        isset($locationData['label']) ? $place->setCompany($locationData['label']) : '';
                        isset($locationData['street']) ? $place->setAddress($locationData['street']) : '';
                        isset($locationData['city']) ? $place->setCity($locationData['city']) : '';
                        isset($locationData['zip']) ? $place->setZip($locationData['zip']) : '';
                        isset($locationData['region']) ? $place->setRegion($locationData['region']) : '';
                        isset($locationData['country']['name']) ? $place->setCountry($locationData['country']['name']) : '';
                        isset($locationData['lat']) ? $place->setLatitude($locationData['lat']) : '';
                        isset($locationData['lon']) ? $place->setLongitude($locationData['lon']) : '';
                        $place->setPid($this->pidOrganizations);
                        $place->setTxExbaseType('place');

                        $existing = $this->findExistingPlace($place, ['pid', 'zip', 'city', 'address']);
                        if(empty($existing)) {
                            $placeRepository->add($place);
                            $persistenceManager->persistAll();
                            $addressStorage->attach($place);
                        } else {
                            $dataMapper = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);
                            $object = $dataMapper->map(Place::class, [$existing]);
                            if(!empty($object)) {
                                $place = $object[0];
                                $addressStorage->attach($place);
                            }
                        }

                        if(isset($location['isMainLocationGh']) && $location['isMainLocationGh'] == true && !empty($place)) {
                            $this->jobpost->setHiringOrganization($place);
                        }
                    }
                }

                $this->jobpost->setJobLocations($addressStorage);
            }

            $this->jobpost->setSlug($properties['slug']);
            // $this->jobpost->setSysLanguageUid(0);
        }
    }

    /**
     * getModel
     *
     * @return \HauerHeinrich\HhSimpleJobPosts\Domain\Model\Jobpost
     */
    public function getModel(): Jobpost {
        return $this->jobpost;
    }

    protected function findExistingPlace(Place $address, array $comparisonFields = [ 'pid', 'zip', 'city', 'address' ]): ?array {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');

        // $queryBuilder->getRestrictions()->removeAll(); // Optional: damit auch versteckte/gelöschte Datensätze berücksichtigt werden

        $conditions = [];

        foreach ($comparisonFields as $field) {
            $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));

            if (method_exists($address, $getter)) {
                $value = $address->$getter();

                // NULL-Werte oder leere Felder ggf. überspringen
                if ($value !== null && $value !== '') {
                    $conditions[] = $queryBuilder->expr()->eq(
                        $field,
                        $queryBuilder->createNamedParameter($value)
                    );
                }
            } else {
                // throw new \InvalidArgumentException("Getter-Methode {$getter} existiert nicht in Address-Objekt.");
            }
        }

        if (empty($conditions)) {
            throw new \RuntimeException('Keine gültigen Vergleichsfelder gefunden.');
        }

        $queryBuilder
            ->select('*')
            ->from('tt_address')
            ->where(
                ...$conditions
            );
        $result = $queryBuilder->executeQuery()->fetchAssociative();

        return $result ?: null;
    }
}
