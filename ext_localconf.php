<?php
defined('TYPO3') || die();

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function() {
    // wizards
    ExtensionManagementUtility::addPageTSConfig(
        'TCEFORM {
            tt_content.pi_flexform {
                hhsimplejobposts_jobslist {
                    sDEF {
                        settings\.useExternalApi {
                            addItems {
                                talentstorm = Talentstorm
                            }
                        }
                    }
                }
            }
        }'
    );
});
