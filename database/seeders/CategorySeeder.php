<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Leafy Vegetables',
            'Fruiting Vegetables',
            'Root Vegetables',
            'Herbs / Aromatic Plants',
            'Legumes',
        ];

        $legacyToTargetSlugMap = [
            'leafy-greens-herbs' => 'leafy-vegetables',
            'local-vegetables' => 'fruiting-vegetables',
            'fresh-fruits' => 'root-vegetables',
            'rice-noodles-dry-goods' => 'herbs-aromatic-plants',
            'fish-meat-eggs' => 'legumes',
        ];

        foreach ($legacyToTargetSlugMap as $legacySlug => $targetSlug) {
            $targetName = collect($categories)
                ->first(static fn (string $categoryName): bool => Str::slug($categoryName) === $targetSlug);

            if (! $targetName) {
                continue;
            }

            ProductCategory::query()
                ->where('slug', $legacySlug)
                ->update([
                    'name' => $targetName,
                    'slug' => $targetSlug,
                ]);
        }

        foreach ($categories as $categoryName) {
            ProductCategory::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName]
            );
        }
    }
}
