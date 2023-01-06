<?php
declare(strict_types=1);

namespace HauerHeinrich\HhTalentstormJobPosts\EventListener;

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
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use \HauerHeinrich\HhSimpleJobPosts\Event\JobpostsListEvent;
use \HauerHeinrich\HhTalentstormJobPosts\Http\TalentstormRequest;
use \HauerHeinrich\HhTalentstormJobPosts\Utility\TalentstormJobpostMapper;

final class TalentstormJobpostsListener {

    private ConfigurationManager $configurationManager;

    private TalentstormRequest $talentstormRequest;

    private TalentstormJobpostMapper $talentstormJobpostMapper;

    private array $settings = [];

    // We need the RequestFactory for creating and sending a request,
    // so we inject it into the class using constructor injection.
    public function __construct(
        ConfigurationManager $configurationManager,
        TalentstormRequest $talentstormRequest,
        TalentstormJobpostMapper $talentstormJobpostMapper
    ) {
        $this->configurationManager = $configurationManager;
        $this->talentstormRequest = $talentstormRequest;
        $this->talentstormJobpostMapper = $talentstormJobpostMapper;
        $this->setSettings();
    }

    /**
     * set own extension typoscript settings
     */
    public function setSettings(): void {
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->settings = $extbaseFrameworkConfiguration['plugin.']['tx_hhtalentstormjobposts.']['settings.'];
    }

    public function getExternalJobpostsFromApi(JobpostsListEvent $event): void {
        $values = $event->getAssignedValues();

        if(isset($event->getSettings()['useExternalApi']) && $event->getSettings()['useExternalApi'] === 'talentstorm') {
            $this->talentstormRequest->setApiUrl($this->settings['talentstorm.']['apiUrl']);
            $this->talentstormRequest->setApiKey($this->settings['talentstorm.']['apiKey']);
            $response = $this->talentstormRequest->request();

            if(empty($response['error'])) {
                $this->talentstormJobpostMapper->setDataArray($response, $values['jobsStorage']);
                $values['jobposts'] = $this->talentstormJobpostMapper->mapMultipleArrayToObject();
                $event->setAssignedValues($values);

                return;
            }

            $values['error'] = [$response['error']];

            $event->setAssignedValues($values);
        }
    }
}
