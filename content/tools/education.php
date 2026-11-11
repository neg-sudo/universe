<?php
/**
 * UniVerse — Education
 * 8 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'gpa-calculator',
        'name' => 'GPA Calculator',
        'category' => 'education',
        'description' => 'Turn course grades and credits into a weighted GPA on the standard 4.0 scale.',
        'icon' => 'graduation-cap',
        'featured' => true,
        'keywords' => ['gpa', 'grade point average', 'grades', 'credits', 'college'],
        'inputs' => [
            [
                'name' => 'courses',
                'label' => 'Courses',
                'type' => 'textarea',
                'default' => "Calculus, A, 4\nBiology, B+, 3\nHistory, A-, 3",
                'placeholder' => "Course, grade, credits\nCalculus, A, 4",
                'required' => true,
                'hint' => 'One course per line. Grades may be A-F with an optional + or -.',
            ],
        ],
        'compute' => <<<'JS'
            const lines = h.splitLines(v.courses);
            if (lines.length === 0) throw new Error('Enter at least one course line.');
            const scale = {
                'A+': 4.0, 'A': 4.0, 'A-': 3.7,
                'B+': 3.3, 'B': 3.0, 'B-': 2.7,
                'C+': 2.3, 'C': 2.0, 'C-': 1.7,
                'D+': 1.3, 'D': 1.0, 'D-': 0.7,
                'F': 0.0,
            };
            let totalCredits = 0;
            let totalPoints = 0;
            let counted = 0;
            for (const line of lines) {
                const parts = line.split(',').map((part) => part.trim());
                if (parts.length < 3) {
                    throw new Error(`Could not read "${line}". Use: Course, grade, credits.`);
                }
                const rawGrade = parts[parts.length - 2];
                const grade = rawGrade.toUpperCase().replace(/[\u2212\u2013]/g, '-').replace(/\s+/g, '');
                if (!(grade in scale)) {
                    throw new Error(`Unknown grade "${rawGrade}". Use A-F with an optional + or -.`);
                }
                const credits = h.n(parts[parts.length - 1]);
                if (!Number.isFinite(credits) || credits <= 0) {
                    throw new Error('Credits must be greater than zero.');
                }
                totalCredits += credits;
                totalPoints += scale[grade] * credits;
                counted++;
            }
            return [
                { label: 'GPA (4.0 scale)', value: totalPoints / totalCredits, decimals: 2 },
                { label: 'Total credits', value: totalCredits, decimals: 2 },
                { label: 'Total grade points', value: totalPoints, decimals: 2 },
                { label: 'Courses counted', value: counted, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['courses' => "Math, A, 4\nEnglish, B+, 3\nPhysics, A-, 3"],
                'expect' => ['GPA (4.0 scale)' => 3.7, 'Total credits' => 10, 'Total grade points' => 37, 'Courses counted' => 3],
            ],
            [
                'input' => ['courses' => "history, b, 3\nart, c+, 3"],
                'expect' => ['GPA (4.0 scale)' => 2.65, 'Total credits' => 6, 'Total grade points' => 15.9],
            ],
            [
                'input' => ['courses' => "Course one, A+, 3\nCourse two, F, 3"],
                'expect' => ['GPA (4.0 scale)' => 2, 'Total grade points' => 12],
            ],
        ],
    ],

    [
        'slug' => 'weighted-grade-calculator',
        'name' => 'Weighted Grade Calculator',
        'category' => 'education',
        'description' => 'Combine assignment scores and their weights into a single weighted grade.',
        'icon' => 'scale',
        'featured' => false,
        'keywords' => ['weighted grade', 'grades', 'assignment', 'weight', 'class'],
        'inputs' => [
            [
                'name' => 'items',
                'label' => 'Assignments',
                'type' => 'textarea',
                'default' => "Homework, 80, 40\nFinal exam, 90, 60",
                'placeholder' => "Assignment, score %, weight %\nHomework, 80, 40",
                'required' => true,
                'hint' => 'One assignment per line: name, score, weight.',
            ],
        ],
        'compute' => <<<'JS'
            const lines = h.splitLines(v.items);
            if (lines.length === 0) throw new Error('Enter at least one assignment line.');
            let weighted = 0;
            let totalWeight = 0;
            for (const line of lines) {
                const parts = line.split(',').map((part) => part.trim());
                if (parts.length < 3) {
                    throw new Error(`Could not read "${line}". Use: Assignment, score %, weight %.`);
                }
                const score = h.n(parts[parts.length - 2]);
                const weight = h.n(parts[parts.length - 1]);
                if (weight <= 0) throw new Error('Each weight must be greater than zero.');
                weighted += score * weight;
                totalWeight += weight;
            }
            if (totalWeight === 0) throw new Error('The total weight must be greater than zero.');
            const warning = Math.abs(totalWeight - 100) < 1e-9
                ? 'Weights add up to 100%.'
                : `Weights add up to ${h.round(totalWeight, 2)}%, not 100%.`;
            return [
                { label: 'Weighted grade', value: weighted / totalWeight, unit: '%', decimals: 2 },
                { label: 'Total weight', value: totalWeight, unit: '%', decimals: 2 },
                { label: 'Warning', value: warning },
            ];
        JS,
        'tests' => [
            [
                'input' => ['items' => "Homework, 80, 40\nFinal exam, 90, 60"],
                'expect' => ['Weighted grade' => 86, 'Total weight' => 100, 'Warning' => 'Weights add up to 100%.'],
            ],
            [
                'input' => ['items' => "Quiz, 70, 20\nProject, 90, 30"],
                'expect' => ['Weighted grade' => 82, 'Total weight' => 50, 'Warning' => 'Weights add up to 50%, not 100%.'],
            ],
            [
                'input' => ['items' => "Test one, 100, 25\nTest two, 80, 25\nExam, 60, 50"],
                'expect' => ['Weighted grade' => 75, 'Total weight' => 100],
            ],
        ],
    ],

    [
        'slug' => 'final-grade-calculator',
        'name' => 'Final Grade Calculator',
        'category' => 'education',
        'description' => 'Find the score you need on the final exam to reach your target course grade.',
        'icon' => 'target',
        'featured' => false,
        'keywords' => ['final grade', 'exam', 'target grade', 'required score'],
        'inputs' => [
            ['name' => 'current', 'label' => 'Current grade', 'type' => 'number', 'default' => 78, 'step' => 'any', 'required' => true, 'hint' => 'Percent so far'],
            ['name' => 'desired', 'label' => 'Desired final grade', 'type' => 'number', 'default' => 85, 'step' => 'any', 'required' => true],
            ['name' => 'weight', 'label' => 'Final exam weight', 'type' => 'number', 'default' => 30, 'min' => 1, 'max' => 100, 'step' => 'any', 'required' => true, 'hint' => 'Percent of the course grade'],
        ],
        'compute' => <<<'JS'
            const current = h.n(v.current);
            const desired = h.n(v.desired);
            const weight = h.n(v.weight);
            if (weight <= 0 || weight > 100) {
                throw new Error('The final exam weight must be between 1 and 100.');
            }
            const w = weight / 100;
            const required = (desired - current * (1 - w)) / w;
            let feasibility;
            if (required > 100) feasibility = 'Impossible';
            else if (required <= 0) feasibility = 'Already achieved';
            else feasibility = 'Achievable';
            return [
                { label: 'Required final score', value: required, unit: '%', decimals: 2 },
                { label: 'Feasibility', value: feasibility },
            ];
        JS,
        'tests' => [
            [
                'input' => ['current' => 78, 'desired' => 85, 'weight' => 30],
                'expect' => ['Required final score' => 101.33333333333333, 'Feasibility' => 'Impossible'],
            ],
            [
                'input' => ['current' => 88, 'desired' => 90, 'weight' => 25],
                'expect' => ['Required final score' => 96, 'Feasibility' => 'Achievable'],
            ],
            [
                'input' => ['current' => 95, 'desired' => 80, 'weight' => 10],
                'expect' => ['Required final score' => -55, 'Feasibility' => 'Already achieved'],
            ],
            [
                'input' => ['current' => 100, 'desired' => 100, 'weight' => 50],
                'expect' => ['Required final score' => 100, 'Feasibility' => 'Achievable'],
            ],
        ],
    ],

    [
        'slug' => 'test-score-percentage',
        'name' => 'Test Score Calculator',
        'category' => 'education',
        'description' => 'Convert correct answers into a percentage, letter grade and points earned.',
        'icon' => 'check-circle',
        'featured' => false,
        'keywords' => ['test score', 'percentage', 'letter grade', 'quiz', 'exam'],
        'inputs' => [
            ['name' => 'correct', 'label' => 'Correct answers', 'type' => 'number', 'default' => 45, 'min' => 0, 'step' => 1, 'required' => true],
            ['name' => 'total', 'label' => 'Total questions', 'type' => 'number', 'default' => 50, 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'points', 'label' => 'Points per question', 'type' => 'number', 'default' => 1, 'min' => 0, 'step' => 'any'],
        ],
        'compute' => <<<'JS'
            const correct = h.n(v.correct);
            const total = h.n(v.total);
            const points = h.n(v.points);
            if (total <= 0) throw new Error('The number of questions must be greater than zero.');
            if (correct < 0 || correct > total) {
                throw new Error('Correct answers must be between zero and the total questions.');
            }
            if (points < 0) throw new Error('Points per question cannot be negative.');
            const percentage = (correct / total) * 100;
            const letter = percentage >= 90 ? 'A'
                : percentage >= 80 ? 'B'
                : percentage >= 70 ? 'C'
                : percentage >= 60 ? 'D'
                : 'F';
            return [
                { label: 'Percentage', value: percentage, unit: '%', decimals: 2 },
                { label: 'Letter grade', value: letter },
                { label: 'Points earned', value: correct * points, decimals: 2 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['correct' => 45, 'total' => 50, 'points' => 1],
                'expect' => ['Percentage' => 90, 'Letter grade' => 'A', 'Points earned' => 45],
            ],
            [
                'input' => ['correct' => 42, 'total' => 50, 'points' => 2],
                'expect' => ['Percentage' => 84, 'Letter grade' => 'B', 'Points earned' => 84],
            ],
            [
                'input' => ['correct' => 35, 'total' => 50, 'points' => 1],
                'expect' => ['Percentage' => 70, 'Letter grade' => 'C'],
            ],
            [
                'input' => ['correct' => 29, 'total' => 50, 'points' => 1],
                'expect' => ['Percentage' => 58, 'Letter grade' => 'F'],
            ],
        ],
    ],

    [
        'slug' => 'reading-time-calculator',
        'name' => 'Reading Time Calculator',
        'category' => 'education',
        'description' => 'Estimate how many minutes a text takes to read silently or speak aloud.',
        'icon' => 'book-open',
        'featured' => true,
        'keywords' => ['reading time', 'words', 'speech', 'wpm', 'presentation'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Text',
                'type' => 'textarea',
                'default' => 'one two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen nineteen twenty',
                'placeholder' => 'Paste the text you want to time',
                'required' => true,
            ],
            ['name' => 'wpm', 'label' => 'Reading speed (words per minute)', 'type' => 'number', 'default' => 200, 'min' => 1, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const words = h.words(v.text);
            const wpm = h.n(v.wpm);
            if (words.length === 0) throw new Error('Enter some text to measure.');
            if (wpm <= 0) throw new Error('Reading speed must be greater than zero.');
            return [
                { label: 'Words', value: words.length, decimals: 0 },
                { label: 'Reading time', value: Math.ceil(words.length / wpm), unit: 'min', decimals: 0 },
                { label: 'Speaking time', value: Math.ceil(words.length / 130), unit: 'min', decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => 'one two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen nineteen twenty', 'wpm' => 15],
                'expect' => ['Words' => 20, 'Reading time' => 2, 'Speaking time' => 1],
            ],
            [
                'input' => ['text' => 'one two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen nineteen twenty', 'wpm' => 4],
                'expect' => ['Words' => 20, 'Reading time' => 5, 'Speaking time' => 1],
            ],
            [
                'input' => ['text' => 'alpha beta gamma delta epsilon zeta eta theta iota kappa', 'wpm' => 200],
                'expect' => ['Words' => 10, 'Reading time' => 1, 'Speaking time' => 1],
            ],
        ],
    ],

    [
        'slug' => 'roman-numeral-converter',
        'name' => 'Roman Numeral Converter',
        'category' => 'education',
        'description' => 'Convert numbers from 1 to 3999 into Roman numerals and back again.',
        'icon' => 'type',
        'featured' => false,
        'keywords' => ['roman numerals', 'number', 'convert', 'ancient'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Direction',
                'type' => 'select',
                'default' => 'number-to-roman',
                'options' => [
                    ['value' => 'number-to-roman', 'label' => 'Number → Roman numeral'],
                    ['value' => 'roman-to-number', 'label' => 'Roman numeral → number'],
                ],
            ],
            ['name' => 'value', 'label' => 'Value', 'type' => 'text', 'default' => '1994', 'required' => true, 'hint' => 'Enter a number or a numeral such as MCMXCIV'],
        ],
        'compute' => <<<'JS'
            const toRoman = (num) => {
                const table = [
                    [1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'],
                    [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'],
                    [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
                ];
                let rest = num;
                let out = '';
                for (const [amount, symbol] of table) {
                    while (rest >= amount) {
                        out += symbol;
                        rest -= amount;
                    }
                }
                return out;
            };
            const fromRoman = (str) => {
                const values = { I: 1, V: 5, X: 10, L: 50, C: 100, D: 500, M: 1000 };
                let total = 0;
                for (let i = 0; i < str.length; i++) {
                    const current = values[str[i]];
                    const next = values[str[i + 1]];
                    total += next && next > current ? -current : current;
                }
                return total;
            };
            let number;
            let roman;
            if (v.mode === 'roman-to-number') {
                const raw = String(v.value).trim().toUpperCase().replace(/[\s\u2212\u2013-]/g, '');
                if (!/^[IVXLCDM]+$/.test(raw)) {
                    throw new Error('Enter a Roman numeral using I, V, X, L, C, D and M.');
                }
                number = fromRoman(raw);
                roman = toRoman(number);
                if (roman !== raw) throw new Error('That is not a valid Roman numeral.');
            } else {
                const raw = String(v.value).trim().replace(/,/g, '');
                if (!/^\d+$/.test(raw)) throw new Error('Enter a whole number between 1 and 3999.');
                number = Number(raw);
                if (number < 1 || number > 3999) {
                    throw new Error('Enter a whole number between 1 and 3999.');
                }
                roman = toRoman(number);
            }
            return [
                { label: 'Roman numeral', value: roman },
                { label: 'Number', value: number, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['mode' => 'number-to-roman', 'value' => '1994'],
                'expect' => ['Roman numeral' => 'MCMXCIV', 'Number' => 1994],
            ],
            [
                'input' => ['mode' => 'roman-to-number', 'value' => 'MCMXCIV'],
                'expect' => ['Roman numeral' => 'MCMXCIV', 'Number' => 1994],
            ],
            [
                'input' => ['mode' => 'number-to-roman', 'value' => '4'],
                'expect' => ['Roman numeral' => 'IV', 'Number' => 4],
            ],
            [
                'input' => ['mode' => 'number-to-roman', 'value' => '3999'],
                'expect' => ['Roman numeral' => 'MMMCMXCIX', 'Number' => 3999],
            ],
        ],
    ],

    [
        'slug' => 'multiplication-table-generator',
        'name' => 'Multiplication Table Generator',
        'category' => 'education',
        'description' => 'Print a clean multiplication table for any number up to twenty rows.',
        'icon' => 'table',
        'featured' => false,
        'keywords' => ['multiplication', 'times table', 'math', 'practice', 'kids'],
        'inputs' => [
            ['name' => 'number', 'label' => 'Number', 'type' => 'number', 'default' => 7, 'step' => 1, 'required' => true],
            ['name' => 'upTo', 'label' => 'Rows (up to)', 'type' => 'number', 'default' => 10, 'min' => 1, 'max' => 20, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const number = h.n(v.number);
            const upTo = Math.floor(h.n(v.upTo));
            if (!Number.isInteger(number)) throw new Error('Enter a whole number to build the table.');
            if (upTo < 1 || upTo > 20) throw new Error('Choose a table size between 1 and 20.');
            const lines = [];
            for (let i = 1; i <= upTo; i++) {
                lines.push(`${number} × ${i} = ${number * i}`);
            }
            return [
                { label: 'Table', value: lines.join('\n') },
                { label: 'Sum of products', value: (number * upTo * (upTo + 1)) / 2, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['number' => 7, 'upTo' => 3],
                'expect' => ['Table' => "7 × 1 = 7\n7 × 2 = 14\n7 × 3 = 21", 'Sum of products' => 42],
            ],
            [
                'input' => ['number' => 5, 'upTo' => 5],
                'expect' => ['Table' => "5 × 1 = 5\n5 × 2 = 10\n5 × 3 = 15\n5 × 4 = 20\n5 × 5 = 25", 'Sum of products' => 75],
            ],
            [
                'input' => ['number' => 12, 'upTo' => 1],
                'expect' => ['Table' => '12 × 1 = 12', 'Sum of products' => 12],
            ],
        ],
    ],

    [
        'slug' => 'fraction-calculator',
        'name' => 'Fraction Calculator',
        'category' => 'education',
        'description' => 'Add, subtract, multiply or divide fractions and see the simplified result.',
        'icon' => 'divide',
        'featured' => false,
        'keywords' => ['fraction', 'add fractions', 'simplify', 'numerator', 'denominator'],
        'inputs' => [
            ['name' => 'n1', 'label' => 'First numerator', 'type' => 'number', 'default' => 1, 'step' => 1, 'required' => true],
            ['name' => 'd1', 'label' => 'First denominator', 'type' => 'number', 'default' => 2, 'step' => 1, 'required' => true],
            [
                'name' => 'operation',
                'label' => 'Operation',
                'type' => 'select',
                'default' => '+',
                'options' => [
                    ['value' => '+', 'label' => '+ Add'],
                    ['value' => '-', 'label' => '− Subtract'],
                    ['value' => '*', 'label' => '× Multiply'],
                    ['value' => '/', 'label' => '÷ Divide'],
                ],
            ],
            ['name' => 'n2', 'label' => 'Second numerator', 'type' => 'number', 'default' => 1, 'step' => 1, 'required' => true],
            ['name' => 'd2', 'label' => 'Second denominator', 'type' => 'number', 'default' => 3, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const n1 = h.n(v.n1);
            const d1 = h.n(v.d1);
            const n2 = h.n(v.n2);
            const d2 = h.n(v.d2);
            if (![n1, d1, n2, d2].every(Number.isInteger)) {
                throw new Error('Enter whole numbers for the numerators and denominators.');
            }
            if (d1 === 0 || d2 === 0) throw new Error('Denominators cannot be zero.');
            let num;
            let den;
            if (v.operation === '+') {
                num = n1 * d2 + n2 * d1;
                den = d1 * d2;
            } else if (v.operation === '-') {
                num = n1 * d2 - n2 * d1;
                den = d1 * d2;
            } else if (v.operation === '*') {
                num = n1 * n2;
                den = d1 * d2;
            } else if (v.operation === '/') {
                if (n2 === 0) throw new Error('Cannot divide by a fraction with a zero numerator.');
                num = n1 * d2;
                den = d1 * n2;
            } else {
                throw new Error('Choose a valid operation.');
            }
            if (den < 0) {
                num = -num;
                den = -den;
            }
            const gcd = (a, b) => {
                a = Math.abs(a);
                b = Math.abs(b);
                while (b) {
                    const t = b;
                    b = a % b;
                    a = t;
                }
                return a || 1;
            };
            const divisor = gcd(num, den);
            num /= divisor;
            den /= divisor;
            return [
                { label: 'Result fraction', value: `${num}/${den}` },
                { label: 'Decimal value', value: num / den, decimals: 10 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['n1' => 1, 'd1' => 2, 'operation' => '+', 'n2' => 1, 'd2' => 3],
                'expect' => ['Result fraction' => '5/6', 'Decimal value' => 0.8333333333333334],
            ],
            [
                'input' => ['n1' => 3, 'd1' => 4, 'operation' => '*', 'n2' => 2, 'd2' => 3],
                'expect' => ['Result fraction' => '1/2', 'Decimal value' => 0.5],
            ],
            [
                'input' => ['n1' => 1, 'd1' => 2, 'operation' => '/', 'n2' => 1, 'd2' => 4],
                'expect' => ['Result fraction' => '2/1', 'Decimal value' => 2],
            ],
            [
                'input' => ['n1' => 1, 'd1' => 2, 'operation' => '-', 'n2' => 1, 'd2' => 2],
                'expect' => ['Result fraction' => '0/1', 'Decimal value' => 0],
            ],
        ],
    ],
];
