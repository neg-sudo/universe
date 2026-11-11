<?php
/**
 * UtilityWorld — Mathematics
 * 12 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'percentage-calculator',
        'name' => 'Percentage Calculator',
        'category' => 'mathematics',
        'description' => 'Find a percentage of a number, percentage change, or a reverse percentage.',
        'icon' => 'percent',
        'featured' => true,
        'keywords' => ['percentage', 'percent', 'increase', 'decrease', 'discount'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Calculation',
                'type' => 'select',
                'default' => 'of',
                'options' => [
                    ['value' => 'of', 'label' => 'What is X% of Y?'],
                    ['value' => 'change', 'label' => 'Percentage change from X to Y'],
                    ['value' => 'reverse', 'label' => 'Y is X% of what number?'],
                ],
            ],
            ['name' => 'x', 'label' => 'Value X', 'type' => 'number', 'default' => 20, 'step' => 'any', 'required' => true],
            ['name' => 'y', 'label' => 'Value Y', 'type' => 'number', 'default' => 150, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const x = h.n(v.x);
            const y = h.n(v.y);
            if (v.mode === 'of') {
                return { label: 'Result', value: (x / 100) * y, hint: `${x}% of ${y}` };
            }
            if (v.mode === 'reverse') {
                if (x === 0) throw new Error('The percentage cannot be zero.');
                return { label: 'Whole value', value: y / (x / 100), hint: `${y} is ${x}% of this number.` };
            }
            if (x === 0) throw new Error('The starting value cannot be zero.');
            const change = ((y - x) / Math.abs(x)) * 100;
            return {
                label: 'Percentage change',
                value: change,
                unit: '%',
                hint: change >= 0 ? 'Increase' : 'Decrease',
            };
        JS,
        'tests' => [
            ['input' => ['mode' => 'of', 'x' => 20, 'y' => 150], 'expect' => ['Result' => 30]],
            ['input' => ['mode' => 'change', 'x' => 50, 'y' => 75], 'expect' => ['Percentage change' => 50]],
            ['input' => ['mode' => 'reverse', 'x' => 20, 'y' => 30], 'expect' => ['Whole value' => 150]],
            ['input' => ['mode' => 'change', 'x' => 80, 'y' => 60], 'expect' => ['Percentage change' => -25]],
        ],
    ],

    [
        'slug' => 'average-calculator',
        'name' => 'Average Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate the mean, median, sum and range of a list of numbers.',
        'icon' => 'sigma',
        'featured' => true,
        'keywords' => ['average', 'mean', 'median', 'statistics'],
        'inputs' => [
            [
                'name' => 'numbers',
                'label' => 'Numbers',
                'type' => 'textarea',
                'default' => '12, 18, 24, 30, 36',
                'placeholder' => 'Separate numbers with commas, spaces or new lines',
                'required' => true,
                'hint' => 'Commas, spaces and line breaks are all accepted.',
            ],
        ],
        'compute' => <<<'JS'
            const matches = String(v.numbers).match(/-?\d+(?:\.\d+)?(?:e[+-]?\d+)?/gi) || [];
            const nums = matches.map(Number).filter(Number.isFinite);
            if (nums.length === 0) throw new Error('Enter at least one number.');
            const sum = nums.reduce((a, b) => a + b, 0);
            const mean = sum / nums.length;
            const sorted = [...nums].sort((a, b) => a - b);
            const mid = Math.floor(sorted.length / 2);
            const median = sorted.length % 2 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2;
            return [
                { label: 'Mean', value: mean },
                { label: 'Median', value: median },
                { label: 'Sum', value: sum },
                { label: 'Count', value: nums.length, decimals: 0 },
                { label: 'Smallest', value: sorted[0] },
                { label: 'Largest', value: sorted[sorted.length - 1] },
                { label: 'Range', value: sorted[sorted.length - 1] - sorted[0] },
            ];
        JS,
        'tests' => [
            ['input' => ['numbers' => '2, 4, 6'], 'expect' => ['Mean' => 4, 'Median' => 4, 'Sum' => 12, 'Count' => 3]],
            ['input' => ['numbers' => '1 3 5 7'], 'expect' => ['Mean' => 4, 'Median' => 4, 'Range' => 6]],
            ['input' => ['numbers' => "10\n20\n30\n40"], 'expect' => ['Median' => 25, 'Largest' => 40]],
        ],
    ],

    [
        'slug' => 'ratio-calculator',
        'name' => 'Ratio Calculator',
        'category' => 'mathematics',
        'description' => 'Simplify a ratio and see its decimal value and percentage shares.',
        'icon' => 'ratio',
        'keywords' => ['ratio', 'proportion', 'simplify', 'scale'],
        'inputs' => [
            ['name' => 'a', 'label' => 'First value', 'type' => 'number', 'default' => 1920, 'step' => 'any', 'required' => true],
            ['name' => 'b', 'label' => 'Second value', 'type' => 'number', 'default' => 1080, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const a = h.n(v.a);
            const b = h.n(v.b);
            if (a === 0 && b === 0) throw new Error('Both values cannot be zero.');
            const gcd = (x, y) => {
                x = Math.abs(x); y = Math.abs(y);
                while (y) { const t = y; y = x % y; x = t; }
                return x || 1;
            };
            const isInt = Number.isInteger(a) && Number.isInteger(b);
            const divisor = gcd(a, b);
            const sa = a / divisor;
            const sb = b / divisor;
            const simplified = isInt ? `${sa}:${sb}` : `${h.round(sa, 4)}:${h.round(sb, 4)}`;
            const total = a + b;
            return [
                { label: 'Simplified ratio', value: simplified },
                { label: 'Decimal value (A ÷ B)', value: b === 0 ? 'Undefined' : a / b },
                { label: 'A share', value: total === 0 ? 0 : (a / total) * 100, unit: '%' },
                { label: 'B share', value: total === 0 ? 0 : (b / total) * 100, unit: '%' },
            ];
        JS,
        'tests' => [
            ['input' => ['a' => 1920, 'b' => 1080], 'expect' => ['Simplified ratio' => '16:9', 'A share' => 64, 'B share' => 36]],
            ['input' => ['a' => 3, 'b' => 4], 'expect' => ['Simplified ratio' => '3:4', 'Decimal value (A ÷ B)' => 0.75]],
        ],
    ],

    [
        'slug' => 'gcd-lcm-calculator',
        'name' => 'GCD and LCM Calculator',
        'category' => 'mathematics',
        'description' => 'Find the greatest common divisor and least common multiple of two integers.',
        'icon' => 'divide',
        'keywords' => ['gcd', 'lcm', 'greatest common divisor', 'least common multiple'],
        'inputs' => [
            ['name' => 'a', 'label' => 'First integer', 'type' => 'number', 'default' => 48, 'step' => 1, 'required' => true],
            ['name' => 'b', 'label' => 'Second integer', 'type' => 'number', 'default' => 180, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const a = h.n(v.a);
            const b = h.n(v.b);
            if (!Number.isInteger(a) || !Number.isInteger(b)) throw new Error('Enter whole numbers only.');
            if (a === 0 || b === 0) throw new Error('Zero is not supported. Enter non-zero integers.');
            const gcd = (x, y) => {
                x = Math.abs(x); y = Math.abs(y);
                while (y) { const t = y; y = x % y; x = t; }
                return x;
            };
            const g = gcd(a, b);
            return [
                { label: 'Greatest common divisor', value: g, decimals: 0 },
                { label: 'Least common multiple', value: Math.abs(a * b) / g, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['a' => 48, 'b' => 180], 'expect' => ['Greatest common divisor' => 12, 'Least common multiple' => 720]],
            ['input' => ['a' => 17, 'b' => 5], 'expect' => ['Greatest common divisor' => 1, 'Least common multiple' => 85]],
        ],
    ],

    [
        'slug' => 'prime-number-checker',
        'name' => 'Prime Number Checker',
        'category' => 'mathematics',
        'description' => 'Check whether a number is prime and show its prime factorisation.',
        'icon' => 'hash',
        'keywords' => ['prime', 'factorization', 'factors', 'number theory'],
        'inputs' => [
            ['name' => 'n', 'label' => 'Whole number', 'type' => 'number', 'default' => 97, 'min' => 0, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const n = h.n(v.n);
            if (!Number.isInteger(n) || n < 0) throw new Error('Enter a whole number of 0 or more.');
            const isPrime = (x) => {
                if (x < 2) return false;
                if (x % 2 === 0) return x === 2;
                for (let i = 3; i * i <= x; i += 2) if (x % i === 0) return false;
                return true;
            };
            const factors = [];
            let rest = n;
            for (let p = 2; p * p <= rest; p++) {
                let count = 0;
                while (rest % p === 0) { rest /= p; count++; }
                if (count) factors.push(count === 1 ? `${p}` : `${p}^${count}`);
            }
            if (rest > 1) factors.push(`${rest}`);
            return [
                { label: 'Result', value: n < 2 ? 'Neither prime nor composite' : isPrime(n) ? 'Prime' : 'Not prime' },
                { label: 'Prime factorisation', value: n < 2 ? '—' : factors.join(' × ') },
                { label: 'Number of divisors', value: (() => {
                    if (n === 0) return 'Infinite';
                    let count = 1, rest2 = n;
                    for (let p = 2; p * p <= rest2; p++) {
                        let e = 0;
                        while (rest2 % p === 0) { rest2 /= p; e++; }
                        if (e) count *= e + 1;
                    }
                    if (rest2 > 1) count *= 2;
                    return count;
                })(), decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['n' => 97], 'expect' => ['Result' => 'Prime', 'Prime factorisation' => '97']],
            ['input' => ['n' => 100], 'expect' => ['Result' => 'Not prime', 'Prime factorisation' => '2^2 × 5^2', 'Number of divisors' => 9]],
            ['input' => ['n' => 1], 'expect' => ['Result' => 'Neither prime nor composite']],
            ['input' => ['n' => 360], 'expect' => ['Prime factorisation' => '2^3 × 3^2 × 5', 'Number of divisors' => 24]],
        ],
    ],

    [
        'slug' => 'factorial-calculator',
        'name' => 'Factorial Calculator',
        'category' => 'mathematics',
        'description' => 'Compute n! exactly for any whole number up to 170.',
        'icon' => 'function-square',
        'keywords' => ['factorial', 'permutation', 'combinatorics'],
        'inputs' => [
            ['name' => 'n', 'label' => 'n', 'type' => 'number', 'default' => 10, 'min' => 0, 'max' => 170, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const n = h.n(v.n);
            if (!Number.isInteger(n) || n < 0) throw new Error('Enter a whole number of 0 or more.');
            if (n > 170) throw new Error('The maximum supported value is 170.');
            let result = 1n;
            for (let i = 2n; i <= BigInt(n); i++) result *= i;
            const digits = result.toString().length;
            return [
                { label: 'n!', value: result.toString() },
                { label: 'Number of digits', value: digits, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['n' => 5], 'expect' => ['n!' => '120', 'Number of digits' => 3]],
            ['input' => ['n' => 0], 'expect' => ['n!' => '1']],
            ['input' => ['n' => 10], 'expect' => ['n!' => '3628800']],
            ['input' => ['n' => 170], 'expect' => ['Number of digits' => 307]],
        ],
    ],

    [
        'slug' => 'quadratic-equation-solver',
        'name' => 'Quadratic Equation Solver',
        'category' => 'mathematics',
        'description' => 'Solve ax² + bx + c = 0 and see the discriminant and roots.',
        'icon' => 'function-square',
        'featured' => true,
        'keywords' => ['quadratic', 'equation', 'roots', 'discriminant', 'algebra'],
        'inputs' => [
            ['name' => 'a', 'label' => 'a', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'b', 'label' => 'b', 'type' => 'number', 'default' => -5, 'step' => 'any', 'required' => true],
            ['name' => 'c', 'label' => 'c', 'type' => 'number', 'default' => 6, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const a = h.n(v.a), b = h.n(v.b), c = h.n(v.c);
            const fmt = (x) => String(h.round(x, 6));
            if (a === 0) {
                if (b === 0) throw new Error('This is not a solvable equation: a and b are both zero.');
                return [
                    { label: 'Equation type', value: 'Linear (a = 0)' },
                    { label: 'Root', value: fmt(-c / b) },
                ];
            }
            const d = b * b - 4 * a * c;
            if (d < 0) {
                const re = -b / (2 * a);
                const im = Math.sqrt(-d) / (2 * a);
                return [
                    { label: 'Discriminant', value: d },
                    { label: 'Roots', value: `${fmt(re)} + ${fmt(Math.abs(im))}i and ${fmt(re)} − ${fmt(Math.abs(im))}i` },
                    { label: 'Nature of roots', value: 'Two complex roots' },
                ];
            }
            if (d === 0) {
                return [
                    { label: 'Discriminant', value: 0, decimals: 0 },
                    { label: 'Root', value: fmt(-b / (2 * a)) },
                    { label: 'Nature of roots', value: 'One repeated real root' },
                ];
            }
            const r1 = (-b + Math.sqrt(d)) / (2 * a);
            const r2 = (-b - Math.sqrt(d)) / (2 * a);
            return [
                { label: 'Discriminant', value: d },
                { label: 'Roots', value: `${fmt(Math.min(r1, r2))} and ${fmt(Math.max(r1, r2))}` },
                { label: 'Nature of roots', value: 'Two distinct real roots' },
            ];
        JS,
        'tests' => [
            ['input' => ['a' => 1, 'b' => -5, 'c' => 6], 'expect' => ['Discriminant' => 1, 'Roots' => '2 and 3', 'Nature of roots' => 'Two distinct real roots']],
            ['input' => ['a' => 1, 'b' => -4, 'c' => 4], 'expect' => ['Discriminant' => 0, 'Root' => '2']],
            ['input' => ['a' => 1, 'b' => 0, 'c' => 1], 'expect' => ['Nature of roots' => 'Two complex roots', 'Roots' => '0 + 1i and 0 − 1i']],
        ],
    ],

    [
        'slug' => 'triangle-area-calculator',
        'name' => 'Triangle Area Calculator',
        'category' => 'mathematics',
        'description' => 'Find a triangle’s area from base and height or from all three sides.',
        'icon' => 'triangle',
        'keywords' => ['triangle', 'area', 'heron', 'geometry'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Method',
                'type' => 'select',
                'default' => 'base-height',
                'options' => [
                    ['value' => 'base-height', 'label' => 'Base and height'],
                    ['value' => 'sides', 'label' => 'Three sides (Heron’s formula)'],
                ],
            ],
            ['name' => 'base', 'label' => 'Base', 'type' => 'number', 'default' => 10, 'step' => 'any', 'showIf' => ['mode' => 'base-height']],
            ['name' => 'height', 'label' => 'Height', 'type' => 'number', 'default' => 6, 'step' => 'any', 'showIf' => ['mode' => 'base-height']],
            ['name' => 'a', 'label' => 'Side a', 'type' => 'number', 'default' => 3, 'step' => 'any', 'showIf' => ['mode' => 'sides']],
            ['name' => 'b', 'label' => 'Side b', 'type' => 'number', 'default' => 4, 'step' => 'any', 'showIf' => ['mode' => 'sides']],
            ['name' => 'c', 'label' => 'Side c', 'type' => 'number', 'default' => 5, 'step' => 'any', 'showIf' => ['mode' => 'sides']],
        ],
        'compute' => <<<'JS'
            if (v.mode === 'base-height') {
                const base = h.n(v.base), height = h.n(v.height);
                if (base <= 0 || height <= 0) throw new Error('Base and height must be greater than zero.');
                return { label: 'Area', value: 0.5 * base * height };
            }
            const a = h.n(v.a), b = h.n(v.b), c = h.n(v.c);
            if (a <= 0 || b <= 0 || c <= 0) throw new Error('All sides must be greater than zero.');
            if (a + b <= c || a + c <= b || b + c <= a) throw new Error('These side lengths cannot form a triangle.');
            const s = (a + b + c) / 2;
            return [
                { label: 'Area', value: Math.sqrt(s * (s - a) * (s - b) * (s - c)) },
                { label: 'Perimeter', value: a + b + c },
                { label: 'Semi-perimeter', value: s },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'base-height', 'base' => 10, 'height' => 6], 'expect' => ['Area' => 30]],
            ['input' => ['mode' => 'sides', 'a' => 3, 'b' => 4, 'c' => 5], 'expect' => ['Area' => 6, 'Perimeter' => 12, 'Semi-perimeter' => 6]],
        ],
    ],

    [
        'slug' => 'circle-calculator',
        'name' => 'Circle Calculator',
        'category' => 'mathematics',
        'description' => 'Calculate a circle’s diameter, circumference and area from its radius.',
        'icon' => 'circle',
        'keywords' => ['circle', 'radius', 'diameter', 'circumference', 'area'],
        'inputs' => [
            ['name' => 'radius', 'label' => 'Radius', 'type' => 'number', 'default' => 5, 'step' => 'any', 'min' => 0, 'required' => true],
        ],
        'compute' => <<<'JS'
            const r = h.n(v.radius);
            if (r < 0) throw new Error('The radius cannot be negative.');
            return [
                { label: 'Diameter', value: 2 * r },
                { label: 'Circumference', value: 2 * Math.PI * r },
                { label: 'Area', value: Math.PI * r * r },
            ];
        JS,
        'tests' => [
            ['input' => ['radius' => 5], 'expect' => ['Diameter' => 10, 'Circumference' => 31.4159265359, 'Area' => 78.5398163397]],
            ['input' => ['radius' => 0], 'expect' => ['Diameter' => 0, 'Area' => 0]],
        ],
    ],

    [
        'slug' => 'scientific-notation-converter',
        'name' => 'Scientific Notation Converter',
        'category' => 'mathematics',
        'description' => 'Convert numbers to scientific notation and back again.',
        'icon' => 'flask-conical',
        'keywords' => ['scientific notation', 'standard form', 'exponent'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Direction',
                'type' => 'select',
                'default' => 'to-notation',
                'options' => [
                    ['value' => 'to-notation', 'label' => 'Number → scientific notation'],
                    ['value' => 'to-decimal', 'label' => 'Scientific notation → number'],
                ],
            ],
            ['name' => 'value', 'label' => 'Value', 'type' => 'text', 'default' => '0.00042', 'required' => true, 'hint' => 'Examples: 12345, 4.2 × 10^-4, 3e8'],
        ],
        'compute' => <<<'JS'
            const trim = (x) => String(Number(x.toPrecision(12)));
            if (v.mode === 'to-notation') {
                const x = h.n(v.value);
                if (x === 0) return [
                    { label: 'Scientific notation', value: '0 × 10^0' },
                    { label: 'Coefficient', value: 0 },
                    { label: 'Exponent', value: 0, decimals: 0 },
                ];
                const exp = Math.floor(Math.log10(Math.abs(x)));
                const coef = Number((x / 10 ** exp).toPrecision(12));
                return [
                    { label: 'Scientific notation', value: `${trim(coef)} × 10^${exp}` },
                    { label: 'Coefficient', value: coef },
                    { label: 'Exponent', value: exp, decimals: 0 },
                ];
            }
            const raw = String(v.value).trim().replace(/×/g, 'x').replace(/\s+/g, '');
            let decimal;
            if (/^[+-]?\d*\.?\d+e[+-]?\d+$/i.test(raw)) {
                decimal = Number(raw);
            } else {
                const match = raw.match(/^([+-]?\d*\.?\d+)(?:x10\^?([+-]?\d+))?$/i);
                if (!match) throw new Error('Enter a number such as 4.2 × 10^-4 or 3e8.');
                decimal = Number(match[1]) * 10 ** Number(match[2] || 0);
            }
            if (!Number.isFinite(decimal)) throw new Error('That value could not be parsed.');
            return [
                { label: 'Decimal', value: decimal },
                { label: 'Rounded (6 decimals)', value: h.round(decimal, 6), decimals: 6 },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'to-notation', 'value' => '0.00042'], 'expect' => ['Scientific notation' => '4.2 × 10^-4', 'Exponent' => -4]],
            ['input' => ['mode' => 'to-notation', 'value' => '12345'], 'expect' => ['Scientific notation' => '1.2345 × 10^4', 'Coefficient' => 1.2345]],
            ['input' => ['mode' => 'to-decimal', 'value' => '4.2 × 10^-4'], 'expect' => ['Decimal' => 0.00042]],
            ['input' => ['mode' => 'to-decimal', 'value' => '3e8'], 'expect' => ['Decimal' => 300000000]],
        ],
    ],

    [
        'slug' => 'random-number-generator',
        'name' => 'Random Number Generator',
        'category' => 'mathematics',
        'description' => 'Generate random whole numbers within a range you choose.',
        'icon' => 'dices',
        'keywords' => ['random', 'number', 'generator', 'dice'],
        'inputs' => [
            ['name' => 'min', 'label' => 'Minimum', 'type' => 'number', 'default' => 1, 'step' => 1, 'required' => true],
            ['name' => 'max', 'label' => 'Maximum', 'type' => 'number', 'default' => 100, 'step' => 1, 'required' => true],
            ['name' => 'count', 'label' => 'How many numbers?', 'type' => 'number', 'default' => 5, 'min' => 1, 'max' => 100, 'step' => 1, 'required' => true],
            ['name' => 'unique', 'label' => 'Avoid duplicate numbers', 'type' => 'checkbox', 'default' => false],
        ],
        'compute' => <<<'JS'
            const min = Math.ceil(h.n(v.min));
            const max = Math.floor(h.n(v.max));
            const count = Math.floor(h.n(v.count));
            if (max < min) throw new Error('The maximum must be greater than or equal to the minimum.');
            if (count < 1 || count > 100) throw new Error('Choose between 1 and 100 numbers.');
            const range = max - min + 1;
            const unique = v.unique === true || v.unique === '1' || v.unique === 'on';
            if (unique && count > range) throw new Error('The range is too small for that many unique numbers.');
            const picked = [];
            const used = new Set();
            while (picked.length < count) {
                const n = min + Math.floor(Math.random() * range);
                if (unique && used.has(n)) continue;
                used.add(n);
                picked.push(n);
            }
            return [
                { label: 'Numbers', value: picked.join(', ') },
                { label: 'Sum', value: picked.reduce((a, b) => a + b, 0), decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['min' => 7, 'max' => 7, 'count' => 3, 'unique' => false], 'expect' => ['Numbers' => '7, 7, 7', 'Sum' => 21]],
            ['input' => ['min' => 5, 'max' => 5, 'count' => 1, 'unique' => true], 'expect' => ['Numbers' => '5']],
        ],
    ],

    [
        'slug' => 'rounding-calculator',
        'name' => 'Rounding Calculator',
        'category' => 'mathematics',
        'description' => 'Round a number to a chosen number of decimal places, up or down.',
        'icon' => 'arrow-up-down',
        'keywords' => ['round', 'ceiling', 'floor', 'decimal places'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Number', 'type' => 'number', 'default' => 3.14159, 'step' => 'any', 'required' => true],
            ['name' => 'decimals', 'label' => 'Decimal places', 'type' => 'number', 'default' => 2, 'min' => 0, 'max' => 10, 'step' => 1, 'required' => true],
            [
                'name' => 'mode',
                'label' => 'Method',
                'type' => 'select',
                'default' => 'nearest',
                'options' => [
                    ['value' => 'nearest', 'label' => 'Nearest'],
                    ['value' => 'up', 'label' => 'Always up (ceiling)'],
                    ['value' => 'down', 'label' => 'Always down (floor)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const value = h.n(v.value);
            const decimals = Math.floor(h.n(v.decimals));
            if (decimals < 0 || decimals > 10) throw new Error('Choose between 0 and 10 decimal places.');
            const factor = 10 ** decimals;
            const scaled = value * factor;
            const rounded = v.mode === 'up' ? Math.ceil(scaled) : v.mode === 'down' ? Math.floor(scaled) : Math.round(scaled);
            return { label: 'Rounded value', value: rounded / factor, decimals };
        JS,
        'tests' => [
            ['input' => ['value' => 3.14159, 'decimals' => 2, 'mode' => 'nearest'], 'expect' => ['Rounded value' => 3.14]],
            ['input' => ['value' => 3.14159, 'decimals' => 2, 'mode' => 'up'], 'expect' => ['Rounded value' => 3.15]],
            ['input' => ['value' => 3.14999, 'decimals' => 0, 'mode' => 'down'], 'expect' => ['Rounded value' => 3]],
        ],
    ],
];
