<?php
/**
 * UniVerse — Science
 * 10 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'bmi-calculator',
        'name' => 'BMI Calculator',
        'category' => 'science',
        'description' => 'Calculate BMI from metric or imperial height and weight, with category and healthy weight range.',
        'icon' => 'heart-pulse',
        'featured' => true,
        'keywords' => ['bmi', 'body mass index', 'health', 'weight', 'obesity'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Units',
                'type' => 'select',
                'default' => 'metric',
                'options' => [
                    ['value' => 'metric', 'label' => 'Metric (cm, kg)'],
                    ['value' => 'imperial', 'label' => 'Imperial (inches, lb)'],
                ],
            ],
            ['name' => 'heightCm', 'label' => 'Height (cm)', 'type' => 'number', 'default' => 175, 'min' => 1, 'step' => 'any', 'showIf' => ['mode' => 'metric']],
            ['name' => 'weightKg', 'label' => 'Weight (kg)', 'type' => 'number', 'default' => 70, 'min' => 1, 'step' => 'any', 'showIf' => ['mode' => 'metric']],
            ['name' => 'heightIn', 'label' => 'Height (inches)', 'type' => 'number', 'default' => 69, 'min' => 1, 'step' => 'any', 'showIf' => ['mode' => 'imperial']],
            ['name' => 'weightLb', 'label' => 'Weight (lb)', 'type' => 'number', 'default' => 154, 'min' => 1, 'step' => 'any', 'showIf' => ['mode' => 'imperial']],
        ],
        'compute' => <<<'JS'
            const metric = v.mode !== 'imperial';
            const height = h.n(metric ? v.heightCm : v.heightIn);
            const weight = h.n(metric ? v.weightKg : v.weightLb);
            if (height <= 0 || weight <= 0) throw new Error('Height and weight must be greater than zero.');
            const metres = metric ? height / 100 : height * 0.0254;
            const kg = metric ? weight : weight * 0.45359237;
            const bmi = kg / (metres * metres);
            const category = bmi < 18.5 ? 'Underweight' : bmi < 25 ? 'Normal' : bmi < 30 ? 'Overweight' : 'Obese';
            return [
                { label: 'BMI', value: h.round(bmi, 1), decimals: 1 },
                { label: 'Category', value: category },
                { label: 'Healthy weight range (min)', value: h.round(18.5 * metres * metres, 1), unit: 'kg', decimals: 1 },
                { label: 'Healthy weight range (max)', value: h.round(24.9 * metres * metres, 1), unit: 'kg', decimals: 1 },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'metric', 'heightCm' => 175, 'weightKg' => 70], 'expect' => ['BMI' => 22.9, 'Category' => 'Normal', 'Healthy weight range (min)' => 56.7, 'Healthy weight range (max)' => 76.3]],
            ['input' => ['mode' => 'imperial', 'heightIn' => 69, 'weightLb' => 154], 'expect' => ['BMI' => 22.7, 'Category' => 'Normal']],
            ['input' => ['mode' => 'metric', 'heightCm' => 160, 'weightKg' => 95], 'expect' => ['BMI' => 37.1, 'Category' => 'Obese']],
        ],
    ],

    [
        'slug' => 'bmr-calorie-calculator',
        'name' => 'BMR & Calorie Calculator',
        'category' => 'science',
        'description' => 'Estimate basal metabolic rate and daily calorie needs using the Mifflin-St Jeor equation.',
        'icon' => 'flame',
        'featured' => true,
        'keywords' => ['bmr', 'calories', 'metabolism', 'tdee', 'mifflin st jeor'],
        'inputs' => [
            [
                'name' => 'sex',
                'label' => 'Sex',
                'type' => 'select',
                'default' => 'male',
                'options' => [
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                ],
            ],
            ['name' => 'age', 'label' => 'Age (years)', 'type' => 'number', 'default' => 30, 'min' => 1, 'max' => 120, 'step' => 1, 'required' => true],
            ['name' => 'height', 'label' => 'Height (cm)', 'type' => 'number', 'default' => 180, 'min' => 1, 'step' => 'any', 'required' => true],
            ['name' => 'weight', 'label' => 'Weight (kg)', 'type' => 'number', 'default' => 80, 'min' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'activity',
                'label' => 'Activity level',
                'type' => 'select',
                'default' => 'sedentary',
                'options' => [
                    ['value' => 'sedentary', 'label' => 'Sedentary (little or no exercise)'],
                    ['value' => 'light', 'label' => 'Light (1–3 days per week)'],
                    ['value' => 'moderate', 'label' => 'Moderate (3–5 days per week)'],
                    ['value' => 'active', 'label' => 'Active (6–7 days per week)'],
                    ['value' => 'very-active', 'label' => 'Very active (physical job or twice daily)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const age = h.n(v.age);
            const height = h.n(v.height);
            const weight = h.n(v.weight);
            if (age <= 0 || height <= 0 || weight <= 0) throw new Error('Age, height and weight must be greater than zero.');
            const factors = { sedentary: 1.2, light: 1.375, moderate: 1.55, active: 1.725, 'very-active': 1.9 };
            const factor = factors[v.activity] || 1.2;
            const female = v.sex === 'female';
            const bmr = 10 * weight + 6.25 * height - 5 * age + (female ? -161 : 5);
            const rounded = h.round(bmr, 0);
            return [
                { label: 'BMR', value: rounded, unit: 'kcal/day', decimals: 0 },
                { label: 'Daily calories', value: h.round(rounded * factor, 2), unit: 'kcal/day', decimals: 2 },
            ];
        JS,
        'tests' => [
            ['input' => ['sex' => 'male', 'age' => 30, 'height' => 180, 'weight' => 80, 'activity' => 'sedentary'], 'expect' => ['BMR' => 1780, 'Daily calories' => 2136]],
            ['input' => ['sex' => 'female', 'age' => 25, 'height' => 165, 'weight' => 60, 'activity' => 'moderate'], 'expect' => ['BMR' => 1345, 'Daily calories' => 2084.75]],
            ['input' => ['sex' => 'male', 'age' => 40, 'height' => 180, 'weight' => 90, 'activity' => 'active'], 'expect' => ['BMR' => 1830, 'Daily calories' => 3156.75]],
        ],
    ],

    [
        'slug' => 'ideal-weight-calculator',
        'name' => 'Ideal Weight Calculator',
        'category' => 'science',
        'description' => 'Estimate ideal body weight from height and sex using the Devine, Robinson and Miller formulas.',
        'icon' => 'scale',
        'featured' => false,
        'keywords' => ['ideal weight', 'devine', 'robinson', 'miller', 'healthy weight'],
        'inputs' => [
            ['name' => 'height', 'label' => 'Height (cm)', 'type' => 'number', 'default' => 175, 'min' => 1, 'step' => 'any', 'required' => true],
            [
                'name' => 'sex',
                'label' => 'Sex',
                'type' => 'select',
                'default' => 'male',
                'options' => [
                    ['value' => 'male', 'label' => 'Male'],
                    ['value' => 'female', 'label' => 'Female'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const height = h.n(v.height);
            if (height <= 0) throw new Error('Height must be greater than zero.');
            const female = v.sex === 'female';
            const over = Math.max(0, height / 2.54 - 60);
            const devine = (female ? 45.5 : 50) + 2.3 * over;
            const robinson = (female ? 49 : 52) + (female ? 1.7 : 1.9) * over;
            const miller = (female ? 53.1 : 56.2) + (female ? 1.36 : 1.41) * over;
            return [
                { label: 'Devine', value: h.round(devine, 1), unit: 'kg', decimals: 1 },
                { label: 'Robinson', value: h.round(robinson, 1), unit: 'kg', decimals: 1 },
                { label: 'Miller', value: h.round(miller, 1), unit: 'kg', decimals: 1 },
            ];
        JS,
        'tests' => [
            ['input' => ['height' => 175, 'sex' => 'male'], 'expect' => ['Devine' => 70.5, 'Robinson' => 68.9, 'Miller' => 68.7]],
            ['input' => ['height' => 165, 'sex' => 'female'], 'expect' => ['Devine' => 56.9, 'Robinson' => 57.4, 'Miller' => 59.8]],
            ['input' => ['height' => 150, 'sex' => 'male'], 'expect' => ['Devine' => 50, 'Robinson' => 52, 'Miller' => 56.2]],
        ],
    ],

    [
        'slug' => 'ohms-law-calculator',
        'name' => 'Ohm\'s Law Calculator',
        'category' => 'science',
        'description' => 'Solve Ohm\'s Law for voltage, current, resistance and power from any two known values.',
        'icon' => 'zap',
        'featured' => false,
        'keywords' => ['ohms law', 'voltage', 'current', 'resistance', 'power'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Known values',
                'type' => 'select',
                'default' => 'vi',
                'options' => [
                    ['value' => 'vi', 'label' => 'Voltage and current'],
                    ['value' => 'vr', 'label' => 'Voltage and resistance'],
                    ['value' => 'vp', 'label' => 'Voltage and power'],
                    ['value' => 'ir', 'label' => 'Current and resistance'],
                    ['value' => 'ip', 'label' => 'Current and power'],
                    ['value' => 'rp', 'label' => 'Resistance and power'],
                ],
            ],
            ['name' => 'voltage', 'label' => 'Voltage (V)', 'type' => 'number', 'default' => 12, 'step' => 'any', 'showIf' => ['mode' => ['vi', 'vr', 'vp']]],
            ['name' => 'current', 'label' => 'Current (A)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'showIf' => ['mode' => ['vi', 'ir', 'ip']]],
            ['name' => 'resistance', 'label' => 'Resistance (Ω)', 'type' => 'number', 'default' => 6, 'step' => 'any', 'showIf' => ['mode' => ['vr', 'ir', 'rp']]],
            ['name' => 'power', 'label' => 'Power (W)', 'type' => 'number', 'default' => 24, 'step' => 'any', 'showIf' => ['mode' => ['vp', 'ip', 'rp']]],
        ],
        'compute' => <<<'JS'
            const positive = (value, label) => {
                const n = h.n(value);
                if (!(n > 0)) throw new Error(`${label} must be greater than zero.`);
                return n;
            };
            let voltage, current, resistance, power;
            if (v.mode === 'vi') {
                voltage = positive(v.voltage, 'Voltage');
                current = positive(v.current, 'Current');
                resistance = voltage / current;
                power = voltage * current;
            } else if (v.mode === 'vr') {
                voltage = positive(v.voltage, 'Voltage');
                resistance = positive(v.resistance, 'Resistance');
                current = voltage / resistance;
                power = voltage * current;
            } else if (v.mode === 'vp') {
                voltage = positive(v.voltage, 'Voltage');
                power = positive(v.power, 'Power');
                current = power / voltage;
                resistance = voltage / current;
            } else if (v.mode === 'ir') {
                current = positive(v.current, 'Current');
                resistance = positive(v.resistance, 'Resistance');
                voltage = current * resistance;
                power = current * current * resistance;
            } else if (v.mode === 'ip') {
                current = positive(v.current, 'Current');
                power = positive(v.power, 'Power');
                voltage = power / current;
                resistance = voltage / current;
            } else {
                resistance = positive(v.resistance, 'Resistance');
                power = positive(v.power, 'Power');
                current = Math.sqrt(power / resistance);
                voltage = current * resistance;
            }
            return [
                { label: 'Voltage', value: voltage, unit: 'V' },
                { label: 'Current', value: current, unit: 'A' },
                { label: 'Resistance', value: resistance, unit: 'Ω' },
                { label: 'Power', value: power, unit: 'W' },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'vi', 'voltage' => 12, 'current' => 2], 'expect' => ['Voltage' => 12, 'Current' => 2, 'Resistance' => 6, 'Power' => 24]],
            ['input' => ['mode' => 'vr', 'voltage' => 230, 'resistance' => 100], 'expect' => ['Current' => 2.3, 'Resistance' => 100, 'Power' => 529]],
            ['input' => ['mode' => 'rp', 'resistance' => 6, 'power' => 24], 'expect' => ['Voltage' => 12, 'Current' => 2, 'Power' => 24]],
        ],
    ],

    [
        'slug' => 'density-calculator',
        'name' => 'Density Calculator',
        'category' => 'science',
        'description' => 'Calculate density from mass and volume in g/cm³ and kg/m³.',
        'icon' => 'droplet',
        'featured' => false,
        'keywords' => ['density', 'mass', 'volume', 'specific gravity'],
        'inputs' => [
            ['name' => 'mass', 'label' => 'Mass (g)', 'type' => 'number', 'default' => 200, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'volume', 'label' => 'Volume', 'type' => 'number', 'default' => 25, 'min' => 0, 'step' => 'any', 'required' => true],
            [
                'name' => 'volumeUnit',
                'label' => 'Volume unit',
                'type' => 'select',
                'default' => 'ml',
                'options' => [
                    ['value' => 'ml', 'label' => 'Millilitres (mL)'],
                    ['value' => 'cm3', 'label' => 'Cubic centimetres (cm³)'],
                ],
                'hint' => '1 mL is exactly equal to 1 cm³.',
            ],
        ],
        'compute' => <<<'JS'
            const mass = h.n(v.mass);
            const volume = h.n(v.volume);
            if (mass < 0) throw new Error('Mass cannot be negative.');
            if (volume <= 0) throw new Error('Volume must be greater than zero.');
            const density = mass / volume;
            return [
                { label: 'Density', value: density, unit: 'g/cm³' },
                { label: 'Density (kg/m³)', value: density * 1000, unit: 'kg/m³' },
            ];
        JS,
        'tests' => [
            ['input' => ['mass' => 200, 'volume' => 25, 'volumeUnit' => 'ml'], 'expect' => ['Density' => 8, 'Density (kg/m³)' => 8000]],
            ['input' => ['mass' => 500, 'volume' => 200, 'volumeUnit' => 'cm3'], 'expect' => ['Density' => 2.5, 'Density (kg/m³)' => 2500]],
        ],
    ],

    [
        'slug' => 'molarity-calculator',
        'name' => 'Molarity Calculator',
        'category' => 'science',
        'description' => 'Calculate molarity, moles or the mass of solute needed from volume and molar mass.',
        'icon' => 'flask-conical',
        'featured' => false,
        'keywords' => ['molarity', 'moles', 'solution', 'concentration', 'chemistry'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Calculation',
                'type' => 'select',
                'default' => 'molarity',
                'options' => [
                    ['value' => 'molarity', 'label' => 'Molarity from moles and volume'],
                    ['value' => 'moles', 'label' => 'Moles from molarity and volume'],
                    ['value' => 'mass', 'label' => 'Mass of solute from molarity and volume'],
                ],
            ],
            ['name' => 'moles', 'label' => 'Moles of solute (mol)', 'type' => 'number', 'default' => 0.5, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => 'molarity']],
            ['name' => 'volume', 'label' => 'Volume of solution (L)', 'type' => 'number', 'default' => 2, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'molarity', 'label' => 'Molarity (mol/L)', 'type' => 'number', 'default' => 0.1, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => ['moles', 'mass']]],
            ['name' => 'molarMass', 'label' => 'Molar mass (g/mol)', 'type' => 'number', 'default' => 58.44, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => 'mass']],
        ],
        'compute' => <<<'JS'
            const volume = h.n(v.volume);
            if (volume <= 0) throw new Error('Volume must be greater than zero.');
            if (v.mode === 'molarity') {
                const moles = h.n(v.moles);
                if (moles < 0) throw new Error('Moles cannot be negative.');
                return [
                    { label: 'Molarity', value: moles / volume, unit: 'mol/L' },
                    { label: 'Moles', value: moles, unit: 'mol' },
                ];
            }
            const molarity = h.n(v.molarity);
            if (molarity < 0) throw new Error('Molarity cannot be negative.');
            const moles = molarity * volume;
            if (v.mode === 'moles') {
                return [
                    { label: 'Molarity', value: molarity, unit: 'mol/L' },
                    { label: 'Moles', value: moles, unit: 'mol' },
                ];
            }
            const molarMass = h.n(v.molarMass);
            if (molarMass <= 0) throw new Error('Molar mass must be greater than zero.');
            return [
                { label: 'Molarity', value: molarity, unit: 'mol/L' },
                { label: 'Moles', value: moles, unit: 'mol' },
                { label: 'Mass', value: moles * molarMass, unit: 'g' },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'molarity', 'moles' => 0.5, 'volume' => 2], 'expect' => ['Molarity' => 0.25, 'Moles' => 0.5]],
            ['input' => ['mode' => 'mass', 'molarity' => 0.1, 'volume' => 0.5, 'molarMass' => 58.44], 'expect' => ['Molarity' => 0.1, 'Moles' => 0.05, 'Mass' => 2.922]],
            ['input' => ['mode' => 'moles', 'molarity' => 0.25, 'volume' => 4], 'expect' => ['Moles' => 1, 'Molarity' => 0.25]],
        ],
    ],

    [
        'slug' => 'ph-calculator',
        'name' => 'pH Calculator',
        'category' => 'science',
        'description' => 'Convert between pH and hydrogen ion concentration and classify a solution as acidic, neutral or basic.',
        'icon' => 'test-tube',
        'featured' => false,
        'keywords' => ['ph', 'acidity', 'alkalinity', 'hydrogen ion', 'chemistry'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Calculation',
                'type' => 'select',
                'default' => 'from-concentration',
                'options' => [
                    ['value' => 'from-concentration', 'label' => 'pH from H⁺ concentration'],
                    ['value' => 'from-ph', 'label' => 'H⁺ concentration from pH'],
                    ['value' => 'classify', 'label' => 'Classify a pH value'],
                ],
            ],
            ['name' => 'concentration', 'label' => 'H⁺ concentration (mol/L)', 'type' => 'number', 'default' => 0.001, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => 'from-concentration']],
            ['name' => 'ph', 'label' => 'pH value', 'type' => 'number', 'default' => 7, 'min' => 0, 'max' => 14, 'step' => 'any', 'showIf' => ['mode' => ['from-ph', 'classify']]],
        ],
        'compute' => <<<'JS'
            let ph;
            let concentration;
            if (v.mode === 'from-concentration') {
                concentration = h.n(v.concentration);
                if (!(concentration > 0)) throw new Error('The H⁺ concentration must be greater than zero.');
                ph = -Math.log10(concentration);
            } else {
                ph = h.n(v.ph);
                if (ph < 0 || ph > 14) throw new Error('Enter a pH value between 0 and 14.');
                concentration = Math.pow(10, -ph);
            }
            const classification = ph < 7 - 1e-9 ? 'Acidic' : ph > 7 + 1e-9 ? 'Basic' : 'Neutral';
            return [
                { label: 'pH', value: ph, decimals: 2 },
                { label: 'H⁺ concentration', value: concentration, unit: 'mol/L' },
                { label: 'Classification', value: classification },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'from-concentration', 'concentration' => 0.001], 'expect' => ['pH' => 3, 'H⁺ concentration' => 0.001, 'Classification' => 'Acidic']],
            ['input' => ['mode' => 'from-ph', 'ph' => 7], 'expect' => ['pH' => 7, 'H⁺ concentration' => 1e-7, 'Classification' => 'Neutral']],
            ['input' => ['mode' => 'from-ph', 'ph' => 4.5], 'expect' => ['pH' => 4.5, 'H⁺ concentration' => 3.1622776601683795e-5, 'Classification' => 'Acidic']],
            ['input' => ['mode' => 'classify', 'ph' => 10], 'expect' => ['pH' => 10, 'Classification' => 'Basic']],
        ],
    ],

    [
        'slug' => 'speed-distance-time-calculator',
        'name' => 'Speed, Distance and Time Calculator',
        'category' => 'science',
        'description' => 'Calculate speed, distance or time and convert the result to m/s, km/h and mph.',
        'icon' => 'gauge',
        'featured' => false,
        'keywords' => ['speed', 'distance', 'time', 'velocity', 'mph'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Find',
                'type' => 'select',
                'default' => 'speed',
                'options' => [
                    ['value' => 'speed', 'label' => 'Speed from distance and time'],
                    ['value' => 'distance', 'label' => 'Distance from speed and time'],
                    ['value' => 'time', 'label' => 'Time from distance and speed'],
                ],
            ],
            ['name' => 'distance', 'label' => 'Distance', 'type' => 'number', 'default' => 100, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => ['speed', 'time']]],
            [
                'name' => 'distanceUnit',
                'label' => 'Distance unit',
                'type' => 'select',
                'default' => 'km',
                'options' => [
                    ['value' => 'm', 'label' => 'Metres (m)'],
                    ['value' => 'km', 'label' => 'Kilometres (km)'],
                    ['value' => 'mi', 'label' => 'Miles (mi)'],
                ],
            ],
            ['name' => 'time', 'label' => 'Time', 'type' => 'number', 'default' => 2, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => ['speed', 'distance']]],
            [
                'name' => 'timeUnit',
                'label' => 'Time unit',
                'type' => 'select',
                'default' => 'h',
                'options' => [
                    ['value' => 's', 'label' => 'Seconds (s)'],
                    ['value' => 'min', 'label' => 'Minutes (min)'],
                    ['value' => 'h', 'label' => 'Hours (h)'],
                ],
            ],
            ['name' => 'speed', 'label' => 'Speed', 'type' => 'number', 'default' => 50, 'min' => 0, 'step' => 'any', 'showIf' => ['mode' => ['distance', 'time']]],
            [
                'name' => 'speedUnit',
                'label' => 'Speed unit',
                'type' => 'select',
                'default' => 'km/h',
                'options' => [
                    ['value' => 'm/s', 'label' => 'Metres per second (m/s)'],
                    ['value' => 'km/h', 'label' => 'Kilometres per hour (km/h)'],
                    ['value' => 'mph', 'label' => 'Miles per hour (mph)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const distanceFactors = { m: 1, km: 1000, mi: 1609.344 };
            const timeFactors = { s: 1, min: 60, h: 3600 };
            const speedFactors = { 'm/s': 1, 'km/h': 1000 / 3600, mph: 1609.344 / 3600 };
            const distanceFactor = distanceFactors[v.distanceUnit] || 1;
            const timeFactor = timeFactors[v.timeUnit] || 1;
            const speedFactor = speedFactors[v.speedUnit] || 1;
            let metres;
            let seconds;
            let mps;
            if (v.mode === 'distance') {
                mps = h.n(v.speed) * speedFactor;
                seconds = h.n(v.time) * timeFactor;
                if (!(mps > 0) || !(seconds > 0)) throw new Error('Speed and time must be greater than zero.');
                metres = mps * seconds;
            } else if (v.mode === 'time') {
                metres = h.n(v.distance) * distanceFactor;
                mps = h.n(v.speed) * speedFactor;
                if (!(metres > 0) || !(mps > 0)) throw new Error('Distance and speed must be greater than zero.');
                seconds = metres / mps;
            } else {
                metres = h.n(v.distance) * distanceFactor;
                seconds = h.n(v.time) * timeFactor;
                if (!(metres > 0) || !(seconds > 0)) throw new Error('Distance and time must be greater than zero.');
                mps = metres / seconds;
            }
            const results = [
                { label: 'Speed (m/s)', value: mps, unit: 'm/s' },
                { label: 'Speed (km/h)', value: mps * 3.6, unit: 'km/h' },
                { label: 'Speed (mph)', value: mps * 2.2369362920544025, unit: 'mph' },
            ];
            if (v.mode === 'distance') {
                results.push({ label: 'Distance', value: metres / distanceFactor, unit: v.distanceUnit });
            }
            if (v.mode === 'time') {
                results.push({ label: 'Time', value: seconds / timeFactor, unit: v.timeUnit });
            }
            return results;
        JS,
        'tests' => [
            ['input' => ['mode' => 'speed', 'distance' => 100, 'distanceUnit' => 'km', 'time' => 2, 'timeUnit' => 'h'], 'expect' => ['Speed (m/s)' => 13.88888888888889, 'Speed (km/h)' => 50, 'Speed (mph)' => 31.068559611866696]],
            ['input' => ['mode' => 'distance', 'speed' => 10, 'speedUnit' => 'm/s', 'time' => 30, 'timeUnit' => 's', 'distanceUnit' => 'm'], 'expect' => ['Distance' => 300, 'Speed (m/s)' => 10, 'Speed (km/h)' => 36]],
            ['input' => ['mode' => 'time', 'distance' => 100, 'distanceUnit' => 'km', 'speed' => 50, 'speedUnit' => 'km/h'], 'expect' => ['Time' => 2, 'Speed (m/s)' => 13.88888888888889, 'Speed (mph)' => 31.068559611866696]],
        ],
    ],

    [
        'slug' => 'force-calculator',
        'name' => 'Force Calculator',
        'category' => 'science',
        'description' => 'Calculate force from mass and acceleration, plus the object\'s weight on Earth.',
        'icon' => 'weight',
        'featured' => false,
        'keywords' => ['force', 'newton', 'mass', 'acceleration', 'weight'],
        'inputs' => [
            ['name' => 'mass', 'label' => 'Mass (kg)', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'acceleration', 'label' => 'Acceleration (m/s²)', 'type' => 'number', 'default' => 2, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const mass = h.n(v.mass);
            const acceleration = h.n(v.acceleration);
            if (mass < 0) throw new Error('Mass cannot be negative.');
            return [
                { label: 'Force', value: mass * acceleration, unit: 'N' },
                { label: 'Weight on Earth', value: mass * 9.80665, unit: 'N' },
            ];
        JS,
        'tests' => [
            ['input' => ['mass' => 10, 'acceleration' => 2], 'expect' => ['Force' => 20, 'Weight on Earth' => 98.0665]],
            ['input' => ['mass' => 5, 'acceleration' => 9.81], 'expect' => ['Force' => 49.05, 'Weight on Earth' => 49.03325]],
            ['input' => ['mass' => 0, 'acceleration' => 5], 'expect' => ['Force' => 0, 'Weight on Earth' => 0]],
        ],
    ],

    [
        'slug' => 'half-life-calculator',
        'name' => 'Half-Life Calculator',
        'category' => 'science',
        'description' => 'Calculate the remaining amount after radioactive decay, the half-lives elapsed and the percentage left.',
        'icon' => 'atom',
        'featured' => false,
        'keywords' => ['half life', 'radioactive decay', 'half-life', 'chemistry', 'physics'],
        'inputs' => [
            ['name' => 'initialAmount', 'label' => 'Initial amount', 'type' => 'number', 'default' => 100, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'halfLife', 'label' => 'Half-life', 'type' => 'number', 'default' => 5, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'elapsed', 'label' => 'Elapsed time', 'type' => 'number', 'default' => 15, 'min' => 0, 'step' => 'any', 'required' => true],
            [
                'name' => 'unit',
                'label' => 'Time unit',
                'type' => 'select',
                'default' => 'days',
                'options' => [
                    ['value' => 'seconds', 'label' => 'Seconds'],
                    ['value' => 'minutes', 'label' => 'Minutes'],
                    ['value' => 'hours', 'label' => 'Hours'],
                    ['value' => 'days', 'label' => 'Days'],
                    ['value' => 'years', 'label' => 'Years'],
                ],
                'hint' => 'The half-life and elapsed time use the same unit.',
            ],
        ],
        'compute' => <<<'JS'
            const initial = h.n(v.initialAmount);
            const halfLife = h.n(v.halfLife);
            const elapsed = h.n(v.elapsed);
            if (!(initial > 0)) throw new Error('The initial amount must be greater than zero.');
            if (!(halfLife > 0)) throw new Error('The half-life must be greater than zero.');
            if (elapsed < 0) throw new Error('The elapsed time cannot be negative.');
            const halfLives = elapsed / halfLife;
            const remaining = initial * Math.pow(0.5, halfLives);
            return [
                { label: 'Remaining amount', value: remaining },
                { label: 'Half-lives elapsed', value: halfLives, decimals: 2 },
                { label: 'Percentage remaining', value: (remaining / initial) * 100, unit: '%' },
            ];
        JS,
        'tests' => [
            ['input' => ['initialAmount' => 100, 'halfLife' => 5, 'elapsed' => 15, 'unit' => 'days'], 'expect' => ['Remaining amount' => 12.5, 'Half-lives elapsed' => 3, 'Percentage remaining' => 12.5]],
            ['input' => ['initialAmount' => 80, 'halfLife' => 10, 'elapsed' => 5, 'unit' => 'years'], 'expect' => ['Remaining amount' => 56.568542494923804, 'Half-lives elapsed' => 0.5, 'Percentage remaining' => 70.71067811865476]],
            ['input' => ['initialAmount' => 1, 'halfLife' => 5730, 'elapsed' => 11460, 'unit' => 'years'], 'expect' => ['Remaining amount' => 0.25, 'Half-lives elapsed' => 2, 'Percentage remaining' => 25]],
        ],
    ],
];
