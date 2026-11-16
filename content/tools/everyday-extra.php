<?php
/**
 * UniVerse — Everyday life (expanded catalogue).
 * 6 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'recipe-scaler',
        'name' => 'Recipe Scaler',
        'category' => 'everyday',
        'description' => 'Scale an ingredient amount up or down for a different number of servings.',
        'icon' => 'scale',
        'keywords' => ['recipe', 'servings', 'cooking', 'scale', 'ingredients'],
        'inputs' => [
            ['name' => 'original', 'label' => 'Original servings', 'type' => 'number', 'default' => 4, 'min' => 0.5, 'step' => 'any', 'required' => true],
            ['name' => 'desired', 'label' => 'Desired servings', 'type' => 'number', 'default' => 6, 'min' => 0.5, 'step' => 'any', 'required' => true],
            ['name' => 'amount', 'label' => 'Ingredient amount', 'type' => 'number', 'default' => 200, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const original = h.n(v.original), desired = h.n(v.desired), amount = h.n(v.amount);
    if (original <= 0 || desired <= 0) throw new Error('Servings must be greater than zero.');
    if (amount < 0) throw new Error('The ingredient amount cannot be negative.');
    const factor = desired / original;
    return [
        { label: 'Scaled amount', value: amount * factor },
        { label: 'Scale factor', value: factor, unit: '×' },
        { label: 'Original amount', value: amount },
        { label: 'Servings', value: desired },
    ];
JS,
        'tests' => [
            ['input' => ['original' => 4, 'desired' => 6, 'amount' => 200], 'expect' => ['Scaled amount' => 300, 'Scale factor' => 1.5, 'Original amount' => 200, 'Servings' => 6]],
            ['input' => ['original' => 2, 'desired' => 1, 'amount' => 1], 'expect' => ['Scaled amount' => 0.5, 'Scale factor' => 0.5]],
            ['input' => ['original' => 3, 'desired' => 3, 'amount' => 45], 'expect' => ['Scaled amount' => 45, 'Scale factor' => 1]],
        ],
    ],
    [
        'slug' => 'rent-split-calculator',
        'name' => 'Rent Split Calculator',
        'category' => 'everyday',
        'description' => 'Split rent fairly between two people based on their incomes.',
        'icon' => 'home',
        'keywords' => ['rent', 'split', 'income', 'roommates', 'budget'],
        'inputs' => [
            ['name' => 'rent', 'label' => 'Monthly rent', 'type' => 'number', 'default' => 2000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'income1', 'label' => 'Person 1 income', 'type' => 'number', 'default' => 3000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'income2', 'label' => 'Person 2 income', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const rent = h.n(v.rent), income1 = h.n(v.income1), income2 = h.n(v.income2);
    if (rent < 0) throw new Error('Rent cannot be negative.');
    if (income1 < 0 || income2 < 0) throw new Error('Incomes cannot be negative.');
    const total = income1 + income2;
    if (total <= 0) throw new Error('At least one income must be greater than zero.');
    const share1 = (rent * income1) / total;
    return [
        { label: 'Person 1 pays', value: share1 },
        { label: 'Person 2 pays', value: rent - share1 },
        { label: 'Person 1 share', value: (income1 / total) * 100, unit: '%' },
        { label: 'Person 2 share', value: (income2 / total) * 100, unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['rent' => 2000, 'income1' => 3000, 'income2' => 1000], 'expect' => ['Person 1 pays' => 1500, 'Person 2 pays' => 500, 'Person 1 share' => 75, 'Person 2 share' => 25]],
            ['input' => ['rent' => 1500, 'income1' => 2000, 'income2' => 2000], 'expect' => ['Person 1 pays' => 750, 'Person 2 pays' => 750, 'Person 1 share' => 50, 'Person 2 share' => 50]],
        ],
    ],
    [
        'slug' => 'pizza-size-comparator',
        'name' => 'Pizza Size Comparator',
        'category' => 'everyday',
        'description' => 'Compare two pizza sizes and prices to find the better value.',
        'icon' => 'circle',
        'keywords' => ['pizza', 'size', 'value', 'price', 'area'],
        'inputs' => [
            ['name' => 'diameter1', 'label' => 'Pizza A diameter (in)', 'type' => 'number', 'default' => 12, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'price1', 'label' => 'Pizza A price', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'diameter2', 'label' => 'Pizza B diameter (in)', 'type' => 'number', 'default' => 18, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'price2', 'label' => 'Pizza B price', 'type' => 'number', 'default' => 16, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const d1 = h.n(v.diameter1), p1 = h.n(v.price1), d2 = h.n(v.diameter2), p2 = h.n(v.price2);
    if (d1 <= 0 || d2 <= 0) throw new Error('Diameters must be greater than zero.');
    if (p1 <= 0 || p2 <= 0) throw new Error('Prices must be greater than zero.');
    const area1 = Math.PI * (d1 / 2) ** 2;
    const area2 = Math.PI * (d2 / 2) ** 2;
    const perArea1 = p1 / area1;
    const perArea2 = p2 / area2;
    return [
        { label: 'Pizza A area', value: area1, unit: 'in²' },
        { label: 'Pizza B area', value: area2, unit: 'in²' },
        { label: 'Size difference', value: ((area2 - area1) / area1) * 100, unit: '%' },
        { label: 'Pizza A price per area', value: perArea1 },
        { label: 'Pizza B price per area', value: perArea2 },
        { label: 'Better value', value: perArea1 === perArea2 ? 'Equal' : perArea1 < perArea2 ? 'Pizza A' : 'Pizza B' },
    ];
JS,
        'tests' => [
            ['input' => ['diameter1' => 12, 'price1' => 10, 'diameter2' => 18, 'price2' => 16], 'expect' => ['Pizza A area' => 113.09733552923255, 'Pizza B area' => 254.46900494077323, 'Size difference' => 125, 'Pizza A price per area' => 0.08841941282883074, 'Pizza B price per area' => 0.06287602690050187, 'Better value' => 'Pizza B']],
            ['input' => ['diameter1' => 10, 'price1' => 8, 'diameter2' => 10, 'price2' => 12], 'expect' => ['Pizza A area' => 78.53981633974483, 'Better value' => 'Pizza A']],
        ],
    ],
    [
        'slug' => 'aquarium-volume-calculator',
        'name' => 'Aquarium Volume Calculator',
        'category' => 'everyday',
        'description' => 'Calculate aquarium volume in litres and gallons from its dimensions.',
        'icon' => 'fuel',
        'keywords' => ['aquarium', 'fish tank', 'volume', 'litres', 'gallons'],
        'inputs' => [
            ['name' => 'length', 'label' => 'Length (cm)', 'type' => 'number', 'default' => 60, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'width', 'label' => 'Width (cm)', 'type' => 'number', 'default' => 30, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Height (cm)', 'type' => 'number', 'default' => 30, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const length = h.n(v.length), width = h.n(v.width), height = h.n(v.height);
    if (length <= 0 || width <= 0 || height <= 0) throw new Error('All dimensions must be greater than zero.');
    const litres = (length * width * height) / 1000;
    return [
        { label: 'Volume', value: litres, unit: 'L' },
        { label: 'US gallons', value: litres / 3.785411784, unit: 'gal' },
        { label: 'Imperial gallons', value: litres / 4.54609, unit: 'gal' },
        { label: 'Water weight', value: litres, unit: 'kg' },
    ];
JS,
        'tests' => [
            ['input' => ['length' => 60, 'width' => 30, 'height' => 30], 'expect' => ['Volume' => 54, 'US gallons' => 14.265290827340015, 'Imperial gallons' => 11.87833940815074, 'Water weight' => 54]],
            ['input' => ['length' => 100, 'width' => 40, 'height' => 50], 'expect' => ['Volume' => 200, 'Water weight' => 200]],
        ],
    ],
    [
        'slug' => 'coffee-ratio-calculator',
        'name' => 'Coffee Ratio Calculator',
        'category' => 'everyday',
        'description' => 'Find how much water to use for a coffee-to-water brewing ratio.',
        'icon' => 'fuel',
        'keywords' => ['coffee', 'ratio', 'brewing', 'water', 'caffeine'],
        'inputs' => [
            ['name' => 'coffee', 'label' => 'Coffee (g)', 'type' => 'number', 'default' => 18, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'ratio', 'label' => 'Water per coffee (1:x)', 'type' => 'number', 'default' => 16, 'min' => 1, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const coffee = h.n(v.coffee), ratio = h.n(v.ratio);
    if (coffee < 0) throw new Error('Coffee cannot be negative.');
    if (ratio <= 0) throw new Error('The ratio must be greater than zero.');
    const water = coffee * ratio;
    return [
        { label: 'Water', value: water, unit: 'ml' },
        { label: 'Coffee', value: coffee, unit: 'g' },
        { label: 'Ratio', value: `1:${ratio}` },
        { label: 'Cups (240 ml)', value: water / 240 },
    ];
JS,
        'tests' => [
            ['input' => ['coffee' => 18, 'ratio' => 16], 'expect' => ['Water' => 288, 'Coffee' => 18, 'Ratio' => '1:16', 'Cups (240 ml)' => 1.2]],
            ['input' => ['coffee' => 30, 'ratio' => 15], 'expect' => ['Water' => 450, 'Ratio' => '1:15', 'Cups (240 ml)' => 1.875]],
        ],
    ],
    [
        'slug' => 'subscription-cost-calculator',
        'name' => 'Subscription Cost Calculator',
        'category' => 'everyday',
        'description' => 'Add up recurring subscriptions and see the annual and long-term cost.',
        'icon' => 'chart-line',
        'keywords' => ['subscription', 'monthly cost', 'annual cost', 'budget', 'recurring'],
        'inputs' => [
            ['name' => 'price', 'label' => 'Monthly price each', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'count', 'label' => 'Number of subscriptions', 'type' => 'number', 'default' => 3, 'min' => 1, 'step' => '1', 'required' => true],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 2, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const price = h.n(v.price), count = Math.trunc(h.n(v.count)), years = h.n(v.years);
    if (price < 0 || years < 0) throw new Error('Values cannot be negative.');
    if (count <= 0) throw new Error('Enter at least one subscription.');
    const monthly = price * count;
    return [
        { label: 'Monthly total', value: monthly },
        { label: 'Annual total', value: monthly * 12 },
        { label: 'Total over period', value: monthly * 12 * years },
        { label: 'Subscriptions', value: count, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['price' => 10, 'count' => 3, 'years' => 2], 'expect' => ['Monthly total' => 30, 'Annual total' => 360, 'Total over period' => 720, 'Subscriptions' => 3]],
            ['input' => ['price' => 15.99, 'count' => 1, 'years' => 1], 'expect' => ['Monthly total' => 15.99, 'Annual total' => 191.88, 'Total over period' => 191.88]],
        ],
    ],
];
