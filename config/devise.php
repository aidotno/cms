<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Media
  |--------------------------------------------------------------------------
  |
  | Configuration for the media manager
  |
  */

  'media' => [
    'root-directory' => 'media',
  ],

  /*
  |--------------------------------------------------------------------------
  | Mothership
  |--------------------------------------------------------------------------
  |
  | Configuration for mothership
  |
  */
  'mothership' => [
    'api-key' => env('MOTHERSHIP_API_KEY', null),
    'analytics-view' => env('MOTHERSHIP_VIEW', null),
    'project-key' => env('MOTHERSHIP_PROJECT_KEY', null)
  ]
];
