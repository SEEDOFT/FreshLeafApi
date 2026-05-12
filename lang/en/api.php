<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication API Messages
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'login_success' => 'Login successful',
        'login_failed' => 'Invalid login details',
        'invalid_password_format' => 'Invalid password format',
        'invalid_password' => 'Invalid password',
        'account_not_active' => 'Your account is not active',
        'password_verified' => 'Password verified',
        'password_updated' => 'Password updated',
        'tokens_revoked' => 'Tokens revoked',
        'register_success' => 'User registered successfully',
    ],

    /*
    |--------------------------------------------------------------------------
    | Device API Messages
    |--------------------------------------------------------------------------
    */
    'device' => [
        'registered' => 'Device registered successfully',
        'deactivated' => 'Device deactivated successfully',
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile API Messages
    |--------------------------------------------------------------------------
    */
    'profile' => [
        'retrieved' => 'Profile retrieved successfully',
        'updated' => 'Profile updated successfully',
        'replaced' => 'Profile replaced successfully',
        'deleted' => 'User deleted successfully',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wallet API Messages
    |--------------------------------------------------------------------------
    */
    'wallet' => [
        'retrieved' => 'Wallet retrieved successfully',
        'wallets_retrieved' => 'Wallets retrieved successfully',
        'history_retrieved' => 'Wallet history retrieved successfully',
        'not_found' => 'Wallet not found',
        'insufficient_balance' => 'Insufficient balance',
        'transfer_success' => 'Transfer completed successfully',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wallet Transaction API Messages
    |--------------------------------------------------------------------------
    */
    'wallet_transaction' => [
        'retrieved' => 'Wallet transaction retrieved successfully',
        'transactions_retrieved' => 'Wallet transactions retrieved successfully',
        'created' => 'Wallet transaction created successfully',
        'updated' => 'Wallet transaction updated successfully',
        'deleted' => 'Wallet transaction deleted successfully',
        'not_found' => 'Wallet transaction not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Address API Messages
    |--------------------------------------------------------------------------
    */
    'address' => [
        'retrieved' => 'Address retrieved successfully',
        'addresses_retrieved' => 'Addresses retrieved successfully',
        'created' => 'Address created successfully',
        'updated' => 'Address updated successfully',
        'replaced' => 'Address replaced successfully',
        'deleted' => 'Address deleted successfully',
        'not_found' => 'Address not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Method API Messages
    |--------------------------------------------------------------------------
    */
    'payment_method' => [
        'retrieved' => 'Payment method retrieved successfully',
        'payment_methods_retrieved' => 'Payment methods retrieved successfully',
        'created' => 'Payment method created successfully',
        'updated' => 'Payment method updated successfully',
        'replaced' => 'Payment method replaced successfully',
        'deleted' => 'Payment method deleted successfully',
        'not_found' => 'Payment method not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Method Type API Messages
    |--------------------------------------------------------------------------
    */
    'payment_method_type' => [
        'retrieved' => 'Payment method type retrieved successfully',
        'payment_method_types_retrieved' => 'Payment method types retrieved successfully',
        'not_found' => 'Payment method type not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Support Chat API Messages
    |--------------------------------------------------------------------------
    */
    'support_chat' => [
        'session_created' => 'Support chat session created',
        'message_sent' => 'Message sent successfully',
        'messages_retrieved' => 'Messages retrieved successfully',
        'typing' => 'Typing indicator sent',
        'unread_retrieved' => 'Unread count retrieved',
        'no_unread' => 'No unread messages',
        'unauthorized_access' => 'Unauthorized access to ticket history',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Chat API Messages
    |--------------------------------------------------------------------------
    */
    'ai_chat' => [
        'chat_started' => 'AI chat session started',
        'response_received' => 'AI response received',
        'history_retrieved' => 'Chat history retrieved',
        'session_not_found' => 'Chat session not found',
        'service_unavailable' => 'AI service is currently unavailable',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Chat API Messages
    |--------------------------------------------------------------------------
    */
    'ai_chat' => [
        'chat_started' => 'AI chat session started',
        'response_received' => 'AI response received',
        'history_retrieved' => 'Chat history retrieved',
        'session_not_found' => 'Chat session not found',
        'service_unavailable' => 'AI service is currently unavailable',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product API Messages
    |--------------------------------------------------------------------------
    */
    'product' => [
        'retrieved' => 'Product retrieved successfully',
        'products_retrieved' => 'Products retrieved successfully',
        'not_found' => 'Product not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart API Messages
    |--------------------------------------------------------------------------
    */
    'cart' => [
        'retrieved' => 'Cart retrieved successfully',
        'item_added' => 'Item added to cart successfully',
        'item_updated' => 'Cart item updated successfully',
        'item_removed' => 'Item removed from cart successfully',
        'checked_out' => 'Cart checked out successfully',
        'empty' => 'Your cart is empty',
        'insufficient_stock' => 'Not enough stock available',
        'insufficient_stock_total' => 'Not enough stock available for the total quantity',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wishlist API Messages
    |--------------------------------------------------------------------------
    */
    'wishlist' => [
        'retrieved' => 'Wishlist retrieved successfully',
        'item_added' => 'Item added to wishlist',
        'item_removed' => 'Item removed from wishlist',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category API Messages
    |--------------------------------------------------------------------------
    */
    'category' => [
        'retrieved' => 'Category retrieved successfully',
        'categories_retrieved' => 'Categories retrieved successfully',
        'not_found' => 'Category not found or inactive',
    ],

    /*
    |--------------------------------------------------------------------------
    | PIN API Messages
    |--------------------------------------------------------------------------
    */
    'pin' => [
        'already_set' => 'PIN already set. Use update endpoint to change it.',
        'set_success' => 'PIN set successfully',
        'invalid_current_pin' => 'Invalid current PIN',
        'updated_success' => 'PIN updated successfully',
        'not_set' => 'PIN not set',
        'invalid_pin' => 'Invalid PIN',
        'verified' => 'PIN verified',
        'reset_success' => 'PIN reset successfully',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Notification API Messages
    |--------------------------------------------------------------------------
    */
    'test_notification' => [
        'sent' => 'Test notification sent',
        'user_not_found' => 'User not found',
        'no_devices' => 'User has no registered devices',
    ],

    /*
    |--------------------------------------------------------------------------
    | General API Messages
    |--------------------------------------------------------------------------
    */
    'general' => [
        'success' => 'Success',
        'error' => 'Error',
        'not_found' => 'Resource not found',
        'validation_error' => 'Validation error',
        'unauthorized' => 'Unauthorized',
        'forbidden' => 'Forbidden',
        'server_error' => 'Server error',
        'endpoint_not_found' => 'Endpoint not found',
    ],
];
