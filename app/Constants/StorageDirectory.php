<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Defines the storage directory constants for the application.
 *
 * These constants are used across the application to reference the different
 * directories within the storage system.
 *
 * @internal
 */
final class StorageDirectory
{
    /**
     * The directory for storing user-related files.
     *
     * @var string
     */
    public const string USERS = 'users';

    /**
     * The directory for storing product-related files.
     *
     * @var string
     */
    public const string PRODUCTS = 'products';

    /**
     * The directory for storing vendor verification files.
     *
     * @var string
     */
    public const string VENDOR_VERIFICATION = 'vendor_verifications';

    /**
     * The directory for storing public shop/store assets (banners, QR codes).
     *
     * @var string
     */
    public const string SHOPS = 'shops';

    /**
     * The directory for storing support message files.
     *
     * @var string
     */
    public const string SUPPORT_MESSAGES = 'support_messages';

    /**
     * The directory for storing inventory adjustment files.
     *
     * @var string
     */
    public const string INVENTORY_ADJUSTMENTS = 'inventory_adjustments';

    /**
     * The directory for storing payment method files.
     *
     * @var string
     */
    public const string PAYMENT_METHODS = 'payment_methods';

    /**
     * The directory for storing product category files.
     *
     * @var string
     */
    public const string PRODUCT_CATEGORIES = 'product_categories';

    /**
     * The directory for storing product batches.
     *
     * @var string
     */
    public const string PRODUCT_BATCHES = 'product_batches';

    /**
     * The directory for storing chat attachments.
     *
     * @var string
     */
    public const string CHAT_ATTACHMENTS = 'chat_attachments';

    /**
     * The directory for storing order payment proofs.
     *
     * @var string
     */
    public const string ORDER_PROOFS = 'order_proofs';

    /**
     * The directory for storing order delivery proofs.
     *
     * @var string
     */
    public const string DELIVERY_PROOFS = 'delivery_proofs';
}
