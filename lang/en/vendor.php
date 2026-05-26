<?php

declare(strict_types=1);

return [
    'settings' => [
        'business_profile' => [
            'label' => 'Business Profile',
            'description' => 'Shop Description',
            'opening_time' => 'Opening Time',
            'closing_time' => 'Closing Time',
            'is_open' => 'Store Open',
            'store_info' => 'Store Information',
            'store_info_desc' => 'Publicly visible details about your business.',
            'success_notification' => 'Business profile updated.',
        ],
        'financial_details' => [
            'label' => 'Financial Details',
            'bank_name' => 'Bank Name',
            'account_holder' => 'Account Holder Name',
            'account_number' => 'Account Number',
            'qr_code' => 'Bank QR Code',
            'success_notification' => 'Financial details updated.',
            'description' => 'Financial details about your business.',
        ],
        'vendor_security' => [
            'label' => 'Security',
        ],
        'verification_docs' => [
            'label' => 'Verification',
            'section_title' => 'Identity & Store Verification',
            'section_desc' => 'Upload documents to verify your identity and organic status. These cannot be changed once verified.',
            'id_front' => 'ID Card (Front)',
            'id_back' => 'ID Card (Back)',
            'store_photo' => 'Farm / Store Photo',
            'organic_cert' => 'Organic Certificate',
            'lock_title' => 'Verification Lock',
            'lock_body' => 'You cannot change verification documents once verified.',
            'success_notification' => 'Verification documents updated.',
        ],
        'vendor_profile' => [
            'label' => 'Vendor Profile',
            'nav_label' => 'My Profile',
            'avatar' => 'Store / Owner Avatar',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'general_info' => 'General Information',
            'general_info_desc' => 'Update your basic profile information and avatar.',
            'preferences' => 'Preferences',
            'preferences_desc' => 'Customize your store dashboard language.',
            'language' => 'Display Language',
            'success_notification' => 'Profile updated successfully.',
        ],
    ],
    'navigation' => [
        'exchange_rate' => 'Exchange Rate',
    ],
];
