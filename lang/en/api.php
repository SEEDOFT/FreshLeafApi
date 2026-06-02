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
        'set_default' => 'Default address set successfully',
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
    'notifications' => [
        'retrieved' => 'Notifications retrieved successfully',
        'not_found' => 'Notification not found',
        'marked_read' => 'Notification marked as read',
        'all_marked_read' => 'All notifications marked as read',
        'new_order_title' => 'New Order Received',
        'new_order_body' => 'A new order #:order_number has been placed.',
        'new_order_alert_body' => 'You have a new order #:order_number to prepare.',
        'new_order_alert_template' => 'You have a new order #',
        'order_status_updated_title' => 'Order Status Updated',
        'order_status_updated_body' => 'Your order #:order_number status has been updated to: :status.',
        'new_support_ticket_title' => 'New Support Ticket',
        'new_support_ticket_body' => 'User :name has started a new support chat.',
        'new_support_message_title' => 'New Support Message',
    ],

    'support_chat' => [
        'tickets_retrieved' => 'Support tickets retrieved successfully',
        'no_active_ticket' => 'No active ticket found',
        'session_retrieved' => 'Support session retrieved successfully',
        'session_created' => 'Support chat session created',
        'view_chat' => 'View Chat',
        'message_sent' => 'Message sent successfully',
        'messages_retrieved' => 'Messages retrieved successfully',
        'typing' => 'Typing indicator sent',
        'unread_retrieved' => 'Unread count retrieved',
        'no_unread' => 'No unread messages',
        'unauthorized_access' => 'Unauthorized access to ticket history',
    ],

    'chat' => [
        'conversations_retrieved' => 'Conversations retrieved successfully',
        'conversation_retrieved' => 'Conversation retrieved successfully',
        'cannot_chat_with_self' => 'You cannot chat with yourself',
        'unread_retrieved' => 'Unread count retrieved',
        'typing' => 'Typing indicator sent',
        'messages_retrieved' => 'Messages retrieved successfully',
        'message_sent' => 'Message sent successfully',
        'conversation_resolved' => 'This support ticket is resolved. Open a new ticket to continue chatting with support.',
        'conversation_not_found' => 'Conversation not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Product API Messages
    |--------------------------------------------------------------------------
    */
    'ai_chat' => [
        'chat_started' => 'AI chat session started',
        'history_retrieved' => 'Chat history retrieved successfully',
        'response_received' => 'AI response received',
        'session_not_found' => 'Chat session not found',
        'service_unavailable' => 'AI service is currently unavailable',
    ],

    'product' => [
        'retrieved' => 'Product retrieved successfully',
        'products_retrieved' => 'Products retrieved successfully',
        'not_found' => 'Product not found',
    ],

    /*
    |--------------------------------------------------------------------------
    | Order API Messages
    |--------------------------------------------------------------------------
    */
    'order' => [
        'retrieved' => 'Order retrieved successfully',
        'orders_retrieved' => 'Orders retrieved successfully',
        'cannot_cancel' => 'Cannot cancel this order',
        'cancelled' => 'Order cancelled successfully',
        'payment_successful' => 'Payment successful',
        'cannot_confirm_receipt' => 'Cannot confirm receipt for this order',
        'receipt_confirmed' => 'Order receipt confirmed successfully',
        'wallet_unauthorized' => 'Unauthorized wallet access.',
        'already_paid' => 'Order is already paid.',
        'insufficient_balance' => 'Insufficient wallet balance.',
        'payment_failed' => 'Payment failed: ',
        'not_awaiting_payment' => 'Order is not awaiting payment.',
        'status_updated' => 'updated',
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
        'invalid_payment_method' => 'Invalid payment method.',
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
        'unauthenticated' => 'Unauthenticated.',
        'document_unauthorized' => 'Unauthorized access to document.',
    ],
];
