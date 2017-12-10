<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Laravel ULS
    |--------------------------------------------------------------------------
    */

    /*
     * ULS Version to use
     *
     * By default we'll use 2
     */
    'version' => env('ULS_VERSION', 2),

    /*
     * Set the JSON Web Key retrieved from VATUSA's Facility Management
     */
    'jwk' => json_decode(env('ULS_JWK', []), true),

    /*
     * Facility 3 letter identifier
     */
    'facility' => env('ULS_FACILITY', 'ZZZ')
];
