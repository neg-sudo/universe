<?php
/**
 * UniVerse — Science (expanded catalogue).
 * 12 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'acceleration-calculator',
        'name' => 'Acceleration Calculator',
        'category' => 'science',
        'description' => 'Find acceleration, distance and average velocity from two speeds.',
        'icon' => 'trending-up',
        'keywords' => ['acceleration', 'velocity', 'physics', 'kinematics'],
        'inputs' => [
            ['name' => 'u', 'label' => 'Initial velocity (m/s)', 'type' => 'number', 'default' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'v', 'label' => 'Final velocity (m/s)', 'type' => 'number', 'default' => 10, 'step' => 'any', 'required' => true],
            ['name' => 't', 'label' => 'Time (s)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const u = h.n(v.u), finalV = h.n(v.v), t = h.n(v.t);
    if (t <= 0) throw new Error('Time must be greater than zero.');
    return [
        { label: 'Acceleration', value: (finalV - u) / t, unit: 'm/s²' },
        { label: 'Distance', value: ((u + finalV) / 2) * t, unit: 'm' },
        { label: 'Average velocity', value: (u + finalV) / 2, unit: 'm/s' },
    ];
JS,
        'tests' => [
            ['input' => ['u' => 0, 'v' => 10, 't' => 2], 'expect' => ['Acceleration' => 5, 'Distance' => 10, 'Average velocity' => 5]],
            ['input' => ['u' => 5, 'v' => 15, 't' => 5], 'expect' => ['Acceleration' => 2, 'Distance' => 50, 'Average velocity' => 10]],
        ],
    ],
    [
        'slug' => 'kinetic-energy-calculator',
        'name' => 'Kinetic Energy Calculator',
        'category' => 'science',
        'description' => 'Calculate kinetic energy and momentum from mass and velocity.',
        'icon' => 'zap',
        'keywords' => ['kinetic energy', 'mass', 'velocity', 'momentum', 'physics'],
        'inputs' => [
            ['name' => 'mass', 'label' => 'Mass (kg)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
            ['name' => 'velocity', 'label' => 'Velocity (m/s)', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const mass = h.n(v.mass), velocity = h.n(v.velocity);
    const energy = 0.5 * mass * velocity ** 2;
    return [
        { label: 'Kinetic energy', value: energy, unit: 'J' },
        { label: 'Kinetic energy (kJ)', value: energy / 1000, unit: 'kJ' },
        { label: 'Momentum', value: mass * velocity, unit: 'kg·m/s' },
    ];
JS,
        'tests' => [
            ['input' => ['mass' => 2, 'velocity' => 3], 'expect' => ['Kinetic energy' => 9, 'Kinetic energy (kJ)' => 0.009, 'Momentum' => 6]],
            ['input' => ['mass' => 1000, 'velocity' => 20], 'expect' => ['Kinetic energy' => 200000, 'Kinetic energy (kJ)' => 200, 'Momentum' => 20000]],
        ],
    ],
    [
        'slug' => 'potential-energy-calculator',
        'name' => 'Potential Energy Calculator',
        'category' => 'science',
        'description' => 'Calculate gravitational potential energy from mass, height and gravity.',
        'icon' => 'arrow-up-down',
        'keywords' => ['potential energy', 'gravity', 'height', 'mass', 'physics'],
        'inputs' => [
            ['name' => 'mass', 'label' => 'Mass (kg)', 'type' => 'number', 'default' => 10, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Height (m)', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
            ['name' => 'gravity', 'label' => 'Gravity (m/s²)', 'type' => 'number', 'default' => 9.80665, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const mass = h.n(v.mass), height = h.n(v.height), gravity = h.n(v.gravity);
    const energy = mass * gravity * height;
    return [
        { label: 'Potential energy', value: energy, unit: 'J' },
        { label: 'Potential energy (kJ)', value: energy / 1000, unit: 'kJ' },
        { label: 'Weight', value: mass * gravity, unit: 'N' },
    ];
JS,
        'tests' => [
            ['input' => ['mass' => 10, 'height' => 5, 'gravity' => 9.80665], 'expect' => ['Potential energy' => 490.3325, 'Potential energy (kJ)' => 0.4903325, 'Weight' => 98.0665]],
            ['input' => ['mass' => 2, 'height' => 10, 'gravity' => 9.81], 'expect' => ['Potential energy' => 196.2, 'Weight' => 19.62]],
        ],
    ],
    [
        'slug' => 'work-calculator',
        'name' => 'Work Calculator',
        'category' => 'science',
        'description' => 'Calculate work done by a force over a distance and angle.',
        'icon' => 'wrench',
        'keywords' => ['work', 'force', 'distance', 'angle', 'physics'],
        'inputs' => [
            ['name' => 'force', 'label' => 'Force (N)', 'type' => 'number', 'default' => 10, 'step' => 'any', 'required' => true],
            ['name' => 'distance', 'label' => 'Distance (m)', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
            ['name' => 'angle', 'label' => 'Angle to motion (°)', 'type' => 'number', 'default' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const force = h.n(v.force), distance = h.n(v.distance), angle = h.n(v.angle);
    const effective = force * Math.cos(angle * Math.PI / 180);
    const work = effective * distance;
    return [
        { label: 'Work', value: work, unit: 'J' },
        { label: 'Work (kJ)', value: work / 1000, unit: 'kJ' },
        { label: 'Effective force', value: effective, unit: 'N' },
    ];
JS,
        'tests' => [
            ['input' => ['force' => 10, 'distance' => 5, 'angle' => 0], 'expect' => ['Work' => 50, 'Work (kJ)' => 0.05, 'Effective force' => 10]],
            ['input' => ['force' => 10, 'distance' => 5, 'angle' => 60], 'expect' => ['Work' => 25, 'Effective force' => 5]],
        ],
    ],
    [
        'slug' => 'momentum-calculator',
        'name' => 'Momentum Calculator',
        'category' => 'science',
        'description' => 'Calculate momentum and kinetic energy from mass and velocity.',
        'icon' => 'zap',
        'keywords' => ['momentum', 'mass', 'velocity', 'kinetic energy', 'physics'],
        'inputs' => [
            ['name' => 'mass', 'label' => 'Mass (kg)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
            ['name' => 'velocity', 'label' => 'Velocity (m/s)', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const mass = h.n(v.mass), velocity = h.n(v.velocity);
    return [
        { label: 'Momentum', value: mass * velocity, unit: 'kg·m/s' },
        { label: 'Kinetic energy', value: 0.5 * mass * velocity ** 2, unit: 'J' },
        { label: 'Velocity', value: velocity, unit: 'm/s' },
    ];
JS,
        'tests' => [
            ['input' => ['mass' => 2, 'velocity' => 3], 'expect' => ['Momentum' => 6, 'Kinetic energy' => 9, 'Velocity' => 3]],
            ['input' => ['mass' => 10, 'velocity' => 5], 'expect' => ['Momentum' => 50, 'Kinetic energy' => 125]],
        ],
    ],
    [
        'slug' => 'wave-speed-calculator',
        'name' => 'Wave Speed Calculator',
        'category' => 'science',
        'description' => 'Find wave speed and period from frequency and wavelength.',
        'icon' => 'zap',
        'keywords' => ['wave', 'frequency', 'wavelength', 'period', 'physics'],
        'inputs' => [
            ['name' => 'frequency', 'label' => 'Frequency (Hz)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
            ['name' => 'wavelength', 'label' => 'Wavelength (m)', 'type' => 'number', 'default' => 3, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const frequency = h.n(v.frequency), wavelength = h.n(v.wavelength);
    if (frequency <= 0) throw new Error('Frequency must be greater than zero.');
    return [
        { label: 'Wave speed', value: frequency * wavelength, unit: 'm/s' },
        { label: 'Period', value: 1 / frequency, unit: 's' },
        { label: 'Frequency', value: frequency, unit: 'Hz' },
    ];
JS,
        'tests' => [
            ['input' => ['frequency' => 2, 'wavelength' => 3], 'expect' => ['Wave speed' => 6, 'Period' => 0.5, 'Frequency' => 2]],
            ['input' => ['frequency' => 440, 'wavelength' => 0.78], 'expect' => ['Wave speed' => 343.2, 'Period' => 0.0022727272727272726]],
        ],
    ],
    [
        'slug' => 'ideal-gas-law-calculator',
        'name' => 'Ideal Gas Law Calculator',
        'category' => 'science',
        'description' => 'Solve PV = nRT for pressure, volume, moles or temperature.',
        'icon' => 'flask-conical',
        'keywords' => ['ideal gas', 'pv=nrt', 'pressure', 'volume', 'temperature'],
        'inputs' => [
            ['name' => 'solveFor', 'label' => 'Solve for', 'type' => 'select', 'default' => 'pressure', 'options' => [
                ['value' => 'pressure', 'label' => 'Pressure (P)'],
                ['value' => 'volume', 'label' => 'Volume (V)'],
                ['value' => 'moles', 'label' => 'Moles (n)'],
                ['value' => 'temperature', 'label' => 'Temperature (T)'],
            ]],
            ['name' => 'pressure', 'label' => 'Pressure (kPa)', 'type' => 'number', 'default' => 101.325, 'step' => 'any'],
            ['name' => 'volume', 'label' => 'Volume (L)', 'type' => 'number', 'default' => 22.414, 'step' => 'any'],
            ['name' => 'moles', 'label' => 'Moles (mol)', 'type' => 'number', 'default' => 1, 'step' => 'any'],
            ['name' => 'temperature', 'label' => 'Temperature (K)', 'type' => 'number', 'default' => 273.15, 'step' => 'any'],
        ],
        'compute' => <<<'JS'
    const R = 8.314462618;
    let P = h.n(v.pressure), V = h.n(v.volume), n = h.n(v.moles), T = h.n(v.temperature);
    if (v.solveFor === 'pressure') {
        if (V <= 0) throw new Error('Volume must be greater than zero.');
        P = (n * R * T) / V;
    } else if (v.solveFor === 'volume') {
        if (P <= 0) throw new Error('Pressure must be greater than zero.');
        V = (n * R * T) / P;
    } else if (v.solveFor === 'moles') {
        if (R * T === 0) throw new Error('Temperature must be greater than zero.');
        n = (P * V) / (R * T);
    } else {
        if (n * R === 0) throw new Error('Moles must be greater than zero.');
        T = (P * V) / (n * R);
    }
    return [
        { label: 'Pressure', value: P, unit: 'kPa' },
        { label: 'Volume', value: V, unit: 'L' },
        { label: 'Moles', value: n, unit: 'mol' },
        { label: 'Temperature', value: T, unit: 'K' },
    ];
JS,
        'tests' => [
            ['input' => ['solveFor' => 'pressure', 'volume' => 22.414, 'moles' => 1, 'temperature' => 273.15], 'expect' => ['Pressure' => 101.3248623229544]],
            ['input' => ['solveFor' => 'volume', 'pressure' => 101.325, 'moles' => 1, 'temperature' => 273.15], 'expect' => ['Volume' => 22.413969544601038]],
            ['input' => ['solveFor' => 'moles', 'pressure' => 101.325, 'volume' => 22.414, 'temperature' => 273.15], 'expect' => ['Moles' => 1.0000013587686423]],
            ['input' => ['solveFor' => 'temperature', 'pressure' => 101.325, 'volume' => 22.414, 'moles' => 1], 'expect' => ['Temperature' => 273.1503711476546]],
        ],
    ],
    [
        'slug' => 'molar-mass-calculator',
        'name' => 'Molar Mass Calculator',
        'category' => 'science',
        'description' => 'Calculate the molar mass of a chemical formula, including parentheses.',
        'icon' => 'flask-conical',
        'featured' => true,
        'keywords' => ['molar mass', 'chemistry', 'formula', 'molecule', 'moles'],
        'inputs' => [
            ['name' => 'formula', 'label' => 'Chemical formula', 'type' => 'text', 'default' => 'H2O', 'required' => true, 'hint' => 'Examples: H2O, C6H12O6, Ca(OH)2, NaCl.'],
        ],
        'compute' => <<<'JS'
    const weights = {
        H: 1.008, He: 4.0026, Li: 6.94, Be: 9.0122, B: 10.81, C: 12.011, N: 14.007, O: 15.999,
        F: 18.998, Ne: 20.180, Na: 22.990, Mg: 24.305, Al: 26.982, Si: 28.085, P: 30.974,
        S: 32.06, Cl: 35.45, Ar: 39.948, K: 39.098, Ca: 40.078, Ti: 47.867, Cr: 51.996,
        Mn: 54.938, Fe: 55.845, Co: 58.933, Ni: 58.693, Cu: 63.546, Zn: 65.38, Br: 79.904,
        Ag: 107.868, I: 126.904, Ba: 137.327, Au: 196.967, Hg: 200.592, Pb: 207.2, U: 238.029,
    };
    const formula = String(v.formula ?? '').replace(/\s+/g, '');
    if (!formula) throw new Error('Enter a chemical formula.');
    let i = 0;
    const readNumber = () => {
        let digits = '';
        while (i < formula.length && /\d/.test(formula[i])) { digits += formula[i]; i++; }
        return digits === '' ? 1 : parseInt(digits, 10);
    };
    const merge = (target, source, factor) => {
        for (const [element, count] of Object.entries(source)) target[element] = (target[element] || 0) + count * factor;
    };
    const parseGroup = (closing) => {
        const counts = {};
        while (i < formula.length) {
            const ch = formula[i];
            if (ch === '(') {
                i++;
                const inner = parseGroup(')');
                if (formula[i] !== ')') throw new Error('Unbalanced parentheses.');
                i++;
                merge(counts, inner, readNumber());
            } else if (ch === ')') {
                if (closing === ')') return counts;
                throw new Error('Unexpected closing parenthesis.');
            } else if (/[A-Z]/.test(ch)) {
                let element = ch;
                i++;
                while (i < formula.length && /[a-z]/.test(formula[i])) { element += formula[i]; i++; }
                if (!(element in weights)) throw new Error('Unknown element: ' + element);
                counts[element] = (counts[element] || 0) + readNumber();
            } else {
                throw new Error('Unexpected character: ' + ch);
            }
        }
        if (closing) throw new Error('Unbalanced parentheses.');
        return counts;
    };
    const counts = parseGroup(null);
    const mass = Object.entries(counts).reduce((sum, [element, count]) => sum + weights[element] * count, 0);
    const atoms = Object.values(counts).reduce((sum, count) => sum + count, 0);
    return [
        { label: 'Molar mass', value: mass, unit: 'g/mol' },
        { label: 'Atoms', value: atoms, decimals: 0 },
        { label: 'Formula', value: formula },
    ];
JS,
        'tests' => [
            ['input' => ['formula' => 'H2O'], 'expect' => ['Molar mass' => 18.015, 'Atoms' => 3, 'Formula' => 'H2O']],
            ['input' => ['formula' => 'C6H12O6'], 'expect' => ['Molar mass' => 180.156, 'Atoms' => 24]],
            ['input' => ['formula' => 'NaCl'], 'expect' => ['Molar mass' => 58.44, 'Atoms' => 2]],
            ['input' => ['formula' => 'Ca(OH)2'], 'expect' => ['Molar mass' => 74.092, 'Atoms' => 5]],
        ],
    ],
    [
        'slug' => 'dilution-calculator',
        'name' => 'Dilution Calculator',
        'category' => 'science',
        'description' => 'Find the final volume and diluent needed for a solution dilution.',
        'icon' => 'flask-conical',
        'keywords' => ['dilution', 'concentration', 'volume', 'chemistry', 'molarity'],
        'inputs' => [
            ['name' => 'c1', 'label' => 'Stock concentration', 'type' => 'number', 'default' => 10, 'step' => 'any', 'required' => true],
            ['name' => 'v1', 'label' => 'Stock volume (mL)', 'type' => 'number', 'default' => 100, 'step' => 'any', 'required' => true],
            ['name' => 'c2', 'label' => 'Final concentration', 'type' => 'number', 'default' => 1, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const c1 = h.n(v.c1), v1 = h.n(v.v1), c2 = h.n(v.c2);
    if (c1 <= 0 || v1 <= 0 || c2 <= 0) throw new Error('Concentrations and volume must be greater than zero.');
    if (c2 > c1) throw new Error('The final concentration cannot exceed the stock concentration.');
    const v2 = (c1 * v1) / c2;
    return [
        { label: 'Final volume', value: v2, unit: 'mL' },
        { label: 'Diluent volume', value: v2 - v1, unit: 'mL' },
        { label: 'Dilution factor', value: c1 / c2 },
    ];
JS,
        'tests' => [
            ['input' => ['c1' => 10, 'v1' => 100, 'c2' => 1], 'expect' => ['Final volume' => 1000, 'Diluent volume' => 900, 'Dilution factor' => 10]],
            ['input' => ['c1' => 5, 'v1' => 20, 'c2' => 0.5], 'expect' => ['Final volume' => 200, 'Diluent volume' => 180, 'Dilution factor' => 10]],
        ],
    ],
    [
        'slug' => 'body-surface-area-calculator',
        'name' => 'Body Surface Area Calculator',
        'category' => 'science',
        'description' => 'Estimate body surface area with the Mosteller or Du Bois formula.',
        'icon' => 'heart-pulse',
        'keywords' => ['body surface area', 'bsa', 'mosteller', 'du bois', 'health'],
        'inputs' => [
            ['name' => 'height', 'label' => 'Height (cm)', 'type' => 'number', 'default' => 180, 'step' => 'any', 'required' => true],
            ['name' => 'weight', 'label' => 'Weight (kg)', 'type' => 'number', 'default' => 80, 'step' => 'any', 'required' => true],
            ['name' => 'formula', 'label' => 'Formula', 'type' => 'select', 'default' => 'mosteller', 'options' => [
                ['value' => 'mosteller', 'label' => 'Mosteller'],
                ['value' => 'dubois', 'label' => 'Du Bois'],
            ]],
        ],
        'compute' => <<<'JS'
    const height = h.n(v.height), weight = h.n(v.weight);
    if (height <= 0 || weight <= 0) throw new Error('Height and weight must be greater than zero.');
    const mosteller = Math.sqrt((height * weight) / 3600);
    const dubois = 0.007184 * Math.pow(height, 0.725) * Math.pow(weight, 0.425);
    return [
        { label: 'Body surface area', value: v.formula === 'dubois' ? dubois : mosteller, unit: 'm²' },
        { label: 'Mosteller', value: mosteller, unit: 'm²' },
        { label: 'Du Bois', value: dubois, unit: 'm²' },
    ];
JS,
        'tests' => [
            ['input' => ['height' => 180, 'weight' => 80, 'formula' => 'mosteller'], 'expect' => ['Body surface area' => 2, 'Mosteller' => 2, 'Du Bois' => 1.996421022275045]],
            ['input' => ['height' => 180, 'weight' => 80, 'formula' => 'dubois'], 'expect' => ['Body surface area' => 1.996421022275045, 'Mosteller' => 2]],
        ],
    ],
    [
        'slug' => 'heart-rate-zones-calculator',
        'name' => 'Heart Rate Zones Calculator',
        'category' => 'science',
        'description' => 'Calculate maximum heart rate and five training zones by age.',
        'icon' => 'heart-pulse',
        'keywords' => ['heart rate', 'zones', 'cardio', 'fitness', 'karvonen'],
        'inputs' => [
            ['name' => 'age', 'label' => 'Age', 'type' => 'number', 'default' => 30, 'step' => '1', 'required' => true],
            ['name' => 'resting', 'label' => 'Resting heart rate (bpm)', 'type' => 'number', 'default' => 60, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const age = Math.trunc(h.n(v.age)), resting = h.n(v.resting);
    if (age <= 0 || age > 120) throw new Error('Enter an age between 1 and 120.');
    if (resting <= 0 || resting > 220) throw new Error('Enter a valid resting heart rate.');
    const max = 220 - age;
    const zone = (low, high) => `${Math.round(resting + (max - resting) * low)}–${Math.round(resting + (max - resting) * high)} bpm`;
    return [
        { label: 'Max heart rate', value: max, unit: 'bpm' },
        { label: 'Zone 1', value: zone(0.5, 0.6) },
        { label: 'Zone 2', value: zone(0.6, 0.7) },
        { label: 'Zone 3', value: zone(0.7, 0.8) },
        { label: 'Zone 4', value: zone(0.8, 0.9) },
        { label: 'Zone 5', value: zone(0.9, 1.0) },
    ];
JS,
        'tests' => [
            ['input' => ['age' => 30, 'resting' => 60], 'expect' => ['Max heart rate' => 190, 'Zone 1' => '125–138 bpm', 'Zone 5' => '177–190 bpm']],
            ['input' => ['age' => 40, 'resting' => 50], 'expect' => ['Max heart rate' => 180, 'Zone 1' => '115–128 bpm', 'Zone 5' => '167–180 bpm']],
        ],
    ],
    [
        'slug' => 'led-resistor-calculator',
        'name' => 'LED Resistor Calculator',
        'category' => 'science',
        'description' => 'Find the current-limiting resistor for one or more LEDs in series.',
        'icon' => 'zap',
        'keywords' => ['led', 'resistor', 'electronics', 'ohm', 'circuit'],
        'inputs' => [
            ['name' => 'supply', 'label' => 'Supply voltage (V)', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
            ['name' => 'forward', 'label' => 'LED forward voltage (V)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
            ['name' => 'current', 'label' => 'LED current (mA)', 'type' => 'number', 'default' => 20, 'step' => 'any', 'required' => true],
            ['name' => 'count', 'label' => 'LEDs in series', 'type' => 'number', 'default' => 1, 'min' => 1, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const supply = h.n(v.supply), forward = h.n(v.forward), current = h.n(v.current), count = Math.max(1, Math.trunc(h.n(v.count)));
    const drop = supply - forward * count;
    if (drop <= 0) throw new Error('The supply voltage must exceed the total LED forward voltage.');
    if (current <= 0) throw new Error('Current must be greater than zero.');
    const resistance = drop / (current / 1000);
    const e12 = [10, 12, 15, 18, 22, 27, 33, 39, 47, 56, 68, 82];
    const decade = Math.pow(10, Math.floor(Math.log10(resistance)));
    let nearest = Infinity;
    for (const base of e12) {
        for (const mult of [decade / 10, decade, decade * 10]) {
            const value = base * mult;
            if (value >= resistance && value < nearest) nearest = value;
        }
    }
    return [
        { label: 'Resistor', value: resistance, unit: 'Ω' },
        { label: 'Nearest E12 resistor', value: nearest, unit: 'Ω' },
        { label: 'Power dissipation', value: (drop * drop) / resistance, unit: 'W' },
    ];
JS,
        'tests' => [
            ['input' => ['supply' => 5, 'forward' => 2, 'current' => 20, 'count' => 1], 'expect' => ['Resistor' => 150, 'Nearest E12 resistor' => 150, 'Power dissipation' => 0.06]],
            ['input' => ['supply' => 12, 'forward' => 2, 'current' => 20, 'count' => 3], 'expect' => ['Resistor' => 300, 'Nearest E12 resistor' => 330, 'Power dissipation' => 0.12]],
            ['input' => ['supply' => 9, 'forward' => 2, 'current' => 15, 'count' => 1], 'expect' => ['Resistor' => 466.6666666666667, 'Nearest E12 resistor' => 470, 'Power dissipation' => 0.105]],
        ],
    ],
];
