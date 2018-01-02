<?php

return [
  'path' => base_path('temp-lib'),
  'namespaces' => [
    'App\\Models' => 'App\\TraitMapper'
  ],
  'path_map' => app_path('ClassDbMap.php'),
  'fk_field_pattern' => '/^(.*)_id/',
];

