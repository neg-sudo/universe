<?php
/**
 * UniVerse — Education (expanded catalogue).
 * 8 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'cgpa-calculator',
        'name' => 'CGPA Calculator',
        'category' => 'education',
        'description' => 'Calculate a credit-weighted GPA from a list of course grades.',
        'icon' => 'graduation-cap',
        'keywords' => ['cgpa', 'gpa', 'grades', 'credits', 'university'],
        'inputs' => [
            ['name' => 'courses', 'label' => 'Courses', 'type' => 'textarea', 'default' => "Math: 4.0, 3\nPhysics: 3.0, 4", 'required' => true, 'hint' => 'One course per line: grade point, credits.'],
        ],
        'compute' => <<<'JS'
    const lines = String(v.courses ?? '').split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
    if (lines.length === 0) throw new Error('Enter at least one course.');
    let points = 0;
    let credits = 0;
    let counted = 0;
    for (const line of lines) {
        const numbers = (line.match(/\d+(?:\.\d+)?/g) || []).map(Number);
        if (numbers.length < 2) throw new Error('Each line needs a grade point and credits: ' + line);
        const grade = numbers[0];
        const credit = numbers[1];
        if (grade < 0 || grade > 5) throw new Error('Grade points must be between 0 and 5: ' + line);
        if (credit <= 0) throw new Error('Credits must be greater than zero: ' + line);
        points += grade * credit;
        credits += credit;
        counted++;
    }
    return [
        { label: 'CGPA', value: points / credits },
        { label: 'Total credits', value: credits, decimals: 0 },
        { label: 'Courses', value: counted, decimals: 0 },
        { label: 'Total grade points', value: points },
    ];
JS,
        'tests' => [
            ['input' => ['courses' => "Math: 4.0, 3\nPhysics: 3.0, 4"], 'expect' => ['CGPA' => 3.4285714285714284, 'Total credits' => 7, 'Courses' => 2, 'Total grade points' => 24]],
            ['input' => ['courses' => "A: 4, 3\nB: 3, 3"], 'expect' => ['CGPA' => 3.5, 'Total credits' => 6, 'Courses' => 2]],
        ],
    ],
    [
        'slug' => 'attendance-calculator',
        'name' => 'Attendance Calculator',
        'category' => 'education',
        'description' => 'Check attendance percentage and how many classes you can still miss.',
        'icon' => 'calendar-days',
        'keywords' => ['attendance', 'classes', 'absent', 'percentage', 'school'],
        'inputs' => [
            ['name' => 'attended', 'label' => 'Classes attended', 'type' => 'number', 'default' => 18, 'min' => 0, 'step' => '1', 'required' => true],
            ['name' => 'total', 'label' => 'Classes held', 'type' => 'number', 'default' => 20, 'min' => 1, 'step' => '1', 'required' => true],
            ['name' => 'target', 'label' => 'Target attendance (%)', 'type' => 'number', 'default' => 90, 'min' => 0, 'max' => 100, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const attended = Math.trunc(h.n(v.attended));
    const total = Math.trunc(h.n(v.total));
    const target = h.n(v.target);
    if (attended < 0 || total <= 0) throw new Error('Enter a positive number of classes.');
    if (attended > total) throw new Error('Attended classes cannot exceed classes held.');
    if (target < 0 || target > 100) throw new Error('The target must be between 0 and 100 percent.');
    const current = (attended / total) * 100;
    const needed = target >= 100
        ? (attended === total ? 0 : null)
        : Math.max(0, Math.ceil((target * total - 100 * attended) / (100 - target) - 1e-9));
    const canSkip = target === 0 ? 0 : Math.max(0, Math.floor((attended * 100) / target - total));
    return [
        { label: 'Attendance', value: current, unit: '%' },
        { label: 'Absences', value: total - attended, decimals: 0 },
        { label: 'Classes needed', value: needed === null ? 'Not reachable' : needed, decimals: 0 },
        { label: 'Classes you can skip', value: canSkip, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['attended' => 18, 'total' => 20, 'target' => 90], 'expect' => ['Attendance' => 90, 'Absences' => 2, 'Classes needed' => 0, 'Classes you can skip' => 0]],
            ['input' => ['attended' => 19, 'total' => 20, 'target' => 90], 'expect' => ['Attendance' => 95, 'Absences' => 1, 'Classes needed' => 0, 'Classes you can skip' => 1]],
            ['input' => ['attended' => 10, 'total' => 20, 'target' => 80], 'expect' => ['Attendance' => 50, 'Absences' => 10, 'Classes needed' => 30, 'Classes you can skip' => 0]],
        ],
    ],
    [
        'slug' => 'quiz-score-calculator',
        'name' => 'Quiz Score Calculator',
        'category' => 'education',
        'description' => 'Score a quiz with points for correct answers and penalties for wrong ones.',
        'icon' => 'target',
        'keywords' => ['quiz', 'score', 'test', 'penalty', 'grading'],
        'inputs' => [
            ['name' => 'correct', 'label' => 'Correct answers', 'type' => 'number', 'default' => 8, 'min' => 0, 'step' => '1', 'required' => true],
            ['name' => 'wrong', 'label' => 'Wrong answers', 'type' => 'number', 'default' => 2, 'min' => 0, 'step' => '1', 'required' => true],
            ['name' => 'points', 'label' => 'Points per correct answer', 'type' => 'number', 'default' => 1, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'penalty', 'label' => 'Penalty per wrong answer', 'type' => 'number', 'default' => 0.25, 'min' => 0, 'step' => 'any'],
        ],
        'compute' => <<<'JS'
    const correct = Math.trunc(h.n(v.correct)), wrong = Math.trunc(h.n(v.wrong));
    const points = h.n(v.points), penalty = h.n(v.penalty || 0);
    if (correct < 0 || wrong < 0 || points < 0 || penalty < 0) throw new Error('Values cannot be negative.');
    const questions = correct + wrong;
    if (questions === 0) throw new Error('Enter at least one answer.');
    const possible = questions * points;
    const score = correct * points - wrong * penalty;
    return [
        { label: 'Score', value: score },
        { label: 'Possible score', value: possible },
        { label: 'Percentage', value: possible === 0 ? 0 : (score / possible) * 100, unit: '%' },
        { label: 'Questions', value: questions, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['correct' => 8, 'wrong' => 2, 'points' => 1, 'penalty' => 0.25], 'expect' => ['Score' => 7.5, 'Possible score' => 10, 'Percentage' => 75, 'Questions' => 10]],
            ['input' => ['correct' => 10, 'wrong' => 0, 'points' => 2, 'penalty' => 0.5], 'expect' => ['Score' => 20, 'Possible score' => 20, 'Percentage' => 100]],
            ['input' => ['correct' => 5, 'wrong' => 5, 'points' => 1, 'penalty' => 1], 'expect' => ['Score' => 0, 'Percentage' => 0]],
        ],
    ],
    [
        'slug' => 'letter-grade-calculator',
        'name' => 'Letter Grade Calculator',
        'category' => 'education',
        'description' => 'Convert a percentage into a letter grade and grade point average.',
        'icon' => 'graduation-cap',
        'keywords' => ['letter grade', 'percentage', 'gpa', 'grading scale', 'school'],
        'inputs' => [
            ['name' => 'percentage', 'label' => 'Percentage', 'type' => 'number', 'default' => 95, 'min' => 0, 'max' => 100, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const score = h.n(v.percentage);
    if (score < 0 || score > 100) throw new Error('Enter a percentage between 0 and 100.');
    const scale = [
        [97, 'A+', 4.0], [93, 'A', 4.0], [90, 'A-', 3.7], [87, 'B+', 3.3],
        [83, 'B', 3.0], [80, 'B-', 2.7], [77, 'C+', 2.3], [73, 'C', 2.0],
        [70, 'C-', 1.7], [67, 'D+', 1.3], [63, 'D', 1.0], [60, 'D-', 0.7],
    ];
    const match = scale.find(([minimum]) => score >= minimum);
    const letter = match ? match[1] : 'F';
    const points = match ? match[2] : 0;
    return [
        { label: 'Letter grade', value: letter },
        { label: 'Grade points', value: points },
        { label: 'Status', value: score >= 60 ? 'Pass' : 'Fail' },
        { label: 'Percentage', value: score, unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['percentage' => 95], 'expect' => ['Letter grade' => 'A', 'Grade points' => 4, 'Status' => 'Pass']],
            ['input' => ['percentage' => 82], 'expect' => ['Letter grade' => 'B-', 'Grade points' => 2.7, 'Status' => 'Pass']],
            ['input' => ['percentage' => 59], 'expect' => ['Letter grade' => 'F', 'Grade points' => 0, 'Status' => 'Fail']],
            ['input' => ['percentage' => 100], 'expect' => ['Letter grade' => 'A+', 'Grade points' => 4, 'Status' => 'Pass']],
        ],
    ],
    [
        'slug' => 'study-time-planner',
        'name' => 'Study Time Planner',
        'category' => 'education',
        'description' => 'Divide study time across topics and days to build a revision plan.',
        'icon' => 'clock',
        'keywords' => ['study', 'revision', 'planner', 'schedule', 'topics'],
        'inputs' => [
            ['name' => 'topics', 'label' => 'Topics to study', 'type' => 'number', 'default' => 10, 'min' => 1, 'step' => '1', 'required' => true],
            ['name' => 'minutes', 'label' => 'Minutes per topic', 'type' => 'number', 'default' => 30, 'min' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'days', 'label' => 'Days available', 'type' => 'number', 'default' => 5, 'min' => 1, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const topics = Math.trunc(h.n(v.topics)), minutes = h.n(v.minutes), days = Math.trunc(h.n(v.days));
    if (topics <= 0 || minutes <= 0 || days <= 0) throw new Error('All values must be greater than zero.');
    const total = topics * minutes;
    return [
        { label: 'Total study time', value: total, unit: 'min' },
        { label: 'Minutes per day', value: total / days, unit: 'min' },
        { label: 'Hours in total', value: total / 60 },
        { label: 'Hours per day', value: total / days / 60 },
    ];
JS,
        'tests' => [
            ['input' => ['topics' => 10, 'minutes' => 30, 'days' => 5], 'expect' => ['Total study time' => 300, 'Minutes per day' => 60, 'Hours in total' => 5, 'Hours per day' => 1]],
            ['input' => ['topics' => 6, 'minutes' => 25, 'days' => 3], 'expect' => ['Total study time' => 150, 'Minutes per day' => 50, 'Hours in total' => 2.5, 'Hours per day' => 0.8333333333333334]],
        ],
    ],
    [
        'slug' => 'significant-figures-counter',
        'name' => 'Significant Figures Counter',
        'category' => 'education',
        'description' => 'Count the significant figures in a measured number.',
        'icon' => 'hash',
        'keywords' => ['significant figures', 'sig figs', 'precision', 'chemistry', 'physics'],
        'inputs' => [
            ['name' => 'number', 'label' => 'Number', 'type' => 'text', 'default' => '0.00450', 'required' => true, 'hint' => 'Include a decimal point to show trailing zeros are significant.'],
        ],
        'compute' => <<<'JS'
    const raw = String(v.number ?? '').trim();
    if (raw === '' || !/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:e[+-]?\d+)?$/i.test(raw)) throw new Error('Enter a valid number.');
    const mantissa = raw.replace(/^[+-]/, '').split(/e/i)[0];
    const hasDecimal = mantissa.includes('.');
    let digits = mantissa.replace('.', '');
    if (!/\d/.test(digits)) throw new Error('Enter a number containing digits.');
    digits = digits.replace(/^0+/, '');
    if (digits === '') {
        return [
            { label: 'Significant figures', value: 1, decimals: 0 },
            { label: 'Digits', value: 1, decimals: 0 },
            { label: 'Decimal point', value: hasDecimal ? 'Yes' : 'No' },
        ];
    }
    if (!hasDecimal) digits = digits.replace(/0+$/, '');
    return [
        { label: 'Significant figures', value: digits.length, decimals: 0 },
        { label: 'Digits', value: digits.length, decimals: 0 },
        { label: 'Decimal point', value: hasDecimal ? 'Yes' : 'No' },
    ];
JS,
        'tests' => [
            ['input' => ['number' => '0.00450'], 'expect' => ['Significant figures' => 3, 'Digits' => 3, 'Decimal point' => 'Yes']],
            ['input' => ['number' => '1200'], 'expect' => ['Significant figures' => 2, 'Digits' => 2, 'Decimal point' => 'No']],
            ['input' => ['number' => '1200.'], 'expect' => ['Significant figures' => 4, 'Digits' => 4, 'Decimal point' => 'Yes']],
            ['input' => ['number' => '1.230'], 'expect' => ['Significant figures' => 4, 'Digits' => 4]],
            ['input' => ['number' => '0.0012'], 'expect' => ['Significant figures' => 2]],
        ],
    ],
    [
        'slug' => 'number-to-words',
        'name' => 'Number to Words',
        'category' => 'education',
        'description' => 'Write a whole number out in English words.',
        'icon' => 'type',
        'keywords' => ['number to words', 'spelling', 'english', 'writing', 'numbers'],
        'inputs' => [
            ['name' => 'number', 'label' => 'Number', 'type' => 'number', 'default' => 123, 'min' => -999999999999, 'max' => 999999999999, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const input = Math.trunc(h.n(v.number));
    const ones = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
    const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
    const under1000 = (n) => {
        const parts = [];
        if (n >= 100) {
            parts.push(ones[Math.floor(n / 100)] + ' hundred');
            n %= 100;
        }
        if (n >= 20) {
            const t = tens[Math.floor(n / 10)];
            const r = n % 10;
            parts.push(r ? `${t}-${ones[r]}` : t);
        } else if (n > 0) {
            parts.push(ones[n]);
        }
        return parts.join(' ');
    };
    const scales = ['', 'thousand', 'million', 'billion'];
    let value = Math.abs(input);
    if (value === 0) return [{ label: 'Words', value: 'zero' }, { label: 'Digits', value: 1, decimals: 0 }];
    const groups = [];
    let index = 0;
    while (value > 0 && index < scales.length) {
        const chunk = value % 1000;
        if (chunk > 0) {
            const words = under1000(chunk);
            groups.unshift(scales[index] ? `${words} ${scales[index]}` : words);
        }
        value = Math.floor(value / 1000);
        index++;
    }
    const words = (input < 0 ? 'minus ' : '') + groups.join(' ');
    return [
        { label: 'Words', value: words },
        { label: 'Digits', value: String(Math.abs(input)).length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['number' => 123], 'expect' => ['Words' => 'one hundred twenty-three', 'Digits' => 3]],
            ['input' => ['number' => 1001], 'expect' => ['Words' => 'one thousand one', 'Digits' => 4]],
            ['input' => ['number' => 1000000], 'expect' => ['Words' => 'one million', 'Digits' => 7]],
            ['input' => ['number' => 0], 'expect' => ['Words' => 'zero', 'Digits' => 1]],
            ['input' => ['number' => -42], 'expect' => ['Words' => 'minus forty-two', 'Digits' => 2]],
        ],
    ],
    [
        'slug' => 'citation-generator',
        'name' => 'Citation Generator',
        'category' => 'education',
        'description' => 'Format a book or article citation in APA or MLA style.',
        'icon' => 'book-open',
        'keywords' => ['citation', 'apa', 'mla', 'bibliography', 'reference'],
        'inputs' => [
            ['name' => 'author', 'label' => 'Author (Last, First)', 'type' => 'text', 'default' => 'Smith, John', 'required' => true],
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'default' => 2020, 'min' => 0, 'max' => 3000, 'step' => '1', 'required' => true],
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'The History of Tools', 'required' => true],
            ['name' => 'source', 'label' => 'Publisher or website', 'type' => 'text', 'default' => 'UniVerse Press', 'required' => true],
            ['name' => 'url', 'label' => 'URL (optional)', 'type' => 'text', 'default' => 'https://example.com'],
            ['name' => 'style', 'label' => 'Style', 'type' => 'select', 'default' => 'apa', 'options' => [
                ['value' => 'apa', 'label' => 'APA'],
                ['value' => 'mla', 'label' => 'MLA'],
            ]],
        ],
        'compute' => <<<'JS'
    const author = String(v.author ?? '').trim();
    const title = String(v.title ?? '').trim();
    const source = String(v.source ?? '').trim();
    const url = String(v.url ?? '').trim();
    const year = Math.trunc(h.n(v.year));
    if (!author || !title || !source) throw new Error('Author, title and source are required.');
    const suffix = url ? ' ' + url : '';
    const citation = v.style === 'mla'
        ? `${author}. "${title}." ${source}, ${year}.${suffix}`
        : `${author}. (${year}). ${title}. ${source}.${suffix}`;
    return [
        { label: 'Citation', value: citation },
        { label: 'Style', value: v.style === 'mla' ? 'MLA' : 'APA' },
        { label: 'Year', value: year, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['author' => 'Smith, John', 'year' => 2020, 'title' => 'The History of Tools', 'source' => 'UniVerse Press', 'url' => 'https://example.com', 'style' => 'apa'], 'expect' => ['Citation' => 'Smith, John. (2020). The History of Tools. UniVerse Press. https://example.com', 'Style' => 'APA', 'Year' => 2020]],
            ['input' => ['author' => 'Smith, John', 'year' => 2020, 'title' => 'The History of Tools', 'source' => 'UniVerse Press', 'url' => 'https://example.com', 'style' => 'mla'], 'expect' => ['Citation' => 'Smith, John. "The History of Tools." UniVerse Press, 2020. https://example.com', 'Style' => 'MLA']],
            ['input' => ['author' => 'Doe, Jane', 'year' => 1999, 'title' => 'Knowledge', 'source' => 'Open Books', 'url' => '', 'style' => 'apa'], 'expect' => ['Citation' => 'Doe, Jane. (1999). Knowledge. Open Books.']],
        ],
    ],
];
