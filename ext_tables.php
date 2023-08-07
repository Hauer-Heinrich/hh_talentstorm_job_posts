<?php
defined('TYPO3') || die();

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(static function() {
    ExtensionManagementUtility::addStaticFile('hh_talentstorm_job_posts', 'Configuration/TypoScript', 'Job posts - Talentstorm');
});
