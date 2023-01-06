<?php
defined('TYPO3_MODE') || die();

call_user_func(function() {
    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
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
