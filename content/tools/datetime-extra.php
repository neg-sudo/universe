<?php
/**
 * UniVerse — Date and time (expanded catalogue).
 * 10 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'months-between-dates',
        'name' => 'Months Between Dates',
        'category' => 'datetime',
        'description' => 'Count whole months, days and years between two calendar dates.',
        'icon' => 'calendar-days',
        'keywords' => ['months', 'dates', 'duration', 'years', 'days'],
        'inputs' => [
            ['name' => 'start', 'label' => 'Start date', 'type' => 'date', 'default' => '2024-01-15', 'required' => true],
            ['name' => 'end', 'label' => 'End date', 'type' => 'date', 'default' => '2024-04-15', 'required' => true],
        ],
        'compute' => <<<'JS'
    const parse = (s) => {
        const p = String(s).split('-').map(Number);
        if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter valid dates.');
        return Date.UTC(p[0], p[1] - 1, p[2]);
    };
    const start = parse(v.start), end = parse(v.end);
    const a = new Date(start), b = new Date(end);
    let months = (b.getUTCFullYear() - a.getUTCFullYear()) * 12 + (b.getUTCMonth() - a.getUTCMonth());
    if (b.getUTCDate() < a.getUTCDate()) months -= 1;
    const days = Math.round((end - start) / 86400000);
    return [
        { label: 'Months', value: months, decimals: 0 },
        { label: 'Days', value: days, decimals: 0 },
        { label: 'Years', value: months / 12 },
    ];
JS,
        'tests' => [
            ['input' => ['start' => '2024-01-15', 'end' => '2024-04-15'], 'expect' => ['Months' => 3, 'Days' => 91, 'Years' => 0.25]],
            ['input' => ['start' => '2024-01-31', 'end' => '2024-02-29'], 'expect' => ['Months' => 0, 'Days' => 29, 'Years' => 0]],
            ['input' => ['start' => '2023-01-01', 'end' => '2024-01-01'], 'expect' => ['Months' => 12, 'Days' => 365, 'Years' => 1]],
        ],
    ],
    [
        'slug' => 'next-birthday-calculator',
        'name' => 'Next Birthday Calculator',
        'category' => 'datetime',
        'description' => 'Find the next birthday date, days remaining and age being turned.',
        'icon' => 'calendar-days',
        'keywords' => ['birthday', 'age', 'countdown', 'date'],
        'inputs' => [
            ['name' => 'birthDate', 'label' => 'Birth date', 'type' => 'date', 'default' => '1990-06-15', 'required' => true],
            ['name' => 'asOf', 'label' => 'As of date', 'type' => 'date', 'default' => '', 'hint' => 'Leave blank to use today.'],
        ],
        'compute' => <<<'JS'
    const parse = (s) => {
        const p = String(s).split('-').map(Number);
        if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter a valid birth date.');
        return { y: p[0], m: p[1], d: p[2] };
    };
    const birth = parse(v.birthDate);
    const asOfText = String(v.asOf ?? '').trim();
    const asOfDate = asOfText ? parse(asOfText) : (() => { const n = new Date(); return { y: n.getUTCFullYear(), m: n.getUTCMonth() + 1, d: n.getUTCDate() }; })();
    const asOf = Date.UTC(asOfDate.y, asOfDate.m - 1, asOfDate.d);
    const daysInMonth = (y, m) => new Date(Date.UTC(y, m, 0)).getUTCDate();
    let year = asOfDate.y;
    let next = Date.UTC(year, birth.m - 1, Math.min(birth.d, daysInMonth(year, birth.m)));
    if (next < asOf) {
        year += 1;
        next = Date.UTC(year, birth.m - 1, Math.min(birth.d, daysInMonth(year, birth.m)));
    }
    const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const iso = new Date(next).toISOString().slice(0, 10);
    return [
        { label: 'Next birthday', value: iso },
        { label: 'Days until', value: Math.round((next - asOf) / 86400000), decimals: 0 },
        { label: 'Age turning', value: year - birth.y, decimals: 0 },
        { label: 'Weekday', value: weekdays[new Date(next).getUTCDay()] },
    ];
JS,
        'tests' => [
            ['input' => ['birthDate' => '1990-06-15', 'asOf' => '2024-06-01'], 'expect' => ['Next birthday' => '2024-06-15', 'Days until' => 14, 'Age turning' => 34, 'Weekday' => 'Saturday']],
            ['input' => ['birthDate' => '2000-01-10', 'asOf' => '2024-12-31'], 'expect' => ['Next birthday' => '2025-01-10', 'Days until' => 10, 'Age turning' => 25, 'Weekday' => 'Friday']],
        ],
    ],
    [
        'slug' => 'zodiac-sign-finder',
        'name' => 'Zodiac Sign Finder',
        'category' => 'datetime',
        'description' => 'Find the Western zodiac sign and element for a birth date.',
        'icon' => 'sparkles',
        'keywords' => ['zodiac', 'star sign', 'horoscope', 'birth date'],
        'inputs' => [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'default' => '1990-06-15', 'required' => true],
        ],
        'compute' => <<<'JS'
    const p = String(v.date).split('-').map(Number);
    if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter a valid date.');
    const md = p[1] * 100 + p[2];
    const signs = [
        [120, 'Capricorn', 'Earth'], [219, 'Aquarius', 'Air'], [320, 'Pisces', 'Water'],
        [420, 'Aries', 'Fire'], [521, 'Taurus', 'Earth'], [621, 'Gemini', 'Air'],
        [722, 'Cancer', 'Water'], [823, 'Leo', 'Fire'], [923, 'Virgo', 'Earth'],
        [1023, 'Libra', 'Air'], [1122, 'Scorpio', 'Water'], [1222, 'Sagittarius', 'Fire'],
        [1231, 'Capricorn', 'Earth'],
    ];
    const sign = signs.find(([limit]) => md <= limit) || signs[signs.length - 1];
    return [
        { label: 'Zodiac sign', value: sign[1] },
        { label: 'Element', value: sign[2] },
    ];
JS,
        'tests' => [
            ['input' => ['date' => '1990-06-15'], 'expect' => ['Zodiac sign' => 'Gemini', 'Element' => 'Air']],
            ['input' => ['date' => '2000-12-25'], 'expect' => ['Zodiac sign' => 'Capricorn', 'Element' => 'Earth']],
            ['input' => ['date' => '1985-10-31'], 'expect' => ['Zodiac sign' => 'Scorpio', 'Element' => 'Water']],
        ],
    ],
    [
        'slug' => 'chinese-zodiac-finder',
        'name' => 'Chinese Zodiac Finder',
        'category' => 'datetime',
        'description' => 'Find the Chinese zodiac animal, element and yin-yang for a year.',
        'icon' => 'sparkles',
        'keywords' => ['chinese zodiac', 'animal', 'element', 'lunar year'],
        'inputs' => [
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'default' => 2020, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const year = Math.trunc(h.n(v.year));
    const animals = ['Rat', 'Ox', 'Tiger', 'Rabbit', 'Dragon', 'Snake', 'Horse', 'Goat', 'Monkey', 'Rooster', 'Dog', 'Pig'];
    const elements = ['Wood', 'Wood', 'Fire', 'Fire', 'Earth', 'Earth', 'Metal', 'Metal', 'Water', 'Water'];
    const animalIndex = ((year - 4) % 12 + 12) % 12;
    const elementIndex = ((year - 4) % 10 + 10) % 10;
    return [
        { label: 'Animal', value: animals[animalIndex] },
        { label: 'Element', value: elements[elementIndex] },
        { label: 'Yin or Yang', value: year % 2 === 0 ? 'Yang' : 'Yin' },
    ];
JS,
        'tests' => [
            ['input' => ['year' => 2020], 'expect' => ['Animal' => 'Rat', 'Element' => 'Metal', 'Yin or Yang' => 'Yang']],
            ['input' => ['year' => 1990], 'expect' => ['Animal' => 'Horse', 'Element' => 'Metal', 'Yin or Yang' => 'Yang']],
            ['input' => ['year' => 1984], 'expect' => ['Animal' => 'Rat', 'Element' => 'Wood', 'Yin or Yang' => 'Yang']],
        ],
    ],
    [
        'slug' => 'season-finder',
        'name' => 'Season Finder',
        'category' => 'datetime',
        'description' => 'Find the meteorological season for a date and hemisphere.',
        'icon' => 'sun',
        'keywords' => ['season', 'summer', 'winter', 'hemisphere', 'date'],
        'inputs' => [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'default' => '2024-04-15', 'required' => true],
            ['name' => 'hemisphere', 'label' => 'Hemisphere', 'type' => 'select', 'default' => 'north', 'options' => [
                ['value' => 'north', 'label' => 'Northern'],
                ['value' => 'south', 'label' => 'Southern'],
            ]],
        ],
        'compute' => <<<'JS'
    const p = String(v.date).split('-').map(Number);
    if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter a valid date.');
    const month = p[1];
    const northern = month >= 3 && month <= 5 ? 'Spring' : month >= 6 && month <= 8 ? 'Summer' : month >= 9 && month <= 11 ? 'Autumn' : 'Winter';
    const seasons = { Spring: 'Autumn', Summer: 'Winter', Autumn: 'Spring', Winter: 'Summer' };
    const dayOfYear = Math.floor((Date.UTC(p[0], p[1] - 1, p[2]) - Date.UTC(p[0], 0, 1)) / 86400000) + 1;
    return [
        { label: 'Season', value: v.hemisphere === 'south' ? seasons[northern] : northern },
        { label: 'Day of year', value: dayOfYear, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['date' => '2024-04-15', 'hemisphere' => 'north'], 'expect' => ['Season' => 'Spring', 'Day of year' => 106]],
            ['input' => ['date' => '2024-07-01', 'hemisphere' => 'north'], 'expect' => ['Season' => 'Summer', 'Day of year' => 183]],
            ['input' => ['date' => '2024-01-15', 'hemisphere' => 'south'], 'expect' => ['Season' => 'Summer', 'Day of year' => 15]],
        ],
    ],
    [
        'slug' => 'julian-date-converter',
        'name' => 'Julian Date Converter',
        'category' => 'datetime',
        'description' => 'Convert a Gregorian date to Julian day number and modified Julian date.',
        'icon' => 'calendar-days',
        'keywords' => ['julian date', 'jdn', 'astronomy', 'date conversion'],
        'inputs' => [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'default' => '2000-01-01', 'required' => true],
        ],
        'compute' => <<<'JS'
    const p = String(v.date).split('-').map(Number);
    if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter a valid date.');
    const [year, month, day] = p;
    const a = Math.floor((14 - month) / 12);
    const y = year + 4800 - a;
    const m = month + 12 * a - 3;
    const jdn = day + Math.floor((153 * m + 2) / 5) + 365 * y + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) - 32045;
    const dayOfYear = Math.floor((Date.UTC(year, month - 1, day) - Date.UTC(year, 0, 1)) / 86400000) + 1;
    return [
        { label: 'Julian day number', value: jdn, decimals: 0 },
        { label: 'Modified Julian date', value: jdn - 2400000.5 },
        { label: 'Day of year', value: dayOfYear, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['date' => '2000-01-01'], 'expect' => ['Julian day number' => 2451545, 'Modified Julian date' => 51544.5, 'Day of year' => 1]],
            ['input' => ['date' => '2024-01-01'], 'expect' => ['Julian day number' => 2460311, 'Modified Julian date' => 60310.5, 'Day of year' => 1]],
            ['input' => ['date' => '2024-02-29'], 'expect' => ['Julian day number' => 2460370, 'Day of year' => 60]],
        ],
    ],
    [
        'slug' => 'moon-phase-calculator',
        'name' => 'Moon Phase Calculator',
        'category' => 'datetime',
        'description' => 'Estimate the moon phase, lunar age and illumination for a date.',
        'icon' => 'moon',
        'keywords' => ['moon', 'lunar phase', 'astronomy', 'illumination'],
        'inputs' => [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'default' => '2024-04-08', 'required' => true],
        ],
        'compute' => <<<'JS'
    const p = String(v.date).split('-').map(Number);
    if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter a valid date.');
    const [year, month, day] = p;
    const a = Math.floor((14 - month) / 12);
    const y = year + 4800 - a;
    const m = month + 12 * a - 3;
    const jdn = day + Math.floor((153 * m + 2) / 5) + 365 * y + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) - 32045;
    const synodic = 29.530588853;
    const age = ((jdn - 2451550.1) % synodic + synodic) % synodic;
    const illumination = ((1 - Math.cos((2 * Math.PI * age) / synodic)) / 2) * 100;
    let phase;
    if (age < 1.845 || age >= 27.685) phase = 'New moon';
    else if (age < 5.536) phase = 'Waxing crescent';
    else if (age < 9.228) phase = 'First quarter';
    else if (age < 12.919) phase = 'Waxing gibbous';
    else if (age < 16.611) phase = 'Full moon';
    else if (age < 20.302) phase = 'Waning gibbous';
    else if (age < 23.993) phase = 'Last quarter';
    else phase = 'Waning crescent';
    return [
        { label: 'Phase', value: phase },
        { label: 'Illumination', value: illumination, unit: '%' },
        { label: 'Lunar age', value: age, unit: 'days' },
    ];
JS,
        'tests' => [
            ['input' => ['date' => '2000-01-06'], 'expect' => ['Phase' => 'New moon', 'Illumination' => 0.011317204094735134, 'Lunar age' => 29.43058885290687]],
            ['input' => ['date' => '2000-01-21'], 'expect' => ['Phase' => 'Full moon', 'Illumination' => 99.97946489483991, 'Lunar age' => 14.899999999906864]],
            ['input' => ['date' => '2024-04-08'], 'expect' => ['Phase' => 'New moon', 'Illumination' => 0.08659842643172211]],
        ],
    ],
    [
        'slug' => 'calendar-generator',
        'name' => 'Calendar Generator',
        'category' => 'datetime',
        'description' => 'Generate a printable text calendar for any month and year.',
        'icon' => 'calendar-days',
        'keywords' => ['calendar', 'month', 'year', 'print', 'weekday'],
        'inputs' => [
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'default' => 2024, 'step' => '1', 'required' => true],
            ['name' => 'month', 'label' => 'Month (1–12)', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 12, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const year = Math.trunc(h.n(v.year));
    const month = Math.trunc(h.n(v.month));
    if (month < 1 || month > 12) throw new Error('Month must be between 1 and 12.');
    const names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const first = new Date(Date.UTC(year, month - 1, 1));
    const days = new Date(Date.UTC(year, month, 0)).getUTCDate();
    const start = first.getUTCDay();
    const cells = [];
    for (let i = 0; i < start; i++) cells.push('');
    for (let d = 1; d <= days; d++) cells.push(String(d));
    while (cells.length % 7 !== 0) cells.push('');
    const rows = [];
    for (let i = 0; i < cells.length; i += 7) {
        rows.push(cells.slice(i, i + 7).map((c) => c.padStart(2, ' ')).join(' '));
    }
    return [
        { label: 'Month', value: `${names[month - 1]} ${year}` },
        { label: 'Days', value: days, decimals: 0 },
        { label: 'First weekday', value: weekdays[start] },
        { label: 'Calendar', value: ['Su Mo Tu We Th Fr Sa', ...rows].join('\n') },
    ];
JS,
        'tests' => [
            ['input' => ['year' => 2024, 'month' => 2], 'expect' => ['Month' => 'February 2024', 'Days' => 29, 'First weekday' => 'Thursday']],
            ['input' => ['year' => 2024, 'month' => 1], 'expect' => ['Month' => 'January 2024', 'Days' => 31, 'First weekday' => 'Monday']],
            ['input' => ['year' => 2023, 'month' => 2], 'expect' => ['Month' => 'February 2023', 'Days' => 28, 'First weekday' => 'Wednesday']],
        ],
    ],
    [
        'slug' => 'add-business-days',
        'name' => 'Add Business Days',
        'category' => 'datetime',
        'description' => 'Add weekdays to a date while skipping Saturdays and Sundays.',
        'icon' => 'calendar-days',
        'keywords' => ['business days', 'working days', 'deadline', 'date'],
        'inputs' => [
            ['name' => 'start', 'label' => 'Start date', 'type' => 'date', 'default' => '2024-01-05', 'required' => true],
            ['name' => 'days', 'label' => 'Business days to add', 'type' => 'number', 'default' => 1, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const p = String(v.start).split('-').map(Number);
    if (p.length !== 3 || p.some((x) => !Number.isFinite(x))) throw new Error('Enter a valid start date.');
    const start = Date.UTC(p[0], p[1] - 1, p[2]);
    const add = Math.trunc(h.n(v.days));
    if (add < 0) throw new Error('Business days must be zero or greater.');
    let current = start;
    let remaining = add;
    while (remaining > 0) {
        current += 86400000;
        const weekday = new Date(current).getUTCDay();
        if (weekday !== 0 && weekday !== 6) remaining--;
    }
    const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    return [
        { label: 'Result date', value: new Date(current).toISOString().slice(0, 10) },
        { label: 'Weekday', value: weekdays[new Date(current).getUTCDay()] },
        { label: 'Calendar days added', value: Math.round((current - start) / 86400000), decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['start' => '2024-01-05', 'days' => 1], 'expect' => ['Result date' => '2024-01-08', 'Weekday' => 'Monday', 'Calendar days added' => 3]],
            ['input' => ['start' => '2024-01-01', 'days' => 5], 'expect' => ['Result date' => '2024-01-08', 'Weekday' => 'Monday', 'Calendar days added' => 7]],
            ['input' => ['start' => '2024-01-06', 'days' => 1], 'expect' => ['Result date' => '2024-01-08', 'Weekday' => 'Monday', 'Calendar days added' => 2]],
        ],
    ],
    [
        'slug' => 'time-card-calculator',
        'name' => 'Time Card Calculator',
        'category' => 'datetime',
        'description' => 'Calculate hours worked from clock-in and clock-out times.',
        'icon' => 'clock',
        'keywords' => ['time card', 'hours worked', 'timesheet', 'overtime'],
        'inputs' => [
            ['name' => 'clockIn', 'label' => 'Clock in', 'type' => 'time', 'default' => '09:00', 'required' => true],
            ['name' => 'clockOut', 'label' => 'Clock out', 'type' => 'time', 'default' => '17:30', 'required' => true],
            ['name' => 'breakMinutes', 'label' => 'Unpaid break (minutes)', 'type' => 'number', 'default' => 30, 'step' => '1'],
        ],
        'compute' => <<<'JS'
    const parse = (s) => {
        const p = String(s).split(':').map(Number);
        if (p.length < 2 || !Number.isFinite(p[0]) || !Number.isFinite(p[1])) throw new Error('Enter valid times.');
        return p[0] * 60 + p[1];
    };
    let minutes = parse(v.clockOut) - parse(v.clockIn);
    if (minutes < 0) minutes += 1440;
    minutes -= Math.max(0, h.n(v.breakMinutes || 0));
    if (minutes < 0) throw new Error('The break is longer than the shift.');
    const decimal = minutes / 60;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return [
        { label: 'Hours worked', value: `${hours}h ${mins}m` },
        { label: 'Decimal hours', value: decimal },
        { label: 'Regular hours', value: Math.min(decimal, 8) },
        { label: 'Overtime hours', value: Math.max(0, decimal - 8) },
    ];
JS,
        'tests' => [
            ['input' => ['clockIn' => '09:00', 'clockOut' => '17:30', 'breakMinutes' => 30], 'expect' => ['Hours worked' => '8h 0m', 'Decimal hours' => 8, 'Regular hours' => 8, 'Overtime hours' => 0]],
            ['input' => ['clockIn' => '08:00', 'clockOut' => '18:00', 'breakMinutes' => 60], 'expect' => ['Hours worked' => '9h 0m', 'Decimal hours' => 9, 'Regular hours' => 8, 'Overtime hours' => 1]],
            ['input' => ['clockIn' => '22:00', 'clockOut' => '06:00', 'breakMinutes' => 0], 'expect' => ['Hours worked' => '8h 0m', 'Decimal hours' => 8, 'Overtime hours' => 0]],
        ],
    ],
];
