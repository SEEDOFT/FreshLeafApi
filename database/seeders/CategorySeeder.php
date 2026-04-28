<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
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
            [
                'name_en' => 'Leafy Vegetables',
                'name_km' => 'បន្លែស្លឹកសរីរាង្គ',
                'description_en' => 'Tender, nutrient-rich leafy greens grown strictly without chemical pesticides or synthetic fertilizers.',
                'description_km' => 'បន្លែស្លឹកស្រស់ៗ ដាំដុះតាមបែបធម្មជាតិពិតៗ ដោយមិនប្រើប្រាស់ថ្នាំសម្លាប់សត្វល្អិត ឬជីគីមី។',
            ],
            [
                'name_en' => 'Fruit Vegetables',
                'name_km' => 'បន្លែផ្លែសរីរាង្គ',
                'description_en' => 'Crisp and juicy vegetables grown naturally from the flower of the plant.',
                'description_km' => 'បន្លែផ្លែស្រួយឆ្ងាញ់ ដាំដោយសុវត្ថិភាព។ ធានាថាគ្មានជាតិគីមី ស័ក្តិសមបំផុតសម្រាប់ញ៉ាំឆៅ ឬយកទៅចម្អិន។',
            ],
            [
                'name_en' => 'Root and Tuber Vegetables',
                'name_km' => 'បន្លែមើមសរីរាង្គ',
                'description_en' => 'Earthy, energy-rich root vegetables grown in healthy, chemical-free soil.',
                'description_km' => 'បន្លែមើមដុះក្នុងដីមានជីជាតិពីធម្មជាតិ គ្មានជាតិគីមីជ្រាបចូល។ ល្អសម្រាប់យកទៅរំងាស់ទឹកស៊ុប ឬស្ងោរញ៉ាំដោយសុវត្ថិភាព។',
            ],
            [
                'name_en' => 'Bulb and Stem Vegetables',
                'name_km' => 'បន្លែដើម និងមើមខ្ទឹមសរីរាង្គ',
                'description_en' => 'Highly aromatic bulb and stem plants grown organically.',
                'description_km' => 'បន្លែប្រភេទដើម និងមើមខ្ទឹម ដែលដាំដុះតាមបែបសរីរាង្គ។ ជាគ្រឿងផ្សំដ៏សំខាន់សម្រាប់បង្កើនរសជាតិមុខម្ហូបខ្មែរឲ្យកាន់តែឈ្ងុយឆ្ងាញ់។',
            ],
            [
                'name_en' => 'Leguminous Vegetables',
                'name_km' => 'បន្លែសណ្តែកសរីរាង្គ',
                'description_en' => 'Protein-rich pod vegetables cultivated without harmful chemicals.',
                'description_km' => 'បន្លែប្រភេទសណ្តែក សម្បូរដោយប្រូតេអ៊ីន ដាំដោយមិនប្រើប្រាស់សារធាតុគីមីពុល។',
            ],
            [
                'name_en' => 'Indigenous and Semi-wild Vegetables',
                'name_km' => 'បន្លែក្នុងស្រុក និងពាក់កណ្តាលព្រៃសរីរាង្គ',
                'description_en' => 'Traditional and wild-harvested vegetables unique to the Cambodian landscape.',
                'description_km' => 'បន្លែប្រពៃណី និងបន្លែព្រៃដែលប្រមូលផលតាមបែបធម្មជាតិ មានតែនៅក្នុងតំបន់ស្រុកស្រែចម្ការរបស់កម្ពុជា។',
            ],
        ];

        foreach ($categories as $data) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($data['name_en'])],
                $data
            );
        }
    }
}
