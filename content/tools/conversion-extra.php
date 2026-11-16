<?php
/**
 * UniVerse — Unit conversion (expanded catalogue).
 * 12 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'acceleration-converter',
        'name' => 'Acceleration Converter',
        'category' => 'conversion',
        'description' => 'Convert acceleration between m/s², ft/s² and standard gravity.',
        'icon' => 'arrow-left-right',
        'keywords' => ['acceleration', 'gravity', 'm/s2', 'ft/s2', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'ms2', 'options' => [
                ['value' => 'ms2', 'label' => 'Metres per second squared (m/s²)'],
                ['value' => 'fts2', 'label' => 'Feet per second squared (ft/s²)'],
                ['value' => 'g', 'label' => 'Standard gravity (g)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toMs2 = { ms2: 1, fts2: 0.3048, g: 9.80665 };
    const ms2 = value * (toMs2[v.from] ?? 1);
    return [
        { label: 'Metres per second squared', value: ms2, unit: 'm/s²' },
        { label: 'Feet per second squared', value: ms2 / 0.3048, unit: 'ft/s²' },
        { label: 'Standard gravity', value: ms2 / 9.80665, unit: 'g' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'ms2'], 'expect' => ['Metres per second squared' => 1, 'Feet per second squared' => 3.280839895013123, 'Standard gravity' => 0.10197162129779283]],
            ['input' => ['value' => 1, 'from' => 'g'], 'expect' => ['Metres per second squared' => 9.80665, 'Feet per second squared' => 32.17404855643044]],
        ],
    ],
    [
        'slug' => 'torque-converter',
        'name' => 'Torque Converter',
        'category' => 'conversion',
        'description' => 'Convert torque between newton-metres, pound-feet and kilogram-force metres.',
        'icon' => 'wrench',
        'keywords' => ['torque', 'newton metre', 'pound foot', 'kgf', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'nm', 'options' => [
                ['value' => 'nm', 'label' => 'Newton-metres (N·m)'],
                ['value' => 'lbft', 'label' => 'Pound-feet (lbf·ft)'],
                ['value' => 'kgfm', 'label' => 'Kilogram-force metres (kgf·m)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toNm = { nm: 1, lbft: 1.3558179483314, kgfm: 9.80665 };
    const nm = value * (toNm[v.from] ?? 1);
    return [
        { label: 'Newton-metres', value: nm, unit: 'N·m' },
        { label: 'Pound-feet', value: nm / 1.3558179483314, unit: 'lbf·ft' },
        { label: 'Kilogram-force metres', value: nm / 9.80665, unit: 'kgf·m' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'nm'], 'expect' => ['Newton-metres' => 1, 'Pound-feet' => 0.7375621492772654, 'Kilogram-force metres' => 0.10197162129779283]],
            ['input' => ['value' => 1, 'from' => 'lbft'], 'expect' => ['Newton-metres' => 1.3558179483314, 'Pound-feet' => 1]],
        ],
    ],
    [
        'slug' => 'frequency-converter',
        'name' => 'Frequency Converter',
        'category' => 'conversion',
        'description' => 'Convert frequency between hertz, kilohertz, megahertz and gigahertz.',
        'icon' => 'zap',
        'keywords' => ['frequency', 'hertz', 'kilohertz', 'megahertz', 'gigahertz'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1000, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'hz', 'options' => [
                ['value' => 'hz', 'label' => 'Hertz (Hz)'],
                ['value' => 'khz', 'label' => 'Kilohertz (kHz)'],
                ['value' => 'mhz', 'label' => 'Megahertz (MHz)'],
                ['value' => 'ghz', 'label' => 'Gigahertz (GHz)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toHz = { hz: 1, khz: 1e3, mhz: 1e6, ghz: 1e9 };
    const hz = value * (toHz[v.from] ?? 1);
    return [
        { label: 'Hertz', value: hz, unit: 'Hz' },
        { label: 'Kilohertz', value: hz / 1e3, unit: 'kHz' },
        { label: 'Megahertz', value: hz / 1e6, unit: 'MHz' },
        { label: 'Gigahertz', value: hz / 1e9, unit: 'GHz' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1000, 'from' => 'hz'], 'expect' => ['Hertz' => 1000, 'Kilohertz' => 1, 'Megahertz' => 0.001, 'Gigahertz' => 1e-6]],
            ['input' => ['value' => 2.4, 'from' => 'ghz'], 'expect' => ['Hertz' => 2400000000, 'Kilohertz' => 2400000, 'Megahertz' => 2400, 'Gigahertz' => 2.4]],
        ],
    ],
    [
        'slug' => 'force-converter',
        'name' => 'Force Converter',
        'category' => 'conversion',
        'description' => 'Convert force between newtons, kilonewtons, pound-force and kilogram-force.',
        'icon' => 'arrow-up-down',
        'keywords' => ['force', 'newton', 'kilonewton', 'pound force', 'kgf'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'n', 'options' => [
                ['value' => 'n', 'label' => 'Newtons (N)'],
                ['value' => 'kn', 'label' => 'Kilonewtons (kN)'],
                ['value' => 'lbf', 'label' => 'Pound-force (lbf)'],
                ['value' => 'kgf', 'label' => 'Kilogram-force (kgf)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toN = { n: 1, kn: 1000, lbf: 4.4482216152605, kgf: 9.80665 };
    const n = value * (toN[v.from] ?? 1);
    return [
        { label: 'Newtons', value: n, unit: 'N' },
        { label: 'Kilonewtons', value: n / 1000, unit: 'kN' },
        { label: 'Pound-force', value: n / 4.4482216152605, unit: 'lbf' },
        { label: 'Kilogram-force', value: n / 9.80665, unit: 'kgf' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'kn'], 'expect' => ['Newtons' => 1000, 'Kilonewtons' => 1, 'Pound-force' => 224.80894308791913, 'Kilogram-force' => 101.97162129779283]],
            ['input' => ['value' => 1, 'from' => 'lbf'], 'expect' => ['Newtons' => 4.4482216152605, 'Pound-force' => 1]],
        ],
    ],
    [
        'slug' => 'density-converter',
        'name' => 'Density Converter',
        'category' => 'conversion',
        'description' => 'Convert density between kg/m³, g/cm³ and pounds per cubic foot.',
        'icon' => 'scale',
        'keywords' => ['density', 'kg/m3', 'g/cm3', 'lb/ft3', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1000, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'kgm3', 'options' => [
                ['value' => 'kgm3', 'label' => 'Kilograms per cubic metre (kg/m³)'],
                ['value' => 'gcm3', 'label' => 'Grams per cubic centimetre (g/cm³)'],
                ['value' => 'lbft3', 'label' => 'Pounds per cubic foot (lb/ft³)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toKgM3 = { kgm3: 1, gcm3: 1000, lbft3: 16.01846337396 };
    const kgm3 = value * (toKgM3[v.from] ?? 1);
    return [
        { label: 'Kilograms per cubic metre', value: kgm3, unit: 'kg/m³' },
        { label: 'Grams per cubic centimetre', value: kgm3 / 1000, unit: 'g/cm³' },
        { label: 'Pounds per cubic foot', value: kgm3 / 16.01846337396, unit: 'lb/ft³' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1000, 'from' => 'kgm3'], 'expect' => ['Kilograms per cubic metre' => 1000, 'Grams per cubic centimetre' => 1, 'Pounds per cubic foot' => 62.42796057614461]],
            ['input' => ['value' => 1, 'from' => 'gcm3'], 'expect' => ['Kilograms per cubic metre' => 1000, 'Grams per cubic centimetre' => 1]],
        ],
    ],
    [
        'slug' => 'flow-rate-converter',
        'name' => 'Flow Rate Converter',
        'category' => 'conversion',
        'description' => 'Convert volumetric flow between litres, cubic metres and US gallons.',
        'icon' => 'arrow-left-right',
        'keywords' => ['flow rate', 'litres', 'gallons', 'cubic metres', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'ls', 'options' => [
                ['value' => 'ls', 'label' => 'Litres per second (L/s)'],
                ['value' => 'lmin', 'label' => 'Litres per minute (L/min)'],
                ['value' => 'm3h', 'label' => 'Cubic metres per hour (m³/h)'],
                ['value' => 'gpm', 'label' => 'US gallons per minute (gal/min)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toLps = { ls: 1, lmin: 1 / 60, m3h: 1000 / 3600, gpm: 3.785411784 / 60 };
    const lps = value * (toLps[v.from] ?? 1);
    return [
        { label: 'Litres per second', value: lps, unit: 'L/s' },
        { label: 'Litres per minute', value: lps * 60, unit: 'L/min' },
        { label: 'Cubic metres per hour', value: lps * 3.6, unit: 'm³/h' },
        { label: 'US gallons per minute', value: lps * 60 / 3.785411784, unit: 'gal/min' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'ls'], 'expect' => ['Litres per second' => 1, 'Litres per minute' => 60, 'Cubic metres per hour' => 3.6, 'US gallons per minute' => 15.850323141488905]],
            ['input' => ['value' => 1, 'from' => 'gpm'], 'expect' => ['Litres per second' => 0.0630901964, 'Litres per minute' => 3.785411784]],
        ],
    ],
    [
        'slug' => 'illuminance-converter',
        'name' => 'Illuminance Converter',
        'category' => 'conversion',
        'description' => 'Convert illuminance between lux, foot-candles and phot.',
        'icon' => 'sun',
        'keywords' => ['illuminance', 'lux', 'foot candle', 'phot', 'light'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'lux', 'options' => [
                ['value' => 'lux', 'label' => 'Lux (lx)'],
                ['value' => 'fc', 'label' => 'Foot-candles (fc)'],
                ['value' => 'phot', 'label' => 'Phot (ph)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toLux = { lux: 1, fc: 10.763910416709722, phot: 10000 };
    const lux = value * (toLux[v.from] ?? 1);
    return [
        { label: 'Lux', value: lux, unit: 'lx' },
        { label: 'Foot-candles', value: lux / 10.763910416709722, unit: 'fc' },
        { label: 'Phot', value: lux / 10000, unit: 'ph' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'lux'], 'expect' => ['Lux' => 1, 'Foot-candles' => 0.09290304, 'Phot' => 0.0001]],
            ['input' => ['value' => 1, 'from' => 'fc'], 'expect' => ['Lux' => 10.763910416709722, 'Foot-candles' => 1]],
        ],
    ],
    [
        'slug' => 'viscosity-converter',
        'name' => 'Viscosity Converter',
        'category' => 'conversion',
        'description' => 'Convert dynamic viscosity between Pa·s, mPa·s (cP) and poise.',
        'icon' => 'arrow-up-down',
        'keywords' => ['viscosity', 'poise', 'centipoise', 'pascal second', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'pas', 'options' => [
                ['value' => 'pas', 'label' => 'Pascal-seconds (Pa·s)'],
                ['value' => 'mpas', 'label' => 'Millipascal-seconds / centipoise (mPa·s)'],
                ['value' => 'p', 'label' => 'Poise (P)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toPas = { pas: 1, mpas: 0.001, p: 0.1 };
    const pas = value * (toPas[v.from] ?? 1);
    return [
        { label: 'Pascal-seconds', value: pas, unit: 'Pa·s' },
        { label: 'Millipascal-seconds', value: pas * 1000, unit: 'mPa·s' },
        { label: 'Poise', value: pas * 10, unit: 'P' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'pas'], 'expect' => ['Pascal-seconds' => 1, 'Millipascal-seconds' => 1000, 'Poise' => 10]],
            ['input' => ['value' => 1, 'from' => 'mpas'], 'expect' => ['Pascal-seconds' => 0.001, 'Millipascal-seconds' => 1, 'Poise' => 0.01]],
        ],
    ],
    [
        'slug' => 'typography-converter',
        'name' => 'Typography Converter',
        'category' => 'conversion',
        'description' => 'Convert font sizes between pixels, points, rem, em and percent.',
        'icon' => 'type',
        'keywords' => ['css', 'font size', 'px', 'pt', 'rem', 'em'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 16, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'px', 'options' => [
                ['value' => 'px', 'label' => 'Pixels (px)'],
                ['value' => 'pt', 'label' => 'Points (pt)'],
                ['value' => 'rem', 'label' => 'Root em (rem)'],
                ['value' => 'em', 'label' => 'Em (em)'],
                ['value' => 'percent', 'label' => 'Percent (%)'],
            ]],
            ['name' => 'base', 'label' => 'Base font size (px)', 'type' => 'number', 'default' => 16, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const base = h.n(v.base);
    if (base <= 0) throw new Error('Base font size must be greater than zero.');
    let px;
    if (v.from === 'pt') px = value * 96 / 72;
    else if (v.from === 'rem' || v.from === 'em') px = value * base;
    else if (v.from === 'percent') px = value / 100 * base;
    else px = value;
    return [
        { label: 'Pixels', value: px, unit: 'px' },
        { label: 'Points', value: px * 72 / 96, unit: 'pt' },
        { label: 'Root em', value: px / base, unit: 'rem' },
        { label: 'Em', value: px / base, unit: 'em' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 16, 'from' => 'px', 'base' => 16], 'expect' => ['Pixels' => 16, 'Points' => 12, 'Root em' => 1, 'Em' => 1]],
            ['input' => ['value' => 1.5, 'from' => 'em', 'base' => 16], 'expect' => ['Pixels' => 24, 'Points' => 18, 'Root em' => 1.5, 'Em' => 1.5]],
            ['input' => ['value' => 12, 'from' => 'pt', 'base' => 16], 'expect' => ['Pixels' => 16, 'Points' => 12, 'Root em' => 1]],
        ],
    ],
    [
        'slug' => 'paper-size-converter',
        'name' => 'Paper Size Converter',
        'category' => 'conversion',
        'description' => 'Look up A, B, C and US paper sizes in millimetres or inches.',
        'icon' => 'file-text',
        'keywords' => ['paper size', 'a4', 'letter', 'a3', 'print'],
        'inputs' => [
            ['name' => 'size', 'label' => 'Paper size', 'type' => 'select', 'default' => 'a4', 'options' => [
                ['value' => 'a0', 'label' => 'A0'], ['value' => 'a1', 'label' => 'A1'], ['value' => 'a2', 'label' => 'A2'],
                ['value' => 'a3', 'label' => 'A3'], ['value' => 'a4', 'label' => 'A4'], ['value' => 'a5', 'label' => 'A5'],
                ['value' => 'a6', 'label' => 'A6'], ['value' => 'a7', 'label' => 'A7'], ['value' => 'a8', 'label' => 'A8'],
                ['value' => 'b4', 'label' => 'B4'], ['value' => 'b5', 'label' => 'B5'], ['value' => 'b6', 'label' => 'B6'],
                ['value' => 'letter', 'label' => 'US Letter'], ['value' => 'legal', 'label' => 'US Legal'],
            ]],
            ['name' => 'unit', 'label' => 'Unit', 'type' => 'select', 'default' => 'mm', 'options' => [
                ['value' => 'mm', 'label' => 'Millimetres'],
                ['value' => 'in', 'label' => 'Inches'],
            ]],
        ],
        'compute' => <<<'JS'
    const sizes = {
        a0: [841, 1189], a1: [594, 841], a2: [420, 594], a3: [297, 420], a4: [210, 297],
        a5: [148, 210], a6: [105, 148], a7: [74, 105], a8: [52, 74],
        b4: [250, 353], b5: [176, 250], b6: [125, 176], letter: [215.9, 279.4], legal: [215.9, 355.6],
    };
    const size = sizes[v.size] || sizes.a4;
    const factor = v.unit === 'in' ? 1 / 25.4 : 1;
    const width = size[0] * factor;
    const height = size[1] * factor;
    return [
        { label: 'Width', value: width, unit: v.unit === 'in' ? 'in' : 'mm' },
        { label: 'Height', value: height, unit: v.unit === 'in' ? 'in' : 'mm' },
        { label: 'Area', value: width * height, unit: v.unit === 'in' ? 'in²' : 'mm²' },
    ];
JS,
        'tests' => [
            ['input' => ['size' => 'a4', 'unit' => 'mm'], 'expect' => ['Width' => 210, 'Height' => 297, 'Area' => 62370]],
            ['input' => ['size' => 'a4', 'unit' => 'in'], 'expect' => ['Width' => 8.26771653543307, 'Height' => 11.69291338582677]],
            ['input' => ['size' => 'letter', 'unit' => 'mm'], 'expect' => ['Width' => 215.9, 'Height' => 279.4]],
        ],
    ],
    [
        'slug' => 'aspect-ratio-calculator',
        'name' => 'Aspect Ratio Calculator',
        'category' => 'conversion',
        'description' => 'Simplify a screen or image aspect ratio and calculate its diagonal.',
        'icon' => 'ratio',
        'keywords' => ['aspect ratio', 'screen', 'resolution', 'image', 'diagonal'],
        'inputs' => [
            ['name' => 'width', 'label' => 'Width (px)', 'type' => 'number', 'default' => 1920, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Height (px)', 'type' => 'number', 'default' => 1080, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const width = h.n(v.width), height = h.n(v.height);
    if (width <= 0 || height <= 0) throw new Error('Width and height must be greater than zero.');
    const gcd = (a, b) => {
        a = Math.abs(a); b = Math.abs(b);
        while (b) { const t = b; b = a % b; a = t; }
        return a || 1;
    };
    const isInt = Number.isInteger(width) && Number.isInteger(height);
    const ratio = isInt ? `${width / gcd(width, height)}:${height / gcd(width, height)}` : `${h.round(width / height, 2)}:1`;
    return [
        { label: 'Aspect ratio', value: ratio },
        { label: 'Decimal ratio', value: width / height },
        { label: 'Diagonal', value: Math.hypot(width, height), unit: 'px' },
        { label: 'Megapixels', value: (width * height) / 1e6, unit: 'MP' },
    ];
JS,
        'tests' => [
            ['input' => ['width' => 1920, 'height' => 1080], 'expect' => ['Aspect ratio' => '16:9', 'Decimal ratio' => 1.7777777777777777, 'Diagonal' => 2202.9071700822983, 'Megapixels' => 2.0736]],
            ['input' => ['width' => 800, 'height' => 600], 'expect' => ['Aspect ratio' => '4:3', 'Decimal ratio' => 1.3333333333333333, 'Diagonal' => 1000, 'Megapixels' => 0.48]],
        ],
    ],
    [
        'slug' => 'data-transfer-rate-converter',
        'name' => 'Data Transfer Rate Converter',
        'category' => 'conversion',
        'description' => 'Convert network speeds between bits, kilobits, megabits and megabytes per second.',
        'icon' => 'network',
        'keywords' => ['bandwidth', 'mbps', 'mb/s', 'network speed', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'from', 'label' => 'From', 'type' => 'select', 'default' => 'mbps', 'options' => [
                ['value' => 'bps', 'label' => 'Bits per second (bps)'],
                ['value' => 'kbps', 'label' => 'Kilobits per second (Kbps)'],
                ['value' => 'mbps', 'label' => 'Megabits per second (Mbps)'],
                ['value' => 'gbps', 'label' => 'Gigabits per second (Gbps)'],
                ['value' => 'mbs', 'label' => 'Megabytes per second (MB/s)'],
            ]],
        ],
        'compute' => <<<'JS'
    const value = h.n(v.value);
    const toBps = { bps: 1, kbps: 1e3, mbps: 1e6, gbps: 1e9, mbs: 8e6 };
    const bps = value * (toBps[v.from] ?? 1);
    return [
        { label: 'Bits per second', value: bps, unit: 'bps' },
        { label: 'Kilobits per second', value: bps / 1e3, unit: 'Kbps' },
        { label: 'Megabits per second', value: bps / 1e6, unit: 'Mbps' },
        { label: 'Megabytes per second', value: bps / 8e6, unit: 'MB/s' },
    ];
JS,
        'tests' => [
            ['input' => ['value' => 1, 'from' => 'mbps'], 'expect' => ['Bits per second' => 1000000, 'Kilobits per second' => 1000, 'Megabits per second' => 1, 'Megabytes per second' => 0.125]],
            ['input' => ['value' => 1, 'from' => 'mbs'], 'expect' => ['Bits per second' => 8000000, 'Megabits per second' => 8, 'Megabytes per second' => 1]],
        ],
    ],
];
