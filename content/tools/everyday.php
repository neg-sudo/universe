<?php
/**
 * UniVerse — Everyday
 * 8 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'fuel-cost-calculator',
        'name' => 'Fuel Cost Calculator',
        'category' => 'everyday',
        'description' => 'Estimate the fuel needed and total cost for a trip from distance, efficiency and price.',
        'icon' => 'fuel',
        'featured' => true,
        'keywords' => ['fuel', 'petrol', 'gas', 'trip cost', 'consumption'],
        'inputs' => [
            ['name' => 'distance', 'label' => 'Distance', 'type' => 'number', 'default' => 500, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Kilometres'],
            ['name' => 'efficiency', 'label' => 'Fuel efficiency', 'type' => 'number', 'default' => 8, 'min' => 0, 'step' => 'any', 'required' => true],
            [
                'name' => 'unit',
                'label' => 'Efficiency unit',
                'type' => 'select',
                'default' => 'l100',
                'options' => [
                    ['value' => 'l100', 'label' => 'Litres per 100 km (L/100 km)'],
                    ['value' => 'kmpl', 'label' => 'Kilometres per litre (km/L)'],
                ],
            ],
            ['name' => 'price', 'label' => 'Fuel price per litre', 'type' => 'number', 'default' => 1.5, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const distance = h.n(v.distance);
            const efficiency = h.n(v.efficiency);
            const price = h.n(v.price);
            if (distance <= 0) throw new Error('Distance must be greater than zero.');
            if (efficiency <= 0) throw new Error('Fuel efficiency must be greater than zero.');
            if (price < 0) throw new Error('Fuel price cannot be negative.');
            const litres = v.unit === 'kmpl'
                ? distance / efficiency
                : (distance / 100) * efficiency;
            const cost = litres * price;
            return [
                { label: 'Fuel needed', value: litres, unit: 'L', decimals: 2 },
                { label: 'Total cost', value: cost, decimals: 2 },
                { label: 'Cost per 100 km', value: (cost / distance) * 100, decimals: 2 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['distance' => 500, 'efficiency' => 8, 'unit' => 'l100', 'price' => 1.5],
                'expect' => ['Fuel needed' => 40, 'Total cost' => 60, 'Cost per 100 km' => 12],
            ],
            [
                'input' => ['distance' => 300, 'efficiency' => 15, 'unit' => 'kmpl', 'price' => 2],
                'expect' => ['Fuel needed' => 20, 'Total cost' => 40, 'Cost per 100 km' => 13.333333333333334],
            ],
            [
                'input' => ['distance' => 100, 'efficiency' => 5, 'unit' => 'l100', 'price' => 1.8],
                'expect' => ['Fuel needed' => 5, 'Total cost' => 9, 'Cost per 100 km' => 9],
            ],
        ],
    ],

    [
        'slug' => 'electricity-cost-calculator',
        'name' => 'Electricity Cost Calculator',
        'category' => 'everyday',
        'description' => 'Work out how much an appliance costs to run from its wattage, hours and energy price.',
        'icon' => 'zap',
        'featured' => false,
        'keywords' => ['electricity', 'energy', 'kwh', 'appliance', 'power cost'],
        'inputs' => [
            ['name' => 'power', 'label' => 'Appliance power', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Watts'],
            ['name' => 'hours', 'label' => 'Hours per day', 'type' => 'number', 'default' => 5, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'days', 'label' => 'Number of days', 'type' => 'number', 'default' => 30, 'min' => 1, 'step' => 1, 'required' => true],
            ['name' => 'price', 'label' => 'Price per kWh', 'type' => 'number', 'default' => 0.2, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const power = h.n(v.power);
            const hours = h.n(v.hours);
            const days = h.n(v.days);
            const price = h.n(v.price);
            if (power <= 0) throw new Error('Appliance power must be greater than zero.');
            if (hours < 0) throw new Error('Hours per day cannot be negative.');
            if (days <= 0) throw new Error('The number of days must be greater than zero.');
            if (price < 0) throw new Error('Price per kWh cannot be negative.');
            const kwh = (power / 1000) * hours * days;
            const cost = kwh * price;
            return [
                { label: 'Energy used', value: kwh, unit: 'kWh', decimals: 3 },
                { label: 'Total cost', value: cost, decimals: 2 },
                { label: 'Cost per day', value: cost / days, decimals: 2 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['power' => 1000, 'hours' => 5, 'days' => 30, 'price' => 0.2],
                'expect' => ['Energy used' => 150, 'Total cost' => 30, 'Cost per day' => 1],
            ],
            [
                'input' => ['power' => 60, 'hours' => 8, 'days' => 30, 'price' => 0.25],
                'expect' => ['Energy used' => 14.4, 'Total cost' => 3.6, 'Cost per day' => 0.12],
            ],
            [
                'input' => ['power' => 2000, 'hours' => 1, 'days' => 7, 'price' => 0.3],
                'expect' => ['Energy used' => 14, 'Total cost' => 4.2, 'Cost per day' => 0.6],
            ],
        ],
    ],

    [
        'slug' => 'paint-coverage-calculator',
        'name' => 'Paint Coverage Calculator',
        'category' => 'everyday',
        'description' => 'Calculate wall area, paint needed and five-litre cans from room dimensions and coats.',
        'icon' => 'paintbrush',
        'featured' => false,
        'keywords' => ['paint', 'coverage', 'wall area', 'litres', 'decorating'],
        'inputs' => [
            ['name' => 'length', 'label' => 'Room length', 'type' => 'number', 'default' => 5, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Metres'],
            ['name' => 'width', 'label' => 'Room width', 'type' => 'number', 'default' => 4, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'height', 'label' => 'Wall height', 'type' => 'number', 'default' => 2.5, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'coats', 'label' => 'Number of coats', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 10, 'step' => 1, 'required' => true],
            ['name' => 'coverage', 'label' => 'Coverage per litre', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Square metres per litre'],
            ['name' => 'openings', 'label' => 'Doors and windows area', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 'any', 'hint' => 'Square metres, optional'],
        ],
        'compute' => <<<'JS'
            const length = h.n(v.length);
            const width = h.n(v.width);
            const height = h.n(v.height);
            const coats = Math.floor(h.n(v.coats));
            const coverage = h.n(v.coverage);
            const openings = h.n(v.openings);
            if (length <= 0 || width <= 0 || height <= 0) {
                throw new Error('Room dimensions must be greater than zero.');
            }
            if (coats < 1 || coats > 10) throw new Error('Choose between 1 and 10 coats.');
            if (coverage <= 0) throw new Error('Coverage must be greater than zero.');
            if (openings < 0) throw new Error('The openings area cannot be negative.');
            const wallArea = 2 * (length + width) * height;
            if (openings >= wallArea) {
                throw new Error('The openings area must be smaller than the wall area.');
            }
            const paintable = (wallArea - openings) * coats;
            const litres = Math.ceil(paintable / coverage);
            return [
                { label: 'Wall area', value: wallArea, unit: 'm²', decimals: 2 },
                { label: 'Paintable area', value: paintable, unit: 'm²', decimals: 2 },
                { label: 'Paint needed', value: litres, unit: 'L', decimals: 0 },
                { label: 'Cans (5 L)', value: Math.ceil(litres / 5), decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['length' => 5, 'width' => 4, 'height' => 2.5, 'coats' => 2, 'coverage' => 10, 'openings' => 0],
                'expect' => ['Wall area' => 45, 'Paintable area' => 90, 'Paint needed' => 9, 'Cans (5 L)' => 2],
            ],
            [
                'input' => ['length' => 4, 'width' => 3, 'height' => 2.4, 'coats' => 1, 'coverage' => 12, 'openings' => 4],
                'expect' => ['Wall area' => 33.6, 'Paintable area' => 29.6, 'Paint needed' => 3, 'Cans (5 L)' => 1],
            ],
            [
                'input' => ['length' => 6, 'width' => 5, 'height' => 3, 'coats' => 2, 'coverage' => 10, 'openings' => 10],
                'expect' => ['Wall area' => 66, 'Paintable area' => 112, 'Paint needed' => 12, 'Cans (5 L)' => 3],
            ],
        ],
    ],

    [
        'slug' => 'tile-calculator',
        'name' => 'Tile Calculator',
        'category' => 'everyday',
        'description' => 'Find how many tiles and boxes you need for a floor, including wastage.',
        'icon' => 'grid',
        'featured' => false,
        'keywords' => ['tiles', 'flooring', 'area', 'wastage', 'boxes'],
        'inputs' => [
            ['name' => 'roomLength', 'label' => 'Room length', 'type' => 'number', 'default' => 4, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'roomWidth', 'label' => 'Room width', 'type' => 'number', 'default' => 3, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'tileLength', 'label' => 'Tile length', 'type' => 'number', 'default' => 0.3, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Use the same unit as the room'],
            ['name' => 'tileWidth', 'label' => 'Tile width', 'type' => 'number', 'default' => 0.3, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'wastage', 'label' => 'Wastage', 'type' => 'number', 'default' => 10, 'min' => 0, 'max' => 100, 'step' => 'any', 'required' => true, 'hint' => 'Percent for cuts and breakages'],
            ['name' => 'tilesPerBox', 'label' => 'Tiles per box', 'type' => 'number', 'default' => 20, 'min' => 0, 'step' => 1, 'hint' => 'Leave as 0 if unknown'],
        ],
        'compute' => <<<'JS'
            const roomLength = h.n(v.roomLength);
            const roomWidth = h.n(v.roomWidth);
            const tileLength = h.n(v.tileLength);
            const tileWidth = h.n(v.tileWidth);
            const wastage = h.n(v.wastage);
            const perBox = h.n(v.tilesPerBox);
            if (roomLength <= 0 || roomWidth <= 0) {
                throw new Error('Room dimensions must be greater than zero.');
            }
            if (tileLength <= 0 || tileWidth <= 0) {
                throw new Error('Tile dimensions must be greater than zero.');
            }
            if (wastage < 0) throw new Error('Wastage cannot be negative.');
            if (perBox < 0) throw new Error('Tiles per box cannot be negative.');
            const floorArea = roomLength * roomWidth;
            const tileArea = tileLength * tileWidth;
            const tiles = Math.ceil((floorArea / tileArea) * (1 + wastage / 100));
            return [
                { label: 'Floor area', value: floorArea, unit: 'm²', decimals: 2 },
                { label: 'Tile area', value: tileArea, unit: 'm²', decimals: 4 },
                { label: 'Tiles needed', value: tiles, decimals: 0 },
                { label: 'Boxes needed', value: perBox > 0 ? Math.ceil(tiles / perBox) : 'Not specified' },
            ];
        JS,
        'tests' => [
            [
                'input' => ['roomLength' => 4, 'roomWidth' => 3, 'tileLength' => 0.3, 'tileWidth' => 0.3, 'wastage' => 10, 'tilesPerBox' => 20],
                'expect' => ['Floor area' => 12, 'Tile area' => 0.09, 'Tiles needed' => 147, 'Boxes needed' => 8],
            ],
            [
                'input' => ['roomLength' => 5, 'roomWidth' => 4, 'tileLength' => 0.5, 'tileWidth' => 0.5, 'wastage' => 0, 'tilesPerBox' => 10],
                'expect' => ['Floor area' => 20, 'Tile area' => 0.25, 'Tiles needed' => 80, 'Boxes needed' => 8],
            ],
            [
                'input' => ['roomLength' => 3, 'roomWidth' => 2, 'tileLength' => 0.6, 'tileWidth' => 0.6, 'wastage' => 5, 'tilesPerBox' => 12],
                'expect' => ['Floor area' => 6, 'Tile area' => 0.36, 'Tiles needed' => 18, 'Boxes needed' => 2],
            ],
        ],
    ],

    [
        'slug' => 'sleep-cycle-calculator',
        'name' => 'Sleep Cycle Calculator',
        'category' => 'everyday',
        'description' => 'Find bedtimes that let you wake up between complete 90-minute sleep cycles.',
        'icon' => 'bed',
        'featured' => true,
        'keywords' => ['sleep', 'bedtime', 'cycles', 'wake up', 'rest'],
        'inputs' => [
            ['name' => 'wakeTime', 'label' => 'Wake-up time', 'type' => 'time', 'default' => '07:00', 'required' => true, 'hint' => '24-hour time'],
            ['name' => 'fallAsleep', 'label' => 'Minutes to fall asleep', 'type' => 'number', 'default' => 15, 'min' => 0, 'step' => 1, 'required' => true],
            ['name' => 'cycleLength', 'label' => 'Sleep cycle length', 'type' => 'number', 'default' => 90, 'min' => 1, 'step' => 1, 'required' => true, 'hint' => 'Minutes; 90 is typical'],
        ],
        'compute' => <<<'JS'
            const match = String(v.wakeTime).trim().match(/^(\d{1,2}):(\d{2})$/);
            if (!match) throw new Error('Enter the wake-up time as HH:MM.');
            const hours = Number(match[1]);
            const minutes = Number(match[2]);
            if (hours > 23 || minutes > 59) throw new Error('Enter a valid 24-hour time.');
            const fallAsleep = h.n(v.fallAsleep);
            const cycleLength = h.n(v.cycleLength);
            if (fallAsleep < 0) throw new Error('Minutes to fall asleep cannot be negative.');
            if (cycleLength <= 0) throw new Error('The sleep cycle length must be greater than zero.');
            const pad = (value) => String(value).padStart(2, '0');
            const format = (totalMinutes) => {
                const wrapped = ((totalMinutes % 1440) + 1440) % 1440;
                return `${pad(Math.floor(wrapped / 60))}:${pad(wrapped % 60)}`;
            };
            const wake = hours * 60 + minutes;
            return [6, 5, 4, 3].map((cycles) => ({
                label: `${cycles} cycles`,
                value: format(Math.round(wake - cycles * cycleLength - fallAsleep)),
                hint: `${cycles * cycleLength} minutes of sleep`,
            }));
        JS,
        'tests' => [
            [
                'input' => ['wakeTime' => '08:00', 'fallAsleep' => 15, 'cycleLength' => 90],
                'expect' => ['6 cycles' => '22:45', '5 cycles' => '00:15', '4 cycles' => '01:45', '3 cycles' => '03:15'],
            ],
            [
                'input' => ['wakeTime' => '07:00', 'fallAsleep' => 15, 'cycleLength' => 90],
                'expect' => ['6 cycles' => '21:45', '5 cycles' => '23:15', '4 cycles' => '00:45', '3 cycles' => '02:15'],
            ],
            [
                'input' => ['wakeTime' => '23:30', 'fallAsleep' => 0, 'cycleLength' => 90],
                'expect' => ['6 cycles' => '14:30', '5 cycles' => '16:00', '4 cycles' => '17:30', '3 cycles' => '19:00'],
            ],
        ],
    ],

    [
        'slug' => 'water-intake-calculator',
        'name' => 'Water Intake Calculator',
        'category' => 'everyday',
        'description' => 'Estimate daily water needs from body weight, exercise minutes and climate.',
        'icon' => 'droplets',
        'featured' => false,
        'keywords' => ['water', 'hydration', 'intake', 'cups', 'health'],
        'inputs' => [
            ['name' => 'weight', 'label' => 'Body weight', 'type' => 'number', 'default' => 70, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Kilograms'],
            ['name' => 'activity', 'label' => 'Activity time', 'type' => 'number', 'default' => 30, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Minutes of exercise per day'],
            [
                'name' => 'climate',
                'label' => 'Climate',
                'type' => 'select',
                'default' => 'temperate',
                'options' => [
                    ['value' => 'temperate', 'label' => 'Temperate'],
                    ['value' => 'hot', 'label' => 'Hot'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const weight = h.n(v.weight);
            const activity = h.n(v.activity);
            if (weight <= 0) throw new Error('Body weight must be greater than zero.');
            if (activity < 0) throw new Error('Activity time cannot be negative.');
            const millilitres = weight * 35 + (activity / 30) * 350 + (v.climate === 'hot' ? 500 : 0);
            const litres = millilitres / 1000;
            return [
                { label: 'Daily water', value: litres, unit: 'L', decimals: 2 },
                { label: 'Cups (250 ml)', value: litres / 0.25, decimals: 1 },
                { label: 'Millilitres', value: millilitres, unit: 'ml', decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['weight' => 70, 'activity' => 30, 'climate' => 'temperate'],
                'expect' => ['Daily water' => 2.8, 'Cups (250 ml)' => 11.2, 'Millilitres' => 2800],
            ],
            [
                'input' => ['weight' => 80, 'activity' => 60, 'climate' => 'hot'],
                'expect' => ['Daily water' => 4, 'Cups (250 ml)' => 16, 'Millilitres' => 4000],
            ],
            [
                'input' => ['weight' => 60, 'activity' => 0, 'climate' => 'temperate'],
                'expect' => ['Daily water' => 2.1, 'Cups (250 ml)' => 8.4, 'Millilitres' => 2100],
            ],
        ],
    ],

    [
        'slug' => 'pace-calculator',
        'name' => 'Running Pace Calculator',
        'category' => 'everyday',
        'description' => 'Convert a run into pace and speed, with predicted 5K, 10K, half and full marathon times.',
        'icon' => 'timer',
        'featured' => false,
        'keywords' => ['pace', 'running', 'speed', 'marathon', '5k'],
        'inputs' => [
            ['name' => 'distance', 'label' => 'Distance', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Kilometres'],
            ['name' => 'hours', 'label' => 'Hours', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 1, 'required' => true],
            ['name' => 'minutes', 'label' => 'Minutes', 'type' => 'number', 'default' => 50, 'min' => 0, 'max' => 59, 'step' => 1, 'required' => true],
            ['name' => 'seconds', 'label' => 'Seconds', 'type' => 'number', 'default' => 0, 'min' => 0, 'max' => 59, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const distance = h.n(v.distance);
            const hours = h.n(v.hours);
            const minutes = h.n(v.minutes);
            const seconds = h.n(v.seconds);
            if (distance <= 0) throw new Error('Distance must be greater than zero.');
            if (hours < 0 || minutes < 0 || seconds < 0) {
                throw new Error('Time values cannot be negative.');
            }
            if (minutes >= 60 || seconds >= 60) {
                throw new Error('Minutes and seconds must be less than 60.');
            }
            const totalSeconds = hours * 3600 + minutes * 60 + seconds;
            if (totalSeconds <= 0) throw new Error('Enter a time greater than zero.');
            const paceSeconds = totalSeconds / distance;
            const pad = (value) => String(value).padStart(2, '0');
            const formatPace = (value) => {
                const rounded = Math.round(value);
                return `${Math.floor(rounded / 60)}:${pad(rounded % 60)}`;
            };
            const formatTime = (value) => {
                const rounded = Math.round(value);
                const h = Math.floor(rounded / 3600);
                const m = Math.floor((rounded % 3600) / 60);
                const s = rounded % 60;
                return h > 0 ? `${h}:${pad(m)}:${pad(s)}` : `${m}:${pad(s)}`;
            };
            return [
                { label: 'Pace', value: formatPace(paceSeconds), unit: '/km' },
                { label: 'Speed', value: distance / (totalSeconds / 3600), unit: 'km/h', decimals: 2 },
                { label: '5K time', value: formatTime(paceSeconds * 5) },
                { label: '10K time', value: formatTime(paceSeconds * 10) },
                { label: 'Half marathon', value: formatTime(paceSeconds * 21.0975) },
                { label: 'Marathon', value: formatTime(paceSeconds * 42.195) },
            ];
        JS,
        'tests' => [
            [
                'input' => ['distance' => 10, 'hours' => 0, 'minutes' => 50, 'seconds' => 0],
                'expect' => ['Pace' => '5:00', 'Speed' => 12, '5K time' => '25:00', '10K time' => '50:00'],
            ],
            [
                'input' => ['distance' => 5, 'hours' => 0, 'minutes' => 25, 'seconds' => 0],
                'expect' => ['Pace' => '5:00', 'Speed' => 12, '5K time' => '25:00'],
            ],
            [
                'input' => ['distance' => 21.0975, 'hours' => 1, 'minutes' => 45, 'seconds' => 29],
                'expect' => ['Pace' => '5:00', 'Half marathon' => '1:45:29'],
            ],
        ],
    ],

    [
        'slug' => 'unit-price-comparator',
        'name' => 'Unit Price Comparator',
        'category' => 'everyday',
        'description' => 'Compare two product prices by quantity to see which has the lower unit price.',
        'icon' => 'shopping-cart',
        'featured' => false,
        'keywords' => ['unit price', 'compare', 'shopping', 'value', 'grocery'],
        'inputs' => [
            ['name' => 'price1', 'label' => 'Product A price', 'type' => 'number', 'default' => 3, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'quantity1', 'label' => 'Product A quantity', 'type' => 'number', 'default' => 500, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'price2', 'label' => 'Product B price', 'type' => 'number', 'default' => 5.5, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'quantity2', 'label' => 'Product B quantity', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'unit', 'label' => 'Quantity unit', 'type' => 'text', 'default' => 'g', 'required' => true, 'hint' => 'For example g, kg, ml or sheets'],
        ],
        'compute' => <<<'JS'
            const price1 = h.n(v.price1);
            const quantity1 = h.n(v.quantity1);
            const price2 = h.n(v.price2);
            const quantity2 = h.n(v.quantity2);
            if (price1 < 0 || price2 < 0) throw new Error('Prices cannot be negative.');
            if (quantity1 <= 0 || quantity2 <= 0) {
                throw new Error('Both quantities must be greater than zero.');
            }
            const unit1 = price1 / quantity1;
            const unit2 = price2 / quantity2;
            const unitLabel = String(v.unit || 'unit').trim() || 'unit';
            let better;
            if (Math.abs(unit1 - unit2) < 1e-12) better = 'Same price';
            else if (unit1 < unit2) better = 'First product';
            else better = 'Second product';
            return [
                { label: 'Price per unit (A)', value: unit1, unit: `per ${unitLabel}`, decimals: 6 },
                { label: 'Price per unit (B)', value: unit2, unit: `per ${unitLabel}`, decimals: 6 },
                { label: 'Better value', value: better },
            ];
        JS,
        'tests' => [
            [
                'input' => ['price1' => 3, 'quantity1' => 500, 'price2' => 5.5, 'quantity2' => 1000, 'unit' => 'g'],
                'expect' => ['Price per unit (A)' => 0.006, 'Price per unit (B)' => 0.0055, 'Better value' => 'Second product'],
            ],
            [
                'input' => ['price1' => 2, 'quantity1' => 500, 'price2' => 4, 'quantity2' => 1000, 'unit' => 'g'],
                'expect' => ['Price per unit (A)' => 0.004, 'Price per unit (B)' => 0.004, 'Better value' => 'Same price'],
            ],
            [
                'input' => ['price1' => 10, 'quantity1' => 2, 'price2' => 12, 'quantity2' => 3, 'unit' => 'kg'],
                'expect' => ['Price per unit (A)' => 5, 'Price per unit (B)' => 4, 'Better value' => 'Second product'],
            ],
        ],
    ],
];
