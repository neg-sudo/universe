<?php
/**
 * UniVerse — Mathematics (expanded catalogue).
 * 14 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'standard-deviation-calculator',
        'name' => 'Standard Deviation Calculator',
        'category' => 'mathematics',
        'description' => 'Find the mean, variance and standard deviation of a list of numbers.',
        'icon' => 'chart-line',
        'featured' => true,
        'keywords' => ['standard deviation', 'variance', 'statistics', 'spread', 'mean'],
        'inputs' => [
            ['name' => 'numbers', 'label' => 'Numbers', 'type' => 'textarea', 'default' => '2, 4, 4, 4, 5, 5, 7, 9', 'required' => true, 'hint' => 'Commas, spaces and line breaks are accepted.'],
            ['name' => 'mode', 'label' => 'Data type', 'type' => 'select', 'default' => 'sample', 'options' => [
                ['value' => 'sample', 'label' => 'Sample (n − 1)'],
                ['value' => 'population', 'label' => 'Population (n)'],
            ]],
        ],
        'compute' => <<<'JS'
    const nums = (String(v.numbers).match(/-?\d+(?:\.\d+)?(?:e[+-]?\d+)?/gi) || []).map(Number).filter(Number.isFinite);
    if (nums.length === 0) throw new Error('Enter at least one number.');
    const n = nums.length;
    const mean = nums.reduce((a, b) => a + b, 0) / n;
    const sumSq = nums.reduce((total, x) => total + (x - mean) ** 2, 0);
    const divisor = v.mode === 'population' ? n : Math.max(1, n - 1);
    const variance = sumSq / divisor;
    return [
        { label: 'Mean', value: mean },
        { label: 'Variance', value: variance },
        { label: 'Standard deviation', value: Math.sqrt(variance) },
        { label: 'Count', value: n, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['numbers' => '2, 4, 4, 4, 5, 5, 7, 9', 'mode' => 'sample'], 'expect' => ['Mean' => 5, 'Variance' => 4.571428571428571, 'Standard deviation' => 2.138089935299395, 'Count' => 8]],
            ['input' => ['numbers' => '2 4 4 4 5 5 7 9', 'mode' => 'population'], 'expect' => ['Mean' => 5, 'Variance' => 4, 'Standard deviation' => 2]],
            ['input' => ['numbers' => '10', 'mode' => 'sample'], 'expect' => ['Mean' => 10, 'Variance' => 0, 'Standard deviation' => 0]],
        ],
    ],
    [
        'slug' => 'median-calculator',
        'name' => 'Median Calculator',
        'category' => 'mathematics',
        'description' => 'Find the median, quartiles and range of a list of numbers.',
        'icon' => 'list',
        'keywords' => ['median', 'quartile', 'statistics', 'range'],
        'inputs' => [
            ['name' => 'numbers', 'label' => 'Numbers', 'type' => 'textarea', 'default' => '1, 2, 3, 4, 5', 'required' => true],
        ],
        'compute' => <<<'JS'
    const nums = (String(v.numbers).match(/-?\d+(?:\.\d+)?(?:e[+-]?\d+)?/gi) || []).map(Number).filter(Number.isFinite);
    if (nums.length === 0) throw new Error('Enter at least one number.');
    const sorted = [...nums].sort((a, b) => a - b);
    const medianOf = (arr) => {
        if (arr.length === 0) return sorted[0];
        const m = Math.floor(arr.length / 2);
        return arr.length % 2 ? arr[m] : (arr[m - 1] + arr[m]) / 2;
    };
    const mid = Math.floor(sorted.length / 2);
    const lower = sorted.length % 2 ? sorted.slice(0, mid) : sorted.slice(0, mid);
    const upper = sorted.length % 2 ? sorted.slice(mid + 1) : sorted.slice(mid);
    return [
        { label: 'Median', value: medianOf(sorted) },
        { label: 'First quartile', value: medianOf(lower) },
        { label: 'Third quartile', value: medianOf(upper) },
        { label: 'Range', value: sorted[sorted.length - 1] - sorted[0] },
        { label: 'Count', value: sorted.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['numbers' => '1, 2, 3, 4, 5'], 'expect' => ['Median' => 3, 'First quartile' => 1.5, 'Third quartile' => 4.5, 'Range' => 4, 'Count' => 5]],
            ['input' => ['numbers' => '1 2 3 4'], 'expect' => ['Median' => 2.5, 'First quartile' => 1.5, 'Third quartile' => 3.5, 'Range' => 3]],
            ['input' => ['numbers' => '7'], 'expect' => ['Median' => 7, 'First quartile' => 7, 'Third quartile' => 7, 'Range' => 0]],
        ],
    ],
    [
        'slug' => 'mode-calculator',
        'name' => 'Mode Calculator',
        'category' => 'mathematics',
        'description' => 'Find the most frequent value or values in a list of numbers.',
        'icon' => 'list',
        'keywords' => ['mode', 'frequency', 'statistics', 'most common'],
        'inputs' => [
            ['name' => 'numbers', 'label' => 'Numbers', 'type' => 'textarea', 'default' => '1, 2, 2, 3, 3, 3', 'required' => true],
        ],
        'compute' => <<<'JS'
    const nums = (String(v.numbers).match(/-?\d+(?:\.\d+)?(?:e[+-]?\d+)?/gi) || []).map(Number).filter(Number.isFinite);
    if (nums.length === 0) throw new Error('Enter at least one number.');
    const counts = new Map();
    for (const n of nums) counts.set(n, (counts.get(n) || 0) + 1);
    const max = Math.max(...counts.values());
    const modes = [...counts.entries()].filter(([, c]) => c === max).map(([n]) => n).sort((a, b) => a - b);
    return [
        { label: 'Mode', value: max === 1 ? 'No mode' : modes.join(', ') },
        { label: 'Frequency', value: max, decimals: 0 },
        { label: 'Unique values', value: counts.size, decimals: 0 },
        { label: 'Count', value: nums.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['numbers' => '1, 2, 2, 3, 3, 3'], 'expect' => ['Mode' => '3', 'Frequency' => 3, 'Unique values' => 3, 'Count' => 6]],
            ['input' => ['numbers' => '1, 1, 2, 2, 3'], 'expect' => ['Mode' => '1, 2', 'Frequency' => 2, 'Unique values' => 3]],
            ['input' => ['numbers' => '4, 5, 6'], 'expect' => ['Mode' => 'No mode', 'Frequency' => 1]],
        ],
    ],
    [
        'slug' => 'percentile-calculator',
        'name' => 'Percentile Calculator',
        'category' => 'mathematics',
        'description' => 'Find the value at any percentile using the nearest-rank method.',
        'icon' => 'trending-up',
        'keywords' => ['percentile', 'quantile', 'statistics', 'rank'],
        'inputs' => [
            ['name' => 'numbers', 'label' => 'Numbers', 'type' => 'textarea', 'default' => '1, 2, 3, 4, 5', 'required' => true],
            ['name' => 'percentile', 'label' => 'Percentile', 'type' => 'number', 'default' => 90, 'min' => 0, 'max' => 100, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const nums = (String(v.numbers).match(/-?\d+(?:\.\d+)?(?:e[+-]?\d+)?/gi) || []).map(Number).filter(Number.isFinite);
    if (nums.length === 0) throw new Error('Enter at least one number.');
    const p = h.clamp(h.n(v.percentile), 0, 100);
    const sorted = [...nums].sort((a, b) => a - b);
    const rank = Math.max(1, Math.ceil((p / 100) * sorted.length));
    return [
        { label: 'Percentile value', value: sorted[rank - 1] },
        { label: 'Rank', value: rank, decimals: 0 },
        { label: 'Percentile', value: p, unit: '%' },
        { label: 'Count', value: sorted.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['numbers' => '1, 2, 3, 4, 5', 'percentile' => 50], 'expect' => ['Percentile value' => 3, 'Rank' => 3, 'Percentile' => 50]],
            ['input' => ['numbers' => '1, 2, 3, 4, 5', 'percentile' => 90], 'expect' => ['Percentile value' => 5, 'Rank' => 5]],
            ['input' => ['numbers' => '1, 2, 3, 4, 5', 'percentile' => 25], 'expect' => ['Percentile value' => 2, 'Rank' => 2]],
        ],
    ],
    [
        'slug' => 'z-score-calculator',
        'name' => 'Z-Score Calculator',
        'category' => 'mathematics',
        'description' => 'Measure how many standard deviations a value is from the mean.',
        'icon' => 'sigma',
        'keywords' => ['z-score', 'standard score', 'statistics', 'normal distribution'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 85, 'step' => 'any', 'required' => true],
            ['name' => 'mean', 'label' => 'Mean', 'type' => 'number', 'default' => 70, 'step' => 'any', 'required' => true],
            ['name' => 'sd', 'label' => 'Standard deviation', 'type' => 'number', 'default' => 10, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value), mean = h.n(v.mean), sd = h.n(v.sd);
    if (sd <= 0) throw new Error('Standard deviation must be greater than zero.');
    const z = (value - mean) / sd;
    return [
        { label: 'Z-score', value: z },
        { label: 'Distance from mean', value: Math.abs(value - mean) },
        { label: 'Position', value: z === 0 ? 'At the mean' : z > 0 ? 'Above the mean' : 'Below the mean' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 85, 'mean' => 70, 'sd' => 10], 'expect' => ['Z-score' => 1.5, 'Distance from mean' => 15, 'Position' => 'Above the mean']],
            ['input' => ['value' => 50, 'mean' => 70, 'sd' => 10], 'expect' => ['Z-score' => -2, 'Distance from mean' => 20, 'Position' => 'Below the mean']],
            ['input' => ['value' => 70, 'mean' => 70, 'sd' => 5], 'expect' => ['Z-score' => 0, 'Position' => 'At the mean']],
        ],
    ],
    [
        'slug' => 'slope-calculator',
        'name' => 'Slope Calculator',
        'category' => 'mathematics',
        'description' => 'Find the slope, y-intercept, angle and distance between two points.',
        'icon' => 'trending-up',
        'keywords' => ['slope', 'gradient', 'line', 'intercept', 'coordinates'],
        'inputs' => [
            ['name' => 'x1', 'label' => 'x₁', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'y1', 'label' => 'y₁', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
            ['name' => 'x2', 'label' => 'x₂', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
            ['name' => 'y2', 'label' => 'y₂', 'type' => 'number', 'default' => 6, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const x1 = h.n(v.x1), y1 = h.n(v.y1), x2 = h.n(v.x2), y2 = h.n(v.y2);
    const dx = x2 - x1, dy = y2 - y1;
    const distance = Math.hypot(dx, dy);
    if (dx === 0) {
        return [
            { label: 'Slope', value: 'Undefined' },
            { label: 'Y-intercept', value: '—' },
            { label: 'Angle', value: 90, unit: '°' },
            { label: 'Distance', value: distance },
        ];
    }
    const slope = dy / dx;
    return [
        { label: 'Slope', value: slope },
        { label: 'Y-intercept', value: y1 - slope * x1 },
        { label: 'Angle', value: Math.atan(slope) * 180 / Math.PI, unit: '°' },
        { label: 'Distance', value: distance },
    ];
JS,
        'tests' => [
            ['input' => ['x1' => 1, 'y1' => 2, 'x2' => 3, 'y2' => 6], 'expect' => ['Slope' => 2, 'Y-intercept' => 0, 'Angle' => 63.43494882292201, 'Distance' => 4.47213595499958]],
            ['input' => ['x1' => 1, 'y1' => 2, 'x2' => 1, 'y2' => 5], 'expect' => ['Slope' => 'Undefined', 'Angle' => 90, 'Distance' => 3]],
            ['input' => ['x1' => 1, 'y1' => 2, 'x2' => 3, 'y2' => 2], 'expect' => ['Slope' => 0, 'Y-intercept' => 2, 'Angle' => 0, 'Distance' => 2]],
        ],
    ],
    [
        'slug' => 'midpoint-calculator',
        'name' => 'Midpoint Calculator',
        'category' => 'mathematics',
        'description' => 'Find the midpoint and distance between two coordinate pairs.',
        'icon' => 'target',
        'keywords' => ['midpoint', 'coordinates', 'distance', 'geometry'],
        'inputs' => [
            ['name' => 'x1', 'label' => 'x₁', 'type' => 'number', 'default' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'y1', 'label' => 'y₁', 'type' => 'number', 'default' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'x2', 'label' => 'x₂', 'type' => 'number', 'default' => 4, 'step' => 'any', 'required' => true],
            ['name' => 'y2', 'label' => 'y₂', 'type' => 'number', 'default' => 6, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const x1 = h.n(v.x1), y1 = h.n(v.y1), x2 = h.n(v.x2), y2 = h.n(v.y2);
    return [
        { label: 'Midpoint x', value: (x1 + x2) / 2 },
        { label: 'Midpoint y', value: (y1 + y2) / 2 },
        { label: 'Distance', value: Math.hypot(x2 - x1, y2 - y1) },
    ];
JS,
        'tests' => [
            ['input' => ['x1' => 0, 'y1' => 0, 'x2' => 4, 'y2' => 6], 'expect' => ['Midpoint x' => 2, 'Midpoint y' => 3, 'Distance' => 7.211102550927978]],
            ['input' => ['x1' => 1, 'y1' => 1, 'x2' => 1, 'y2' => 5], 'expect' => ['Midpoint x' => 1, 'Midpoint y' => 3, 'Distance' => 4]],
        ],
    ],
    [
        'slug' => 'pythagorean-theorem-calculator',
        'name' => 'Pythagorean Theorem Calculator',
        'category' => 'mathematics',
        'description' => 'Find a missing side of a right triangle and its area and perimeter.',
        'icon' => 'triangle',
        'keywords' => ['pythagorean', 'right triangle', 'hypotenuse', 'geometry'],
        'inputs' => [
            ['name' => 'mode', 'label' => 'Find', 'type' => 'select', 'default' => 'hypotenuse', 'options' => [
                ['value' => 'hypotenuse', 'label' => 'Hypotenuse (c)'],
                ['value' => 'leg', 'label' => 'A leg (a or b)'],
            ]],
            ['name' => 'a', 'label' => 'Side a / hypotenuse', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
            ['name' => 'b', 'label' => 'Side b', 'type' => 'number', 'default' => 4, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const a = h.n(v.a), b = h.n(v.b);
    if (a <= 0 || b <= 0) throw new Error('Sides must be greater than zero.');
    let missing;
    let legA = a, legB = b;
    if (v.mode === 'leg') {
        if (a <= b) throw new Error('The hypotenuse must be longer than the known leg.');
        missing = Math.sqrt(a * a - b * b);
        legA = missing;
        legB = b;
    } else {
        missing = Math.hypot(a, b);
        legA = a;
        legB = b;
    }
    return [
        { label: 'Missing side', value: missing },
        { label: 'Area', value: (legA * legB) / 2 },
        { label: 'Perimeter', value: legA + legB + Math.hypot(legA, legB) },
    ];
JS,
        'tests' => [
            ['input' => ['mode' => 'hypotenuse', 'a' => 3, 'b' => 4], 'expect' => ['Missing side' => 5, 'Area' => 6, 'Perimeter' => 12]],
            ['input' => ['mode' => 'leg', 'a' => 5, 'b' => 4], 'expect' => ['Missing side' => 3, 'Area' => 6, 'Perimeter' => 12]],
            ['input' => ['mode' => 'hypotenuse', 'a' => 5, 'b' => 12], 'expect' => ['Missing side' => 13, 'Area' => 30, 'Perimeter' => 30]],
        ],
    ],
    [
        'slug' => 'rectangle-calculator',
        'name' => 'Rectangle Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate the area, perimeter and diagonal of a rectangle.',
        'icon' => 'function-square',
        'keywords' => ['rectangle', 'area', 'perimeter', 'diagonal'],
        'inputs' => [
            ['name' => 'length', 'label' => 'Length', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
            ['name' => 'width', 'label' => 'Width', 'type' => 'number', 'default' => 4, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const length = h.n(v.length), width = h.n(v.width);
    if (length <= 0 || width <= 0) throw new Error('Length and width must be greater than zero.');
    return [
        { label: 'Area', value: length * width },
        { label: 'Perimeter', value: 2 * (length + width) },
        { label: 'Diagonal', value: Math.hypot(length, width) },
    ];
JS,
        'tests' => [
            ['input' => ['length' => 3, 'width' => 4], 'expect' => ['Area' => 12, 'Perimeter' => 14, 'Diagonal' => 5]],
            ['input' => ['length' => 5, 'width' => 5], 'expect' => ['Area' => 25, 'Perimeter' => 20, 'Diagonal' => 7.0710678118654755]],
        ],
    ],
    [
        'slug' => 'trapezoid-area-calculator',
        'name' => 'Trapezoid Area Calculator',
        'category' => 'mathematics',
        'description' => 'Find the area and average width of a trapezoid.',
        'icon' => 'triangle',
        'keywords' => ['trapezoid', 'trapezium', 'area', 'geometry'],
        'inputs' => [
            ['name' => 'a', 'label' => 'Parallel side a', 'type' => 'number', 'default' => 4, 'step' => 'any', 'required' => true],
            ['name' => 'b', 'label' => 'Parallel side b', 'type' => 'number', 'default' => 6, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Height', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const a = h.n(v.a), b = h.n(v.b), height = h.n(v.height);
    if (a <= 0 || b <= 0 || height <= 0) throw new Error('All lengths must be greater than zero.');
    return [
        { label: 'Area', value: ((a + b) / 2) * height },
        { label: 'Average width', value: (a + b) / 2 },
    ];
JS,
        'tests' => [
            ['input' => ['a' => 4, 'b' => 6, 'height' => 5], 'expect' => ['Area' => 25, 'Average width' => 5]],
            ['input' => ['a' => 3, 'b' => 7, 'height' => 4], 'expect' => ['Area' => 20, 'Average width' => 5]],
        ],
    ],
    [
        'slug' => 'sphere-calculator',
        'name' => 'Sphere Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate a sphere’s volume, surface area, diameter and circumference.',
        'icon' => 'circle',
        'keywords' => ['sphere', 'volume', 'surface area', 'radius', 'geometry'],
        'inputs' => [
            ['name' => 'radius', 'label' => 'Radius', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const r = h.n(v.radius);
    if (r <= 0) throw new Error('Radius must be greater than zero.');
    return [
        { label: 'Volume', value: (4 / 3) * Math.PI * r ** 3 },
        { label: 'Surface area', value: 4 * Math.PI * r ** 2 },
        { label: 'Diameter', value: 2 * r },
        { label: 'Circumference', value: 2 * Math.PI * r },
    ];
JS,
        'tests' => [
            ['input' => ['radius' => 3], 'expect' => ['Volume' => 113.09733552923255, 'Surface area' => 113.09733552923255, 'Diameter' => 6, 'Circumference' => 18.84955592153876]],
            ['input' => ['radius' => 1], 'expect' => ['Volume' => 4.1887902047863905, 'Surface area' => 12.566370614359172, 'Diameter' => 2]],
        ],
    ],
    [
        'slug' => 'cylinder-calculator',
        'name' => 'Cylinder Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate a cylinder’s volume, surface area and base area.',
        'icon' => 'circle',
        'keywords' => ['cylinder', 'volume', 'surface area', 'radius', 'geometry'],
        'inputs' => [
            ['name' => 'radius', 'label' => 'Radius', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Height', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const r = h.n(v.radius), height = h.n(v.height);
    if (r <= 0 || height <= 0) throw new Error('Radius and height must be greater than zero.');
    return [
        { label: 'Volume', value: Math.PI * r ** 2 * height },
        { label: 'Surface area', value: 2 * Math.PI * r * (r + height) },
        { label: 'Lateral area', value: 2 * Math.PI * r * height },
        { label: 'Base area', value: Math.PI * r ** 2 },
    ];
JS,
        'tests' => [
            ['input' => ['radius' => 2, 'height' => 5], 'expect' => ['Volume' => 62.83185307179586, 'Surface area' => 87.96459430051421, 'Lateral area' => 62.83185307179586, 'Base area' => 12.566370614359172]],
            ['input' => ['radius' => 1, 'height' => 1], 'expect' => ['Volume' => 3.141592653589793, 'Surface area' => 12.566370614359172, 'Base area' => 3.141592653589793]],
        ],
    ],
    [
        'slug' => 'cone-calculator',
        'name' => 'Cone Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate a cone’s volume, slant height and surface area.',
        'icon' => 'triangle',
        'keywords' => ['cone', 'volume', 'surface area', 'slant height', 'geometry'],
        'inputs' => [
            ['name' => 'radius', 'label' => 'Radius', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Height', 'type' => 'number', 'default' => 4, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const r = h.n(v.radius), height = h.n(v.height);
    if (r <= 0 || height <= 0) throw new Error('Radius and height must be greater than zero.');
    const slant = Math.hypot(r, height);
    return [
        { label: 'Volume', value: (Math.PI * r ** 2 * height) / 3 },
        { label: 'Slant height', value: slant },
        { label: 'Surface area', value: Math.PI * r * (r + slant) },
        { label: 'Lateral area', value: Math.PI * r * slant },
    ];
JS,
        'tests' => [
            ['input' => ['radius' => 3, 'height' => 4], 'expect' => ['Volume' => 37.69911184307752, 'Slant height' => 5, 'Surface area' => 75.39822368615503, 'Lateral area' => 47.12388980384689]],
            ['input' => ['radius' => 1, 'height' => 1], 'expect' => ['Volume' => 1.0471975511965976, 'Slant height' => 1.4142135623730951]],
        ],
    ],
    [
        'slug' => 'logarithm-calculator',
        'name' => 'Logarithm Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate a logarithm in any base plus the natural and base-10 logs.',
        'icon' => 'function-square',
        'keywords' => ['logarithm', 'log', 'natural log', 'base'],
        'inputs' => [
            ['name' => 'x', 'label' => 'Number', 'type' => 'number', 'default' => 8, 'step' => 'any', 'required' => true],
            ['name' => 'base', 'label' => 'Base', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const x = h.n(v.x), base = h.n(v.base);
    if (x <= 0) throw new Error('The number must be greater than zero.');
    if (base <= 0 || base === 1) throw new Error('The base must be greater than zero and not equal to one.');
    return [
        { label: 'Logarithm', value: Math.log(x) / Math.log(base) },
        { label: 'Natural log', value: Math.log(x) },
        { label: 'Log base 10', value: Math.log10(x) },
    ];
JS,
        'tests' => [
            ['input' => ['x' => 8, 'base' => 2], 'expect' => ['Logarithm' => 3, 'Natural log' => 2.0794415416798357, 'Log base 10' => 0.9030899869919435]],
            ['input' => ['x' => 100, 'base' => 10], 'expect' => ['Logarithm' => 2, 'Natural log' => 4.605170185988092, 'Log base 10' => 2]],
        ],
    ],
];
