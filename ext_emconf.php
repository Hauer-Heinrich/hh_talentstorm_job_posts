<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "hh_talenstorm_job_posts"
 *
 * Auto generated by Extension Builder 2023-01-05
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['hh_talentstorm_job_posts'] = [
    'title' => 'Hauer-Heinrich - Simple job posts',
    'description' => 'Add Talentstorm jobs for EXT: hh_simple_job_posts.',
    'category' => 'plugin',
    'author' => 'Christian Hackl',
    'author_email' => 'chackl@hauer-heinrich.de',
    'state' => 'beta',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'hh_simple_job_posts' => ''
        ],
        'conflicts' => [],
        'suggests' => [
            'hh_seo' => ''
        ],
    ],
];
