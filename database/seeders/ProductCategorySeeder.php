<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\ProductCategoryStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'name_en' => 'Leafy vegetables',
                'name_km' => 'បន្លែស្លឹក',
                'description_en' => 'Vegetables grown primarily for their leaves.',
                'description_km' => 'បន្លែដែលដាំដុះជាចម្បងសម្រាប់ស្លឹករបស់វា។',
                'image_url' => 'leafy_vegetables.jpg',
            ],
            [
                'id' => 2,
                'name_en' => 'Fruit vegetables',
                'name_km' => 'បន្លែផ្លែ',
                'description_en' => 'Vegetables that bear fruit, such as tomatoes and peppers.',
                'description_km' => 'បន្លែដែលបង្កើតផ្លែ ដូចជាប៉េងប៉ោះ និងម្ទេស។',
                'image_url' => 'fruit_vegetables.jpg',
            ],
            [
                'id' => 3,
                'name_en' => 'Root and tuber vegetables',
                'name_km' => 'បន្លែឫសនិងមើម',
                'description_en' => 'Vegetables grown underground, like carrots and potatoes.',
                'description_km' => 'បន្លែដែលដាំនៅក្រោមដី ដូចជាការ៉ុត និងដំឡូង។',
                'image_url' => 'root_and_tuber_vegetables.jpg',
            ],
            [
                'id' => 4,
                'name_en' => 'Bulb and stem vegetables',
                'name_km' => 'បន្លែមើមនិងដើម',
                'description_en' => 'Vegetables with edible bulbs or stems, like onions and celery.',
                'description_km' => 'បន្លែដែលមានមើម ឬដើមដែលអាចបរិភោគបាន ដូចជាខ្ទឹមបារាំង និងស៊ែលឺរី។',
                'image_url' => 'bulb_and_stem_vegetables.jpg',
            ],
            [
                'id' => 5,
                'name_en' => 'Legume vegetables',
                'name_km' => 'បន្លែសណ្តែក',
                'description_en' => 'Vegetables in the legume family, like beans and peas.',
                'description_km' => 'បន្លែក្នុងគ្រួសារសណ្តែក ដូចជាសណ្តែក និងសណ្តែកខៀវ។',
                'image_url' => 'legume_vegetables.jpg',
            ],
            [
                'id' => 6,
                'name_en' => 'Indigenous and wild vegetables',
                'name_km' => 'បន្លែព្រៃនិងក្នុងស្រុក',
                'description_en' => 'Native and wild-foraged edible plants.',
                'description_km' => 'រុក្ខជាតិដែលអាចបរិភោគបានពីធម្មជាតិ និងក្នុងស្រុក។',
                'image_url' => 'indigenous_and_wild_vegetables.jpg',
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create([
                'name_en' => $category['name_en'],
                'name_km' => $category['name_km'],
                'description_en' => $category['description_en'],
                'description_km' => $category['description_km'],
                'slug' => Str::slug($category['name_en']),
                'image_url' => $category['image_url'],
                'product_category_status_id' => ProductCategoryStatus::ACTIVE_ID,
            ]);
        }
    }
}
