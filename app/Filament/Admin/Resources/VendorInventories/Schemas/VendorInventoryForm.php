<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Schemas;

use Filament\Schemas\Schema;

class VendorInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
