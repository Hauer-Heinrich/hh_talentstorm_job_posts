<?php
defined('TYPO3_MODE') || die();

call_user_func(static function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('hh_talentstorm_job_posts', 'Configuration/TypoScript', 'Job posts - Talentstorm');
});
