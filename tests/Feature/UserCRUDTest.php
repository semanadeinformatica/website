<?php

namespace Tests\Feature;

use Tests\CRUDTestCase;

class UserCRUDTest extends CRUDTestCase
{
    protected array $validCreateData = [
        // Admin
        [
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'type' => 'admin',
        ],
        // Participant
        [
            'name' => 'Test participant',
            'email' => 'participant@example.com',
            'type' => 'participant',
        ],
        [
            'name' => 'Test participant with social media',
            'email' => 'socialmedia@example.com',
            'type' => 'participant',
            'social_media' => [
                'email' => 'test@example.com',
                'facebook' => 'test',
                'github' => 'test',
                'instagram' => 'test',
                'linkedin' => 'test',
                'twitter' => 'test',
                'website' => 'https://example.com',
            ],
        ],
        // Company
        [
            'name' => 'Test company',
            'email' => 'company@example.com',
            'type' => 'company',
        ],
        [
            'name' => 'Test company with social media and description',
            'email' => 'companysocialmedia@example.com',
            'type' => 'company',
            'description' => 'Test description',
            'social_media' => [
                'email' => 'test@example.com',
                'facebook' => 'test',
                'github' => 'test',
                'instagram' => 'test',
                'linkedin' => 'test',
                'twitter' => 'test',
                'website' => 'https://example.com',
            ],
        ],
    ];

    protected array $validUpdateData = [
        // Admin
        [
            'name' => 'Test participant updated',
            'email' => 'test@exampl.com',
            'type' => 'participant',
        ],
    ];
}
