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
    'jwk' => json_deocde(env('ULS_JWK', []), true)
];
