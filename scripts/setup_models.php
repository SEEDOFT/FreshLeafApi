<?php

$models = [
    'UserCartStatus' => ['active', 'abandoned', 'converted'],
    'UserCartType' => ['default', 'scheduled'],
    'UserCartItemStatus' => ['active', 'saved_for_later'],
    'UserCartItemType' => ['standard', 'subscription'],
    'UserWishlistStatus' => ['active', 'archived'],
    'UserWishlistType' => ['default', 'shared'],
    'UserWishlistItemStatus' => ['active', 'inactive'],
    'UserWishlistItemType' => ['default'],
];

foreach ($models as $model => $statuses) {
    $modelContent = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$model} extends Model\n{\n    protected \$fillable = ['code', 'name'];\n\n";
    $i = 1;
    foreach ($statuses as $status) {
        $const = strtoupper($status);
        $modelContent .= "    public const int {$const} = {$i};\n";
        $i++;
    }
    $modelContent .= "}\n";
    file_put_contents('D:/FreshLeaf/FreshLeafApi/app/Models/'.$model.'.php', $modelContent);

    $seederContent = "<?php\n\ndeclare(strict_types=1);\n\nnamespace Database\Seeders;\n\nuse App\Models\\{$model};\nuse Illuminate\Database\Seeder;\n\nclass {$model}Seeder extends Seeder\n{\n    public function run(): void\n    {\n        \$items = [\n";
    $i = 1;
    foreach ($statuses as $status) {
        $name = ucfirst(str_replace('_', ' ', $status));
        $seederContent .= "            ['id' => {$i}, 'code' => '{$status}', 'name' => '{$name}'],\n";
        $i++;
    }
    $seederContent .= "        ];\n\n        foreach (\$items as \$item) {\n            {$model}::query()->updateOrCreate(['id' => \$item['id']], ['code' => \$item['code'], 'name' => \$item['name']]);\n        }\n    }\n}\n";
    file_put_contents('D:/FreshLeaf/FreshLeafApi/database/seeders/'.$model.'Seeder.php', $seederContent);
}
