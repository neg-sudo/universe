<?php
/**
 * UniVerse — Date & Time
 * 10 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'age-calculator',
        'name' => 'Age Calculator',
        'category' => 'datetime',
        'description' => 'Work out age in years, months and days, total days lived and the countdown to the next birthday.',
        'icon' => 'cake',
        'featured' => true,
        'keywords' => ['age', 'birthday', 'date of birth', 'years old'],
        'inputs' => [
            ['name' => 'birthDate', 'label' => 'Date of birth', 'type' => 'date', 'default' => '1990-01-15', 'required' => true],
            ['name' => 'asOf', 'label' => 'As of date', 'type' => 'date', 'default' => '2026-11-11', 'required' => true],
        ],
        'compute' => <<<'JS'
            const DAY = 86400000;
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const birth = parse(v.birthDate);
            const asOf = parse(v.asOf);
            if (birth > asOf) throw new Error('The date of birth must be on or before the as-of date.');
            const by = birth.getUTCFullYear();
            const bm = birth.getUTCMonth() + 1;
            const bd = birth.getUTCDate();
            const ay = asOf.getUTCFullYear();
            const am = asOf.getUTCMonth() + 1;
            const ad = asOf.getUTCDate();
            let years = ay - by;
            let months = am - bm;
            let days = ad - bd;
            if (days < 0) {
                months -= 1;
                const previousMonth = new Date(Date.UTC(ay, am - 1, 0));
                days += previousMonth.getUTCDate();
            }
            if (months < 0) {
                years -= 1;
                months += 12;
            }
            let next = Date.UTC(ay, bm - 1, bd);
            if (next < asOf.getTime()) {
                next = Date.UTC(ay + 1, bm - 1, bd);
                if (next < asOf.getTime()) next = Date.UTC(ay + 2, bm - 1, bd);
            }
            return [
                { label: 'Years', value: years, decimals: 0 },
                { label: 'Months', value: months, decimals: 0 },
                { label: 'Days', value: days, decimals: 0 },
                { label: 'Total days lived', value: Math.round((asOf - birth) / DAY), decimals: 0 },
                { label: 'Next birthday in days', value: Math.round((next - asOf.getTime()) / DAY), decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['birthDate' => '1990-01-15', 'asOf' => '2026-11-11'], 'expect' => ['Years' => 36, 'Months' => 9, 'Days' => 27, 'Total days lived' => 13449, 'Next birthday in days' => 65]],
            ['input' => ['birthDate' => '1985-06-15', 'asOf' => '2024-09-20'], 'expect' => ['Years' => 39, 'Months' => 3, 'Days' => 5, 'Total days lived' => 14342]],
            ['input' => ['birthDate' => '1990-11-11', 'asOf' => '2026-11-11'], 'expect' => ['Years' => 36, 'Months' => 0, 'Days' => 0, 'Next birthday in days' => 0]],
        ],
    ],

    [
        'slug' => 'days-between-dates',
        'name' => 'Days Between Dates',
        'category' => 'datetime',
        'description' => 'Count the days between two dates, including business days, weeks and an inclusive total.',
        'icon' => 'calendar-range',
        'featured' => true,
        'keywords' => ['days between', 'date difference', 'business days', 'weeks'],
        'inputs' => [
            ['name' => 'startDate', 'label' => 'Start date', 'type' => 'date', 'default' => '2024-01-01', 'required' => true],
            ['name' => 'endDate', 'label' => 'End date', 'type' => 'date', 'default' => '2024-01-31', 'required' => true],
        ],
        'compute' => <<<'JS'
            const DAY = 86400000;
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const plural = (n, word) => `${n} ${word}${n === 1 ? '' : 's'}`;
            const start = parse(v.startDate);
            const end = parse(v.endDate);
            if (end < start) throw new Error('The end date must be on or after the start date.');
            const total = Math.round((end - start) / DAY);
            let business = 0;
            const cursor = new Date(start.getTime());
            cursor.setUTCDate(cursor.getUTCDate() + 1);
            while (cursor <= end) {
                const weekday = cursor.getUTCDay();
                if (weekday !== 0 && weekday !== 6) business += 1;
                cursor.setUTCDate(cursor.getUTCDate() + 1);
            }
            return [
                { label: 'Total days', value: total, decimals: 0 },
                { label: 'Weeks and days', value: `${plural(Math.floor(total / 7), 'week')}, ${plural(total % 7, 'day')}` },
                { label: 'Business days', value: business, decimals: 0 },
                { label: 'Inclusive days', value: total + 1, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['startDate' => '2024-01-01', 'endDate' => '2024-01-31'], 'expect' => ['Total days' => 30, 'Weeks and days' => '4 weeks, 2 days', 'Business days' => 22, 'Inclusive days' => 31]],
            ['input' => ['startDate' => '2024-03-01', 'endDate' => '2024-03-11'], 'expect' => ['Total days' => 10, 'Weeks and days' => '1 week, 3 days', 'Business days' => 6, 'Inclusive days' => 11]],
            ['input' => ['startDate' => '2024-12-20', 'endDate' => '2025-01-05'], 'expect' => ['Total days' => 16, 'Business days' => 10, 'Inclusive days' => 17]],
        ],
    ],

    [
        'slug' => 'date-add-subtract',
        'name' => 'Date Add/Subtract',
        'category' => 'datetime',
        'description' => 'Add or subtract days, weeks, months or years from a date and see the resulting weekday.',
        'icon' => 'calendar-plus',
        'featured' => false,
        'keywords' => ['date add', 'date subtract', 'days', 'months', 'years'],
        'inputs' => [
            ['name' => 'startDate', 'label' => 'Start date', 'type' => 'date', 'default' => '2024-01-01', 'required' => true],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'default' => 30, 'min' => 0, 'step' => 1, 'required' => true],
            [
                'name' => 'unit',
                'label' => 'Unit',
                'type' => 'select',
                'default' => 'days',
                'options' => [
                    ['value' => 'days', 'label' => 'Days'],
                    ['value' => 'weeks', 'label' => 'Weeks'],
                    ['value' => 'months', 'label' => 'Months'],
                    ['value' => 'years', 'label' => 'Years'],
                ],
            ],
            [
                'name' => 'direction',
                'label' => 'Direction',
                'type' => 'select',
                'default' => 'add',
                'options' => [
                    ['value' => 'add', 'label' => 'Add'],
                    ['value' => 'subtract', 'label' => 'Subtract'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const DAY = 86400000;
            const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const addMonths = (date, count) => {
                const year = date.getUTCFullYear();
                const month = date.getUTCMonth();
                const day = date.getUTCDate();
                const target = new Date(Date.UTC(year, month + count, 1));
                const lastDay = new Date(Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)).getUTCDate();
                target.setUTCDate(Math.min(day, lastDay));
                return target;
            };
            const amount = h.n(v.amount);
            if (!Number.isInteger(amount) || amount < 0) throw new Error('Enter a whole number of units (0 or more).');
            const start = parse(v.startDate);
            const sign = v.direction === 'subtract' ? -1 : 1;
            let result;
            if (v.unit === 'weeks') {
                result = new Date(start.getTime() + sign * amount * 7 * DAY);
            } else if (v.unit === 'months') {
                result = addMonths(start, sign * amount);
            } else if (v.unit === 'years') {
                result = addMonths(start, sign * amount * 12);
            } else {
                result = new Date(start.getTime() + sign * amount * DAY);
            }
            return [
                { label: 'Resulting date', value: result.toISOString().slice(0, 10) },
                { label: 'Weekday', value: WEEKDAYS[result.getUTCDay()] },
            ];
        JS,
        'tests' => [
            ['input' => ['startDate' => '2024-01-01', 'amount' => 30, 'unit' => 'days', 'direction' => 'add'], 'expect' => ['Resulting date' => '2024-01-31', 'Weekday' => 'Wednesday']],
            ['input' => ['startDate' => '2024-01-31', 'amount' => 1, 'unit' => 'months', 'direction' => 'add'], 'expect' => ['Resulting date' => '2024-02-29', 'Weekday' => 'Thursday']],
            ['input' => ['startDate' => '2024-03-15', 'amount' => 2, 'unit' => 'weeks', 'direction' => 'subtract'], 'expect' => ['Resulting date' => '2024-03-01', 'Weekday' => 'Friday']],
            ['input' => ['startDate' => '2023-02-28', 'amount' => 1, 'unit' => 'years', 'direction' => 'add'], 'expect' => ['Resulting date' => '2024-02-28', 'Weekday' => 'Wednesday']],
        ],
    ],

    [
        'slug' => 'week-number-calculator',
        'name' => 'Week Number Calculator',
        'category' => 'datetime',
        'description' => 'Find the ISO-8601 week number and ISO week-year for any calendar date.',
        'icon' => 'calendar',
        'featured' => false,
        'keywords' => ['week number', 'iso week', 'week of year', 'calendar'],
        'inputs' => [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'default' => '2024-01-01', 'required' => true],
        ],
        'compute' => <<<'JS'
            const DAY = 86400000;
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const d = parse(v.date);
            const dayNumber = d.getUTCDay() || 7;
            d.setUTCDate(d.getUTCDate() + 4 - dayNumber);
            const isoYear = d.getUTCFullYear();
            const yearStart = new Date(Date.UTC(isoYear, 0, 1));
            const week = Math.ceil((((d - yearStart) / DAY) + 1) / 7);
            return [
                { label: 'Week number', value: week, decimals: 0 },
                { label: 'ISO week-year', value: isoYear, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['date' => '2024-01-01'], 'expect' => ['Week number' => 1, 'ISO week-year' => 2024]],
            ['input' => ['date' => '2023-01-01'], 'expect' => ['Week number' => 52, 'ISO week-year' => 2022]],
            ['input' => ['date' => '2020-12-31'], 'expect' => ['Week number' => 53, 'ISO week-year' => 2020]],
            ['input' => ['date' => '2026-11-11'], 'expect' => ['Week number' => 46, 'ISO week-year' => 2026]],
        ],
    ],

    [
        'slug' => 'leap-year-checker',
        'name' => 'Leap Year Checker',
        'category' => 'datetime',
        'description' => 'Check whether a year is a leap year, see its length in days and the next leap year.',
        'icon' => 'calendar-check',
        'featured' => false,
        'keywords' => ['leap year', 'february 29', 'calendar', 'days in year'],
        'inputs' => [
            ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'default' => 2024, 'min' => 1, 'max' => 9999, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const year = h.n(v.year);
            if (!Number.isInteger(year) || year < 1 || year > 9999) throw new Error('Enter a whole year between 1 and 9999.');
            const isLeap = (y) => (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0;
            const leap = isLeap(year);
            let next = year + 1;
            while (!isLeap(next)) next += 1;
            return [
                { label: 'Result', value: leap ? 'Leap year' : 'Not a leap year' },
                { label: 'Days in year', value: leap ? 366 : 365, decimals: 0 },
                { label: 'Next leap year', value: next, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['year' => 2024], 'expect' => ['Result' => 'Leap year', 'Days in year' => 366, 'Next leap year' => 2028]],
            ['input' => ['year' => 1900], 'expect' => ['Result' => 'Not a leap year', 'Days in year' => 365, 'Next leap year' => 1904]],
            ['input' => ['year' => 2000], 'expect' => ['Result' => 'Leap year', 'Days in year' => 366, 'Next leap year' => 2004]],
        ],
    ],

    [
        'slug' => 'time-duration-calculator',
        'name' => 'Time Duration Calculator',
        'category' => 'datetime',
        'description' => 'Calculate the duration between two times, with an optional overnight span, in hours and minutes.',
        'icon' => 'timer',
        'featured' => false,
        'keywords' => ['time duration', 'hours', 'minutes', 'overnight', 'timesheet'],
        'inputs' => [
            ['name' => 'startTime', 'label' => 'Start time', 'type' => 'time', 'default' => '09:30', 'required' => true],
            ['name' => 'endTime', 'label' => 'End time', 'type' => 'time', 'default' => '17:15', 'required' => true],
            ['name' => 'overnight', 'label' => 'End time is on the next day', 'type' => 'checkbox', 'default' => false],
        ],
        'compute' => <<<'JS'
            const parseTime = (s) => {
                const m = String(s).trim().match(/^(\d{1,2}):(\d{2})$/);
                if (!m) throw new Error('Enter a valid time in HH:MM format.');
                const hours = Number(m[1]);
                const minutes = Number(m[2]);
                if (hours > 23 || minutes > 59) throw new Error('Enter a valid time in HH:MM format.');
                return hours * 60 + minutes;
            };
            const start = parseTime(v.startTime);
            let end = parseTime(v.endTime);
            const overnight = v.overnight === true || v.overnight === '1' || v.overnight === 'on';
            if (overnight && end <= start) end += 1440;
            if (!overnight && end < start) throw new Error('The end time must be after the start time, or tick the overnight box.');
            const total = end - start;
            const hours = Math.floor(total / 60);
            const minutes = total % 60;
            return [
                { label: 'Duration', value: `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}` },
                { label: 'Total minutes', value: total, decimals: 0 },
                { label: 'Total hours', value: total / 60, decimals: 2 },
            ];
        JS,
        'tests' => [
            ['input' => ['startTime' => '09:30', 'endTime' => '17:15', 'overnight' => false], 'expect' => ['Duration' => '07:45', 'Total minutes' => 465, 'Total hours' => 7.75]],
            ['input' => ['startTime' => '22:00', 'endTime' => '06:30', 'overnight' => true], 'expect' => ['Duration' => '08:30', 'Total minutes' => 510, 'Total hours' => 8.5]],
            ['input' => ['startTime' => '13:00', 'endTime' => '13:45', 'overnight' => false], 'expect' => ['Duration' => '00:45', 'Total minutes' => 45, 'Total hours' => 0.75]],
        ],
    ],

    [
        'slug' => 'business-days-calculator',
        'name' => 'Business Days Calculator',
        'category' => 'datetime',
        'description' => 'Add or subtract a number of business days (Monday to Friday) from a starting date.',
        'icon' => 'briefcase',
        'featured' => false,
        'keywords' => ['business days', 'working days', 'weekdays', 'deadline'],
        'inputs' => [
            ['name' => 'startDate', 'label' => 'Start date', 'type' => 'date', 'default' => '2024-01-01', 'required' => true],
            ['name' => 'businessDays', 'label' => 'Business days', 'type' => 'number', 'default' => 5, 'min' => 0, 'step' => 1, 'required' => true],
            [
                'name' => 'direction',
                'label' => 'Direction',
                'type' => 'select',
                'default' => 'add',
                'options' => [
                    ['value' => 'add', 'label' => 'Add business days'],
                    ['value' => 'subtract', 'label' => 'Subtract business days'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const count = h.n(v.businessDays);
            if (!Number.isInteger(count) || count < 0) throw new Error('Enter a whole number of business days (0 or more).');
            const sign = v.direction === 'subtract' ? -1 : 1;
            const d = parse(v.startDate);
            let moved = 0;
            while (moved < count) {
                d.setUTCDate(d.getUTCDate() + sign);
                const weekday = d.getUTCDay();
                if (weekday !== 0 && weekday !== 6) moved += 1;
            }
            return [
                { label: 'Resulting date', value: d.toISOString().slice(0, 10) },
                { label: 'Weekday', value: WEEKDAYS[d.getUTCDay()] },
            ];
        JS,
        'tests' => [
            ['input' => ['startDate' => '2024-01-01', 'businessDays' => 5, 'direction' => 'add'], 'expect' => ['Resulting date' => '2024-01-08', 'Weekday' => 'Monday']],
            ['input' => ['startDate' => '2024-01-01', 'businessDays' => 5, 'direction' => 'subtract'], 'expect' => ['Resulting date' => '2023-12-25', 'Weekday' => 'Monday']],
            ['input' => ['startDate' => '2024-01-05', 'businessDays' => 1, 'direction' => 'add'], 'expect' => ['Resulting date' => '2024-01-08', 'Weekday' => 'Monday']],
        ],
    ],

    [
        'slug' => 'countdown-to-date',
        'name' => 'Countdown to Date',
        'category' => 'datetime',
        'description' => 'Count the days, weeks and hours remaining until a target date, or see if it has passed.',
        'icon' => 'hourglass',
        'featured' => false,
        'keywords' => ['countdown', 'days remaining', 'until', 'event'],
        'inputs' => [
            ['name' => 'targetDate', 'label' => 'Target date', 'type' => 'date', 'default' => '2026-12-25', 'required' => true],
            ['name' => 'today', 'label' => 'Today (optional)', 'type' => 'date', 'hint' => 'Leave blank to use today’s date in UTC.'],
        ],
        'compute' => <<<'JS'
            const DAY = 86400000;
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const plural = (n, word) => `${n} ${word}${n === 1 ? '' : 's'}`;
            const todayText = String(v.today || '').trim() || new Date().toISOString().slice(0, 10);
            const today = parse(todayText);
            const target = parse(v.targetDate);
            const days = Math.round((target - today) / DAY);
            if (days < 0) {
                return { label: 'Days remaining', value: 'Date has passed', hint: 'Choose a target date on or after today.' };
            }
            return [
                { label: 'Days remaining', value: days, decimals: 0 },
                { label: 'Weeks and days', value: `${plural(Math.floor(days / 7), 'week')}, ${plural(days % 7, 'day')}` },
                { label: 'Hours remaining', value: days * 24, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['targetDate' => '2026-12-25', 'today' => '2026-11-11'], 'expect' => ['Days remaining' => 44, 'Weeks and days' => '6 weeks, 2 days', 'Hours remaining' => 1056]],
            ['input' => ['targetDate' => '2026-11-11', 'today' => '2026-11-11'], 'expect' => ['Days remaining' => 0, 'Weeks and days' => '0 weeks, 0 days', 'Hours remaining' => 0]],
            ['input' => ['targetDate' => '2026-01-01', 'today' => '2026-11-11'], 'expect' => ['Days remaining' => 'Date has passed']],
        ],
    ],

    [
        'slug' => 'day-of-week-finder',
        'name' => 'Day of Week Finder',
        'category' => 'datetime',
        'description' => 'Find the weekday, day of the year and ISO week number for any date.',
        'icon' => 'calendar-days',
        'featured' => false,
        'keywords' => ['day of week', 'weekday', 'day of year', 'iso week'],
        'inputs' => [
            ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'default' => '2024-02-29', 'required' => true],
        ],
        'compute' => <<<'JS'
            const DAY = 86400000;
            const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const parse = (s) => {
                const text = String(s).trim();
                const m = text.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!m) throw new Error('Enter a valid date in YYYY-MM-DD format.');
                const d = new Date(text + 'T00:00:00Z');
                if (Number.isNaN(d.getTime()) || d.getUTCFullYear() !== Number(m[1]) || d.getUTCMonth() + 1 !== Number(m[2]) || d.getUTCDate() !== Number(m[3])) {
                    throw new Error('Enter a valid date in YYYY-MM-DD format.');
                }
                return d;
            };
            const d = parse(v.date);
            const year = d.getUTCFullYear();
            const dayOfYear = Math.floor((d - Date.UTC(year, 0, 1)) / DAY) + 1;
            const isoDate = new Date(d.getTime());
            const dayNumber = isoDate.getUTCDay() || 7;
            isoDate.setUTCDate(isoDate.getUTCDate() + 4 - dayNumber);
            const isoYear = isoDate.getUTCFullYear();
            const yearStart = new Date(Date.UTC(isoYear, 0, 1));
            const week = Math.ceil((((isoDate - yearStart) / DAY) + 1) / 7);
            return [
                { label: 'Weekday', value: WEEKDAYS[d.getUTCDay()] },
                { label: 'Day of year', value: dayOfYear, decimals: 0 },
                { label: 'ISO week number', value: week, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['date' => '2000-01-01'], 'expect' => ['Weekday' => 'Saturday', 'Day of year' => 1, 'ISO week number' => 52]],
            ['input' => ['date' => '2024-02-29'], 'expect' => ['Weekday' => 'Thursday', 'Day of year' => 60, 'ISO week number' => 9]],
            ['input' => ['date' => '2026-11-11'], 'expect' => ['Weekday' => 'Wednesday', 'Day of year' => 315, 'ISO week number' => 46]],
        ],
    ],

    [
        'slug' => 'date-format-converter',
        'name' => 'Date Format Converter',
        'category' => 'datetime',
        'description' => 'Convert an ISO 8601 date or date-time into a readable date, Unix timestamp and weekday.',
        'icon' => 'calendar-clock',
        'featured' => false,
        'keywords' => ['date format', 'iso 8601', 'unix timestamp', 'convert date'],
        'inputs' => [
            ['name' => 'dateString', 'label' => 'Date or date-time', 'type' => 'text', 'default' => '2026-11-11T14:30:00Z', 'required' => true, 'hint' => 'Examples: 2026-11-11 or 2026-11-11T14:30:00Z'],
        ],
        'compute' => <<<'JS'
            const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const WEEKDAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const raw = String(v.dateString).trim();
            if (!raw) throw new Error('Enter a date or date-time.');
            let text = raw.replace(' ', 'T');
            if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
                text += 'T00:00:00Z';
            } else if (!/(?:Z|[+-]\d{2}:?\d{2})$/i.test(text)) {
                text += 'Z';
            }
            const d = new Date(text);
            if (Number.isNaN(d.getTime())) throw new Error('Enter a valid ISO 8601 date or date-time.');
            return [
                { label: 'ISO date', value: d.toISOString().slice(0, 10) },
                { label: 'Unix timestamp', value: Math.floor(d.getTime() / 1000), unit: 's', decimals: 0 },
                { label: 'Human-readable date', value: `${d.getUTCDate()} ${MONTHS[d.getUTCMonth()]} ${d.getUTCFullYear()}` },
                { label: 'Day of week', value: WEEKDAYS[d.getUTCDay()] },
            ];
        JS,
        'tests' => [
            ['input' => ['dateString' => '2026-11-11'], 'expect' => ['ISO date' => '2026-11-11', 'Unix timestamp' => 1794355200, 'Human-readable date' => '11 November 2026', 'Day of week' => 'Wednesday']],
            ['input' => ['dateString' => '2024-02-29T12:00:00Z'], 'expect' => ['ISO date' => '2024-02-29', 'Unix timestamp' => 1709208000, 'Human-readable date' => '29 February 2024', 'Day of week' => 'Thursday']],
            ['input' => ['dateString' => '2026-11-11T14:30:00Z'], 'expect' => ['Unix timestamp' => 1794407400, 'Human-readable date' => '11 November 2026']],
        ],
    ],
];
