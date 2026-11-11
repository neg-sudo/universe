<?php
/**
 * UtilityWorld — Conversion
 * 14 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'length-converter',
        'name' => 'Length Converter',
        'category' => 'conversion',
        'description' => 'Convert between metres, kilometres, centimetres, millimetres, miles, yards, feet and inches.',
        'icon' => 'ruler',
        'featured' => true,
        'keywords' => ['length', 'distance', 'metres', 'feet', 'inches', 'miles'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'm',
                'options' => [
                    ['value' => 'm', 'label' => 'Metres (m)'],
                    ['value' => 'km', 'label' => 'Kilometres (km)'],
                    ['value' => 'cm', 'label' => 'Centimetres (cm)'],
                    ['value' => 'mm', 'label' => 'Millimetres (mm)'],
                    ['value' => 'mi', 'label' => 'Miles (mi)'],
                    ['value' => 'yd', 'label' => 'Yards (yd)'],
                    ['value' => 'ft', 'label' => 'Feet (ft)'],
                    ['value' => 'in', 'label' => 'Inches (in)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'km',
                'options' => [
                    ['value' => 'm', 'label' => 'Metres (m)'],
                    ['value' => 'km', 'label' => 'Kilometres (km)'],
                    ['value' => 'cm', 'label' => 'Centimetres (cm)'],
                    ['value' => 'mm', 'label' => 'Millimetres (mm)'],
                    ['value' => 'mi', 'label' => 'Miles (mi)'],
                    ['value' => 'yd', 'label' => 'Yards (yd)'],
                    ['value' => 'ft', 'label' => 'Feet (ft)'],
                    ['value' => 'in', 'label' => 'Inches (in)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { m: 1, km: 1000, cm: 0.01, mm: 0.001, mi: 1609.344, yd: 0.9144, ft: 0.3048, in: 0.0254 };
            const labels = { m: 'm', km: 'km', cm: 'cm', mm: 'mm', mi: 'mi', yd: 'yd', ft: 'ft', in: 'in' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'km', 'to' => 'm'], 'expect' => ['Result' => 1000, 'Conversion factor' => 1000]],
            ['input' => ['value' => 100, 'from' => 'cm', 'to' => 'm'], 'expect' => ['Result' => 1]],
            ['input' => ['value' => 1, 'from' => 'mi', 'to' => 'km'], 'expect' => ['Result' => 1.609344]],
            ['input' => ['value' => 5, 'from' => 'm', 'to' => 'm'], 'expect' => ['Result' => 5, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'weight-converter',
        'name' => 'Weight Converter',
        'category' => 'conversion',
        'description' => 'Convert between kilograms, grams, milligrams, tonnes, pounds, ounces and stone.',
        'icon' => 'weight',
        'keywords' => ['weight', 'mass', 'kilograms', 'pounds', 'ounces', 'stone'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'kg',
                'options' => [
                    ['value' => 'kg', 'label' => 'Kilograms (kg)'],
                    ['value' => 'g', 'label' => 'Grams (g)'],
                    ['value' => 'mg', 'label' => 'Milligrams (mg)'],
                    ['value' => 't', 'label' => 'Metric tonnes (t)'],
                    ['value' => 'lb', 'label' => 'Pounds (lb)'],
                    ['value' => 'oz', 'label' => 'Ounces (oz)'],
                    ['value' => 'st', 'label' => 'Stone (st)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'lb',
                'options' => [
                    ['value' => 'kg', 'label' => 'Kilograms (kg)'],
                    ['value' => 'g', 'label' => 'Grams (g)'],
                    ['value' => 'mg', 'label' => 'Milligrams (mg)'],
                    ['value' => 't', 'label' => 'Metric tonnes (t)'],
                    ['value' => 'lb', 'label' => 'Pounds (lb)'],
                    ['value' => 'oz', 'label' => 'Ounces (oz)'],
                    ['value' => 'st', 'label' => 'Stone (st)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const LB = 0.45359237;
            const table = { kg: 1, g: 0.001, mg: 0.000001, t: 1000, lb: LB, oz: LB / 16, st: LB * 14 };
            const labels = { kg: 'kg', g: 'g', mg: 'mg', t: 't', lb: 'lb', oz: 'oz', st: 'st' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'lb', 'to' => 'kg'], 'expect' => ['Result' => 0.45359237, 'Conversion factor' => 0.45359237]],
            ['input' => ['value' => 1000, 'from' => 'g', 'to' => 'kg'], 'expect' => ['Result' => 1]],
            ['input' => ['value' => 1, 'from' => 'st', 'to' => 'lb'], 'expect' => ['Result' => 14]],
            ['input' => ['value' => 5, 'from' => 'kg', 'to' => 'kg'], 'expect' => ['Result' => 5, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'temperature-converter',
        'name' => 'Temperature Converter',
        'category' => 'conversion',
        'description' => 'Convert temperatures between Celsius, Fahrenheit and Kelvin with the formula shown.',
        'icon' => 'thermometer',
        'featured' => true,
        'keywords' => ['temperature', 'celsius', 'fahrenheit', 'kelvin', 'degrees'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 100, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'c',
                'options' => [
                    ['value' => 'c', 'label' => 'Celsius (°C)'],
                    ['value' => 'f', 'label' => 'Fahrenheit (°F)'],
                    ['value' => 'k', 'label' => 'Kelvin (K)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'f',
                'options' => [
                    ['value' => 'c', 'label' => 'Celsius (°C)'],
                    ['value' => 'f', 'label' => 'Fahrenheit (°F)'],
                    ['value' => 'k', 'label' => 'Kelvin (K)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const names = { c: '°C', f: '°F', k: 'K' };
            const formulas = {
                'c-f': '°F = °C × 9/5 + 32',
                'f-c': '°C = (°F − 32) × 5/9',
                'c-k': 'K = °C + 273.15',
                'k-c': '°C = K − 273.15',
                'f-k': 'K = (°F − 32) × 5/9 + 273.15',
                'k-f': '°F = (K − 273.15) × 9/5 + 32',
            };
            if (!(v.from in names) || !(v.to in names)) throw new Error('Choose a valid temperature unit.');
            const value = h.n(v.value);
            const toCelsius = (x, unit) => unit === 'c' ? x : unit === 'f' ? (x - 32) * 5 / 9 : x - 273.15;
            const fromCelsius = (x, unit) => unit === 'c' ? x : unit === 'f' ? x * 9 / 5 + 32 : x + 273.15;
            const celsius = toCelsius(value, v.from);
            if (celsius < -273.15) throw new Error('Temperature cannot be below absolute zero.');
            const formula = v.from === v.to ? `The value stays in ${names[v.to]}.` : formulas[`${v.from}-${v.to}`];
            return [
                { label: 'Result', value: fromCelsius(celsius, v.to), unit: names[v.to] },
                { label: 'Formula', value: formula },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 100, 'from' => 'c', 'to' => 'f'], 'expect' => ['Result' => 212]],
            ['input' => ['value' => 32, 'from' => 'f', 'to' => 'c'], 'expect' => ['Result' => 0]],
            ['input' => ['value' => 0, 'from' => 'c', 'to' => 'k'], 'expect' => ['Result' => 273.15]],
            ['input' => ['value' => 300, 'from' => 'k', 'to' => 'k'], 'expect' => ['Result' => 300]],
        ],
    ],

    [
        'slug' => 'area-converter',
        'name' => 'Area Converter',
        'category' => 'conversion',
        'description' => 'Convert between square metres, kilometres, centimetres, hectares, acres, feet, yards and miles.',
        'icon' => 'square',
        'keywords' => ['area', 'square metres', 'hectares', 'acres', 'square feet'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'ha',
                'options' => [
                    ['value' => 'm2', 'label' => 'Square metres (m²)'],
                    ['value' => 'km2', 'label' => 'Square kilometres (km²)'],
                    ['value' => 'cm2', 'label' => 'Square centimetres (cm²)'],
                    ['value' => 'ha', 'label' => 'Hectares (ha)'],
                    ['value' => 'acre', 'label' => 'Acres'],
                    ['value' => 'ft2', 'label' => 'Square feet (ft²)'],
                    ['value' => 'yd2', 'label' => 'Square yards (yd²)'],
                    ['value' => 'mi2', 'label' => 'Square miles (mi²)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'm2',
                'options' => [
                    ['value' => 'm2', 'label' => 'Square metres (m²)'],
                    ['value' => 'km2', 'label' => 'Square kilometres (km²)'],
                    ['value' => 'cm2', 'label' => 'Square centimetres (cm²)'],
                    ['value' => 'ha', 'label' => 'Hectares (ha)'],
                    ['value' => 'acre', 'label' => 'Acres'],
                    ['value' => 'ft2', 'label' => 'Square feet (ft²)'],
                    ['value' => 'yd2', 'label' => 'Square yards (yd²)'],
                    ['value' => 'mi2', 'label' => 'Square miles (mi²)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { m2: 1, km2: 1000000, cm2: 0.0001, ha: 10000, acre: 4046.8564224, ft2: 0.09290304, yd2: 0.83612736, mi2: 2589988.110336 };
            const labels = { m2: 'm²', km2: 'km²', cm2: 'cm²', ha: 'ha', acre: 'acre', ft2: 'ft²', yd2: 'yd²', mi2: 'mi²' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'ha', 'to' => 'm2'], 'expect' => ['Result' => 10000, 'Conversion factor' => 10000]],
            ['input' => ['value' => 1, 'from' => 'acre', 'to' => 'm2'], 'expect' => ['Result' => 4046.8564224]],
            ['input' => ['value' => 1, 'from' => 'yd2', 'to' => 'ft2'], 'expect' => ['Result' => 9]],
            ['input' => ['value' => 5, 'from' => 'm2', 'to' => 'm2'], 'expect' => ['Result' => 5, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'volume-converter',
        'name' => 'Volume Converter',
        'category' => 'conversion',
        'description' => 'Convert between litres, millilitres, cubic metres, US gallons, quarts, pints, cups and fluid ounces.',
        'icon' => 'flask-conical',
        'keywords' => ['volume', 'litres', 'gallons', 'cups', 'pints', 'fluid ounces'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'gal',
                'options' => [
                    ['value' => 'l', 'label' => 'Litres (L)'],
                    ['value' => 'ml', 'label' => 'Millilitres (mL)'],
                    ['value' => 'm3', 'label' => 'Cubic metres (m³)'],
                    ['value' => 'cm3', 'label' => 'Cubic centimetres (cm³)'],
                    ['value' => 'gal', 'label' => 'US gallons'],
                    ['value' => 'qt', 'label' => 'US quarts'],
                    ['value' => 'pt', 'label' => 'US pints'],
                    ['value' => 'cup', 'label' => 'US cups'],
                    ['value' => 'floz', 'label' => 'US fluid ounces'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'l',
                'options' => [
                    ['value' => 'l', 'label' => 'Litres (L)'],
                    ['value' => 'ml', 'label' => 'Millilitres (mL)'],
                    ['value' => 'm3', 'label' => 'Cubic metres (m³)'],
                    ['value' => 'cm3', 'label' => 'Cubic centimetres (cm³)'],
                    ['value' => 'gal', 'label' => 'US gallons'],
                    ['value' => 'qt', 'label' => 'US quarts'],
                    ['value' => 'pt', 'label' => 'US pints'],
                    ['value' => 'cup', 'label' => 'US cups'],
                    ['value' => 'floz', 'label' => 'US fluid ounces'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const GAL = 3.785411784;
            const table = { l: 1, ml: 0.001, m3: 1000, cm3: 0.001, gal: GAL, qt: GAL / 4, pt: GAL / 8, cup: 0.2365882365, floz: 0.0295735295625 };
            const labels = { l: 'L', ml: 'mL', m3: 'm³', cm3: 'cm³', gal: 'US gal', qt: 'US qt', pt: 'US pt', cup: 'US cup', floz: 'US fl oz' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'gal', 'to' => 'l'], 'expect' => ['Result' => 3.785411784, 'Conversion factor' => 3.785411784]],
            ['input' => ['value' => 4, 'from' => 'qt', 'to' => 'gal'], 'expect' => ['Result' => 1]],
            ['input' => ['value' => 1, 'from' => 'cup', 'to' => 'floz'], 'expect' => ['Result' => 8]],
            ['input' => ['value' => 2, 'from' => 'l', 'to' => 'l'], 'expect' => ['Result' => 2, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'speed-converter',
        'name' => 'Speed Converter',
        'category' => 'conversion',
        'description' => 'Convert speeds between metres per second, kilometres per hour, miles per hour, knots and feet per second.',
        'icon' => 'gauge',
        'keywords' => ['speed', 'velocity', 'km/h', 'mph', 'knots'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 100, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'kmh',
                'options' => [
                    ['value' => 'mps', 'label' => 'Metres per second (m/s)'],
                    ['value' => 'kmh', 'label' => 'Kilometres per hour (km/h)'],
                    ['value' => 'mph', 'label' => 'Miles per hour (mph)'],
                    ['value' => 'knot', 'label' => 'Knots'],
                    ['value' => 'fps', 'label' => 'Feet per second (ft/s)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'mph',
                'options' => [
                    ['value' => 'mps', 'label' => 'Metres per second (m/s)'],
                    ['value' => 'kmh', 'label' => 'Kilometres per hour (km/h)'],
                    ['value' => 'mph', 'label' => 'Miles per hour (mph)'],
                    ['value' => 'knot', 'label' => 'Knots'],
                    ['value' => 'fps', 'label' => 'Feet per second (ft/s)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { mps: 1, kmh: 1 / 3.6, mph: 0.44704, knot: 0.514444444, fps: 0.3048 };
            const labels = { mps: 'm/s', kmh: 'km/h', mph: 'mph', knot: 'knot', fps: 'ft/s' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 3.6, 'from' => 'kmh', 'to' => 'mps'], 'expect' => ['Result' => 1, 'Conversion factor' => 0.2777777777777778]],
            ['input' => ['value' => 1, 'from' => 'mph', 'to' => 'kmh'], 'expect' => ['Result' => 1.609344]],
            ['input' => ['value' => 1, 'from' => 'knot', 'to' => 'kmh'], 'expect' => ['Result' => 1.852]],
            ['input' => ['value' => 10, 'from' => 'mps', 'to' => 'mps'], 'expect' => ['Result' => 10, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'time-unit-converter',
        'name' => 'Time Unit Converter',
        'category' => 'conversion',
        'description' => 'Convert between nanoseconds, microseconds, milliseconds, seconds, minutes, hours, days and weeks.',
        'icon' => 'clock',
        'keywords' => ['time', 'seconds', 'minutes', 'hours', 'days', 'weeks'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'h',
                'options' => [
                    ['value' => 'ns', 'label' => 'Nanoseconds (ns)'],
                    ['value' => 'us', 'label' => 'Microseconds (µs)'],
                    ['value' => 'ms', 'label' => 'Milliseconds (ms)'],
                    ['value' => 's', 'label' => 'Seconds (s)'],
                    ['value' => 'min', 'label' => 'Minutes (min)'],
                    ['value' => 'h', 'label' => 'Hours (h)'],
                    ['value' => 'day', 'label' => 'Days'],
                    ['value' => 'week', 'label' => 'Weeks'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'min',
                'options' => [
                    ['value' => 'ns', 'label' => 'Nanoseconds (ns)'],
                    ['value' => 'us', 'label' => 'Microseconds (µs)'],
                    ['value' => 'ms', 'label' => 'Milliseconds (ms)'],
                    ['value' => 's', 'label' => 'Seconds (s)'],
                    ['value' => 'min', 'label' => 'Minutes (min)'],
                    ['value' => 'h', 'label' => 'Hours (h)'],
                    ['value' => 'day', 'label' => 'Days'],
                    ['value' => 'week', 'label' => 'Weeks'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { ns: 0.000000001, us: 0.000001, ms: 0.001, s: 1, min: 60, h: 3600, day: 86400, week: 604800 };
            const labels = { ns: 'ns', us: 'µs', ms: 'ms', s: 's', min: 'min', h: 'h', day: 'day', week: 'week' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'In seconds', value: value * table[v.from], unit: 's' },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'h', 'to' => 'min'], 'expect' => ['Result' => 60, 'In seconds' => 3600, 'Conversion factor' => 60]],
            ['input' => ['value' => 1, 'from' => 'day', 'to' => 'h'], 'expect' => ['Result' => 24]],
            ['input' => ['value' => 500, 'from' => 'ms', 'to' => 's'], 'expect' => ['Result' => 0.5]],
            ['input' => ['value' => 2, 'from' => 'h', 'to' => 'h'], 'expect' => ['Result' => 2, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'data-storage-converter',
        'name' => 'Data Storage Converter',
        'category' => 'conversion',
        'description' => 'Convert data sizes between bits, bytes, kilobytes, megabytes, gigabytes and terabytes (1024-based).',
        'icon' => 'hard-drive',
        'keywords' => ['data storage', 'bytes', 'kilobytes', 'megabytes', 'gigabytes', 'binary'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'mb',
                'options' => [
                    ['value' => 'bit', 'label' => 'Bits'],
                    ['value' => 'byte', 'label' => 'Bytes'],
                    ['value' => 'kb', 'label' => 'Kilobytes (KB)'],
                    ['value' => 'mb', 'label' => 'Megabytes (MB)'],
                    ['value' => 'gb', 'label' => 'Gigabytes (GB)'],
                    ['value' => 'tb', 'label' => 'Terabytes (TB)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'kb',
                'options' => [
                    ['value' => 'bit', 'label' => 'Bits'],
                    ['value' => 'byte', 'label' => 'Bytes'],
                    ['value' => 'kb', 'label' => 'Kilobytes (KB)'],
                    ['value' => 'mb', 'label' => 'Megabytes (MB)'],
                    ['value' => 'gb', 'label' => 'Gigabytes (GB)'],
                    ['value' => 'tb', 'label' => 'Terabytes (TB)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { bit: 0.125, byte: 1, kb: 1024, mb: 1024 ** 2, gb: 1024 ** 3, tb: 1024 ** 4 };
            const labels = { bit: 'bit', byte: 'byte', kb: 'KB', mb: 'MB', gb: 'GB', tb: 'TB' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Binary 1024-based units: 1 KB = 1024 bytes (1 KiB).' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'kb', 'to' => 'byte'], 'expect' => ['Result' => 1024, 'Conversion factor' => 1024]],
            ['input' => ['value' => 1, 'from' => 'mb', 'to' => 'kb'], 'expect' => ['Result' => 1024]],
            ['input' => ['value' => 8, 'from' => 'bit', 'to' => 'byte'], 'expect' => ['Result' => 1]],
            ['input' => ['value' => 4, 'from' => 'gb', 'to' => 'gb'], 'expect' => ['Result' => 4, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'pressure-converter',
        'name' => 'Pressure Converter',
        'category' => 'conversion',
        'description' => 'Convert pressure between pascals, kilopascals, bar, atmospheres, psi and millimetres of mercury.',
        'icon' => 'gauge',
        'keywords' => ['pressure', 'pascals', 'bar', 'atmosphere', 'psi', 'mmHg'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'bar',
                'options' => [
                    ['value' => 'pa', 'label' => 'Pascals (Pa)'],
                    ['value' => 'kpa', 'label' => 'Kilopascals (kPa)'],
                    ['value' => 'bar', 'label' => 'Bar'],
                    ['value' => 'atm', 'label' => 'Atmospheres (atm)'],
                    ['value' => 'psi', 'label' => 'Pounds per square inch (psi)'],
                    ['value' => 'mmhg', 'label' => 'Millimetres of mercury (mmHg)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'psi',
                'options' => [
                    ['value' => 'pa', 'label' => 'Pascals (Pa)'],
                    ['value' => 'kpa', 'label' => 'Kilopascals (kPa)'],
                    ['value' => 'bar', 'label' => 'Bar'],
                    ['value' => 'atm', 'label' => 'Atmospheres (atm)'],
                    ['value' => 'psi', 'label' => 'Pounds per square inch (psi)'],
                    ['value' => 'mmhg', 'label' => 'Millimetres of mercury (mmHg)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { pa: 1, kpa: 1000, bar: 100000, atm: 101325, psi: 6894.757293168, mmhg: 133.322387415 };
            const labels = { pa: 'Pa', kpa: 'kPa', bar: 'bar', atm: 'atm', psi: 'psi', mmhg: 'mmHg' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'bar', 'to' => 'pa'], 'expect' => ['Result' => 100000, 'Conversion factor' => 100000]],
            ['input' => ['value' => 1, 'from' => 'atm', 'to' => 'kpa'], 'expect' => ['Result' => 101.325]],
            ['input' => ['value' => 1, 'from' => 'psi', 'to' => 'pa'], 'expect' => ['Result' => 6894.757293168]],
            ['input' => ['value' => 2, 'from' => 'bar', 'to' => 'bar'], 'expect' => ['Result' => 2, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'energy-converter',
        'name' => 'Energy Converter',
        'category' => 'conversion',
        'description' => 'Convert energy between joules, kilojoules, calories, kilocalories, watt-hours, kilowatt-hours and BTU.',
        'icon' => 'zap',
        'keywords' => ['energy', 'joules', 'calories', 'kilocalories', 'kwh', 'btu'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'kcal',
                'options' => [
                    ['value' => 'j', 'label' => 'Joules (J)'],
                    ['value' => 'kj', 'label' => 'Kilojoules (kJ)'],
                    ['value' => 'cal', 'label' => 'Calories (cal)'],
                    ['value' => 'kcal', 'label' => 'Kilocalories (kcal)'],
                    ['value' => 'wh', 'label' => 'Watt-hours (Wh)'],
                    ['value' => 'kwh', 'label' => 'Kilowatt-hours (kWh)'],
                    ['value' => 'btu', 'label' => 'British thermal units (BTU)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'kj',
                'options' => [
                    ['value' => 'j', 'label' => 'Joules (J)'],
                    ['value' => 'kj', 'label' => 'Kilojoules (kJ)'],
                    ['value' => 'cal', 'label' => 'Calories (cal)'],
                    ['value' => 'kcal', 'label' => 'Kilocalories (kcal)'],
                    ['value' => 'wh', 'label' => 'Watt-hours (Wh)'],
                    ['value' => 'kwh', 'label' => 'Kilowatt-hours (kWh)'],
                    ['value' => 'btu', 'label' => 'British thermal units (BTU)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { j: 1, kj: 1000, cal: 4.184, kcal: 4184, wh: 3600, kwh: 3600000, btu: 1055.05585262 };
            const labels = { j: 'J', kj: 'kJ', cal: 'cal', kcal: 'kcal', wh: 'Wh', kwh: 'kWh', btu: 'BTU' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'kcal', 'to' => 'cal'], 'expect' => ['Result' => 1000, 'Conversion factor' => 1000]],
            ['input' => ['value' => 1, 'from' => 'kwh', 'to' => 'j'], 'expect' => ['Result' => 3600000]],
            ['input' => ['value' => 1, 'from' => 'btu', 'to' => 'j'], 'expect' => ['Result' => 1055.05585262]],
            ['input' => ['value' => 5, 'from' => 'kj', 'to' => 'kj'], 'expect' => ['Result' => 5, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'power-converter',
        'name' => 'Power Converter',
        'category' => 'conversion',
        'description' => 'Convert power between watts, kilowatts, megawatts, mechanical horsepower and BTU per hour.',
        'icon' => 'power',
        'keywords' => ['power', 'watts', 'kilowatts', 'horsepower', 'btu per hour'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'hp',
                'options' => [
                    ['value' => 'w', 'label' => 'Watts (W)'],
                    ['value' => 'kw', 'label' => 'Kilowatts (kW)'],
                    ['value' => 'mw', 'label' => 'Megawatts (MW)'],
                    ['value' => 'hp', 'label' => 'Mechanical horsepower (hp)'],
                    ['value' => 'btuh', 'label' => 'BTU per hour (BTU/h)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'kw',
                'options' => [
                    ['value' => 'w', 'label' => 'Watts (W)'],
                    ['value' => 'kw', 'label' => 'Kilowatts (kW)'],
                    ['value' => 'mw', 'label' => 'Megawatts (MW)'],
                    ['value' => 'hp', 'label' => 'Mechanical horsepower (hp)'],
                    ['value' => 'btuh', 'label' => 'BTU per hour (BTU/h)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { w: 1, kw: 1000, mw: 1000000, hp: 745.6998715823, btuh: 0.2930710702 };
            const labels = { w: 'W', kw: 'kW', mw: 'MW', hp: 'hp', btuh: 'BTU/h' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Multiply the value by this factor.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'kw', 'to' => 'w'], 'expect' => ['Result' => 1000, 'Conversion factor' => 1000]],
            ['input' => ['value' => 1, 'from' => 'hp', 'to' => 'w'], 'expect' => ['Result' => 745.6998715823]],
            ['input' => ['value' => 1, 'from' => 'mw', 'to' => 'kw'], 'expect' => ['Result' => 1000]],
            ['input' => ['value' => 3, 'from' => 'w', 'to' => 'w'], 'expect' => ['Result' => 3, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'angle-converter',
        'name' => 'Angle Converter',
        'category' => 'conversion',
        'description' => 'Convert angles between degrees, radians, gradians and turns, with degrees and radians shown.',
        'icon' => 'compass',
        'keywords' => ['angle', 'degrees', 'radians', 'gradians', 'turns'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 180, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'deg',
                'options' => [
                    ['value' => 'deg', 'label' => 'Degrees (°)'],
                    ['value' => 'rad', 'label' => 'Radians (rad)'],
                    ['value' => 'grad', 'label' => 'Gradians (grad)'],
                    ['value' => 'turn', 'label' => 'Turns'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'rad',
                'options' => [
                    ['value' => 'deg', 'label' => 'Degrees (°)'],
                    ['value' => 'rad', 'label' => 'Radians (rad)'],
                    ['value' => 'grad', 'label' => 'Gradians (grad)'],
                    ['value' => 'turn', 'label' => 'Turns'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { deg: 1, rad: 180 / Math.PI, grad: 0.9, turn: 360 };
            const labels = { deg: '°', rad: 'rad', grad: 'grad', turn: 'turn' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const degrees = value * table[v.from];
            return [
                { label: 'Result', value: degrees / table[v.to], unit: labels[v.to] },
                { label: 'Degrees', value: degrees, unit: '°' },
                { label: 'Radians', value: degrees * Math.PI / 180, unit: 'rad' },
                { label: 'Conversion factor', value: table[v.from] / table[v.to] },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 180, 'from' => 'deg', 'to' => 'rad'], 'expect' => ['Result' => 3.141592653589793, 'Degrees' => 180, 'Radians' => 3.141592653589793]],
            ['input' => ['value' => 1, 'from' => 'turn', 'to' => 'deg'], 'expect' => ['Result' => 360, 'Degrees' => 360]],
            ['input' => ['value' => 100, 'from' => 'grad', 'to' => 'deg'], 'expect' => ['Result' => 90]],
            ['input' => ['value' => 1, 'from' => 'rad', 'to' => 'rad'], 'expect' => ['Result' => 1, 'Conversion factor' => 1]],
        ],
    ],

    [
        'slug' => 'fuel-consumption-converter',
        'name' => 'Fuel Consumption Converter',
        'category' => 'conversion',
        'description' => 'Convert fuel consumption between L/100 km, km/L, US mpg and UK mpg with correct inversion.',
        'icon' => 'fuel',
        'keywords' => ['fuel consumption', 'mpg', 'l/100 km', 'km/l', 'efficiency'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'l100km',
                'options' => [
                    ['value' => 'l100km', 'label' => 'Litres per 100 km (L/100 km)'],
                    ['value' => 'kml', 'label' => 'Kilometres per litre (km/L)'],
                    ['value' => 'mpgus', 'label' => 'US miles per gallon (mpg)'],
                    ['value' => 'mpguk', 'label' => 'UK miles per gallon (mpg)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'mpgus',
                'options' => [
                    ['value' => 'l100km', 'label' => 'Litres per 100 km (L/100 km)'],
                    ['value' => 'kml', 'label' => 'Kilometres per litre (km/L)'],
                    ['value' => 'mpgus', 'label' => 'US miles per gallon (mpg)'],
                    ['value' => 'mpguk', 'label' => 'UK miles per gallon (mpg)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const MI_TO_KM = 1.609344;
            const US_GAL = 3.785411784;
            const UK_GAL = 4.54609;
            const labels = { l100km: 'L/100 km', kml: 'km/L', mpgus: 'US mpg', mpguk: 'UK mpg' };
            if (!(v.from in labels) || !(v.to in labels)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            if (value <= 0) throw new Error('Fuel consumption must be greater than zero.');
            const toKml = (x, unit) => {
                if (unit === 'kml') return x;
                if (unit === 'l100km') return 100 / x;
                if (unit === 'mpgus') return x * MI_TO_KM / US_GAL;
                return x * MI_TO_KM / UK_GAL;
            };
            const fromKml = (x, unit) => {
                if (unit === 'kml') return x;
                if (unit === 'l100km') return 100 / x;
                if (unit === 'mpgus') return x * US_GAL / MI_TO_KM;
                return x * UK_GAL / MI_TO_KM;
            };
            const kml = toKml(value, v.from);
            return [
                { label: 'Result', value: fromKml(kml, v.to), unit: labels[v.to] },
                { label: 'Kilometres per litre', value: kml, unit: 'km/L' },
                { label: 'Litres per 100 km', value: 100 / kml, unit: 'L/100 km' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 5, 'from' => 'l100km', 'to' => 'kml'], 'expect' => ['Result' => 20, 'Kilometres per litre' => 20, 'Litres per 100 km' => 5]],
            ['input' => ['value' => 5, 'from' => 'l100km', 'to' => 'mpgus'], 'expect' => ['Result' => 47.04291666666666]],
            ['input' => ['value' => 30, 'from' => 'mpgus', 'to' => 'l100km'], 'expect' => ['Result' => 7.840486111111111]],
            ['input' => ['value' => 8, 'from' => 'l100km', 'to' => 'l100km'], 'expect' => ['Result' => 8, 'Litres per 100 km' => 8]],
        ],
    ],

    [
        'slug' => 'cooking-measurement-converter',
        'name' => 'Cooking Measurement Converter',
        'category' => 'conversion',
        'description' => 'Convert cooking measures between cups, tablespoons, teaspoons, millilitres, fluid ounces and grams.',
        'icon' => 'utensils',
        'keywords' => ['cooking', 'cups', 'tablespoons', 'teaspoons', 'millilitres', 'grams'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'from',
                'label' => 'From',
                'type' => 'select',
                'default' => 'cup',
                'options' => [
                    ['value' => 'cup', 'label' => 'Cups'],
                    ['value' => 'tbsp', 'label' => 'Tablespoons'],
                    ['value' => 'tsp', 'label' => 'Teaspoons'],
                    ['value' => 'ml', 'label' => 'Millilitres (mL)'],
                    ['value' => 'floz', 'label' => 'Fluid ounces (US)'],
                    ['value' => 'g', 'label' => 'Grams (water)'],
                ],
            ],
            [
                'name' => 'to',
                'label' => 'To',
                'type' => 'select',
                'default' => 'ml',
                'options' => [
                    ['value' => 'cup', 'label' => 'Cups'],
                    ['value' => 'tbsp', 'label' => 'Tablespoons'],
                    ['value' => 'tsp', 'label' => 'Teaspoons'],
                    ['value' => 'ml', 'label' => 'Millilitres (mL)'],
                    ['value' => 'floz', 'label' => 'Fluid ounces (US)'],
                    ['value' => 'g', 'label' => 'Grams (water)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const table = { cup: 236.5882365, tbsp: 14.78676478125, tsp: 4.92892159375, ml: 1, floz: 29.5735295625, g: 1 };
            const labels = { cup: 'cups', tbsp: 'tbsp', tsp: 'tsp', ml: 'mL', floz: 'US fl oz', g: 'g' };
            if (!(v.from in table) || !(v.to in table)) throw new Error('Choose a valid unit.');
            const value = h.n(v.value);
            const factor = table[v.from] / table[v.to];
            return [
                { label: 'Result', value: value * factor, unit: labels[v.to], hint: `1 ${labels[v.from]} = ${h.round(factor, 10)} ${labels[v.to]}` },
                { label: 'Conversion factor', value: factor, hint: 'Grams assume water density: 1 g = 1 mL.' },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'cup', 'to' => 'ml'], 'expect' => ['Result' => 236.5882365, 'Conversion factor' => 236.5882365]],
            ['input' => ['value' => 1, 'from' => 'cup', 'to' => 'tbsp'], 'expect' => ['Result' => 16]],
            ['input' => ['value' => 1, 'from' => 'tbsp', 'to' => 'tsp'], 'expect' => ['Result' => 3]],
            ['input' => ['value' => 2, 'from' => 'cup', 'to' => 'cup'], 'expect' => ['Result' => 2, 'Conversion factor' => 1]],
        ],
    ],
];
