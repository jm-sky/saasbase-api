<?php

return [
    'tenant_invitation' => [
        'subject' => 'You are invited to join a tenant',
        'greeting' => 'Hello!',
        'intro' => 'You have been invited to join a tenant in SaaSBase.',
        'tenant_info' => 'Tenant: :name',
        'role_info' => 'Role: :role',
        'accept_button' => 'Accept Invitation',
        'ignore_info' => 'If you did not expect this invitation, you can ignore this email.',
        'accepted' => [
            'title' => 'Your invitation has been accepted!',
            'message' => ':name has joined :tenant as :role!',
        ],
        'rejected' => [
            'title' => 'Your invitation has been rejected!',
            'message' => ':name has rejected your invitation to join :tenant as :role!',
        ],
    ],
    'application_invitation' => [
        'subject' => 'You are invited to join SaaSBase',
        'greeting' => 'Hello!',
        'intro' => 'You have been invited to join SaaSBase.',
        'accept_button' => 'Accept Invitation',
        'ignore_info' => 'If you did not expect this invitation, you can ignore this email.',
    ],
    'password' => [
        'changed' => [
            'subject' => 'Password changed for :app!',
            'greeting' => 'Hi :name,',
            'message' => 'Your password has been changed for :app.',
            'warning' => 'If you did not change your password, please contact support.',
            'help' => 'If you have any questions, feel free to reach out anytime.',
            'title' => 'Password changed!',
        ],
        'reset' => [
            'subject' => 'Reset Password Notification',
            'message' => 'You are receiving this email because we received a password reset request for your account.',
            'button' => 'Reset Password',
            'expiry' => 'This password reset link will expire in :count minutes.',
            'ignore' => 'If you did not request a password reset, no further action is required.',
        ],
    ],
    'email_verification' => [
        'subject' => 'Verify Email Address',
    ],
    'welcome' => [
        'subject' => 'Welcome to :app!',
        'greeting' => 'Hi :name,',
        'message' => 'Thanks for signing up to :app.',
        'excitement' => 'We\'re thrilled to have you on board and can\'t wait to see what you build.',
        'dashboard_button' => 'Go to Dashboard',
        'help' => 'If you have any questions, feel free to reach out anytime.',
        'title' => 'Welcome to :app!',
        'notification_message' => 'Welcome :name, we\'re glad you\'re here!',
    ],
];
