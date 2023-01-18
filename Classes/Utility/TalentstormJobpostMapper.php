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
use \HauerHeinrich\HhSimpleJobPosts\Domain\Model\Jobpost;
use \HauerHeinrich\HhSimpleJobPosts\Domain\Model\Address;

final class TalentstormJobpostMapper {
    protected Jobpost $jobpost;

    private array $dataArray = [];

    private int $pid = 0;

    public function __construct() {
        $this->jobpost = GeneralUtility::makeInstance(Jobpost::class);
    }

    /**
     * setDataArray
     */
    public function setDataArray(array $dataArray, int $pid): void {
        if(!empty($dataArray['hydra:member'])) {
            $this->dataArray = $dataArray['hydra:member'];
            $this->pid = $pid;
        }
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
        $this->jobpost->setApiUid($properties['id']);
        $this->jobpost->setPid($properties['pid']);
        $this->jobpost->setTitle($properties['label']);
        $this->jobpost->setDescription($properties['description']);
        $this->jobpost->setSkills($properties['descApplicantProfile']);
        $this->jobpost->setWeprovide($properties['descOffer']);

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
        $this->jobpost->setEmploymentType(\strval($properties['idJobtype']));


        if(isset($properties['jobofferLocations']) && \is_array($properties['jobofferLocations'])) {
            foreach ($properties['jobofferLocations'] as $key => $location) {
                $locationData = $location['location'];

                if(isset($locationData) && \is_array($locationData)) {
                    $address = new Address;

                    isset($locationData['label']) ? $address->setCompany($locationData['label']) : '';
                    isset($locationData['street']) ? $address->setAddress($locationData['street']) : '';
                    isset($locationData['city']) ? $address->setCity($locationData['city']) : '';
                    isset($locationData['zip']) ? $address->setZip($locationData['zip']) : '';
                    isset($locationData['region']) ? $address->setRegion($locationData['region']) : '';
                    isset($locationData['country']['name']) ? $address->setCountry($locationData['country']['name']) : '';
                    isset($locationData['lat']) ? $address->setLatitude($locationData['lat']) : '';
                    isset($locationData['lon']) ? $address->setLongitude($locationData['lon']) : '';
                    $address->setTxExtbaseType('ttAddress_location');

                    $this->jobpost->addJobLocation($address);
                }

            }
        }

        $this->jobpost->setSlug($properties['slug']);

        // $this->jobpost->setSysLanguageUid(0);
    }

    /**
     * getModel
     *
     * @return \HauerHeinrich\HhSimpleJobPosts\Domain\Model\Jobpost
     */
    public function getModel(): Jobpost {
        return $this->jobpost;
    }
}
