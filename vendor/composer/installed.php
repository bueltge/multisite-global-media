<?php return array(
    'root' => array(
        'name' => 'bueltge/multisite-global-media',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'f40da128d672116dd0896180a0152269d601d12f',
        'type' => 'wordpress-muplugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'bueltge/multisite-global-media' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'f40da128d672116dd0896180a0152269d601d12f',
            'type' => 'wordpress-muplugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'composer/installers' => array(
            'pretty_version' => 'v1.11.0',
            'version' => '1.11.0.0',
            'reference' => 'ae03311f45dfe194412081526be2e003960df74b',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
