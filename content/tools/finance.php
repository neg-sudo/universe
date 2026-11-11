<?php
/**
 * UtilityWorld — Finance
 * 12 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'loan-payment-calculator',
        'name' => 'Loan Payment Calculator',
        'category' => 'finance',
        'description' => 'Estimate the monthly payment, total interest and total cost of a loan.',
        'icon' => 'landmark',
        'featured' => true,
        'keywords' => ['loan', 'mortgage', 'payment', 'amortization', 'interest'],
        'inputs' => [
            ['name' => 'principal', 'label' => 'Loan amount', 'type' => 'number', 'default' => 200000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate', 'type' => 'number', 'default' => 6, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Percentage per year, for example 6.'],
            ['name' => 'term', 'label' => 'Loan term', 'type' => 'number', 'default' => 30, 'min' => 0, 'step' => 'any', 'required' => true],
            [
                'name' => 'termUnit',
                'label' => 'Term unit',
                'type' => 'select',
                'default' => 'years',
                'options' => [
                    ['value' => 'years', 'label' => 'Years'],
                    ['value' => 'months', 'label' => 'Months'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const principal = h.n(v.principal);
            const annualRate = h.n(v.rate);
            const term = h.n(v.term);
            if (principal <= 0) throw new Error('The loan amount must be greater than zero.');
            if (annualRate < 0) throw new Error('The interest rate cannot be negative.');
            if (term <= 0) throw new Error('The loan term must be greater than zero.');
            const months = v.termUnit === 'months' ? Math.round(term) : Math.round(term * 12);
            if (months < 1) throw new Error('The loan term must be at least one month.');
            const monthlyRate = annualRate / 12 / 100;
            const monthly = monthlyRate === 0
                ? principal / months
                : (principal * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -months));
            const totalPaid = monthly * months;
            return [
                { label: 'Monthly payment', value: monthly },
                { label: 'Total paid', value: totalPaid },
                { label: 'Total interest', value: totalPaid - principal },
                { label: 'Number of payments', value: months, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['principal' => 200000, 'rate' => 6, 'term' => 30, 'termUnit' => 'years'], 'expect' => [
                'Monthly payment' => 1199.1010503,
                'Total paid' => 431676.378,
                'Total interest' => 231676.378,
            ]],
            ['input' => ['principal' => 10000, 'rate' => 5, 'term' => 5, 'termUnit' => 'years'], 'expect' => [
                'Monthly payment' => 188.7123364401,
                'Total paid' => 11322.7401864066,
                'Total interest' => 1322.7401864066,
            ]],
            ['input' => ['principal' => 12000, 'rate' => 6, 'term' => 24, 'termUnit' => 'months'], 'expect' => [
                'Monthly payment' => 531.8473230331,
                'Total paid' => 12764.3357527943,
                'Number of payments' => 24,
            ]],
        ],
    ],

    [
        'slug' => 'compound-interest-calculator',
        'name' => 'Compound Interest Calculator',
        'category' => 'finance',
        'description' => 'See how savings grow with compound interest and the effective annual rate.',
        'icon' => 'piggy-bank',
        'featured' => true,
        'keywords' => ['compound interest', 'savings', 'investment', 'growth', 'ear'],
        'inputs' => [
            ['name' => 'principal', 'label' => 'Starting principal', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate', 'type' => 'number', 'default' => 5, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Percentage per year.'],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
            [
                'name' => 'frequency',
                'label' => 'Compounding frequency',
                'type' => 'select',
                'default' => 'annually',
                'options' => [
                    ['value' => 'annually', 'label' => 'Annually'],
                    ['value' => 'semiannually', 'label' => 'Semiannually'],
                    ['value' => 'quarterly', 'label' => 'Quarterly'],
                    ['value' => 'monthly', 'label' => 'Monthly'],
                    ['value' => 'daily', 'label' => 'Daily'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const principal = h.n(v.principal);
            const annualRate = h.n(v.rate);
            const years = h.n(v.years);
            if (principal < 0) throw new Error('The principal cannot be negative.');
            if (annualRate < 0) throw new Error('The interest rate cannot be negative.');
            if (years < 0) throw new Error('The number of years cannot be negative.');
            const periods = v.frequency === 'semiannually' ? 2
                : v.frequency === 'quarterly' ? 4
                : v.frequency === 'monthly' ? 12
                : v.frequency === 'daily' ? 365
                : 1;
            const rate = annualRate / 100;
            const finalBalance = principal * Math.pow(1 + rate / periods, periods * years);
            const interest = finalBalance - principal;
            const effectiveRate = (Math.pow(1 + rate / periods, periods) - 1) * 100;
            return [
                { label: 'Final balance', value: finalBalance },
                { label: 'Total interest', value: interest },
                { label: 'Effective annual rate', value: effectiveRate, unit: '%' },
            ];
        JS,
        'tests' => [
            ['input' => ['principal' => 1000, 'rate' => 5, 'years' => 10, 'frequency' => 'annually'], 'expect' => [
                'Final balance' => 1628.8946268,
                'Total interest' => 628.8946268,
                'Effective annual rate' => 5,
            ]],
            ['input' => ['principal' => 1000, 'rate' => 5, 'years' => 10, 'frequency' => 'quarterly'], 'expect' => [
                'Final balance' => 1643.619463487,
                'Total interest' => 643.619463487,
                'Effective annual rate' => 5.0945336914,
            ]],
            ['input' => ['principal' => 5000, 'rate' => 4, 'years' => 3, 'frequency' => 'monthly'], 'expect' => [
                'Final balance' => 5636.3593725896,
                'Total interest' => 636.3593725896,
                'Effective annual rate' => 4.074154292,
            ]],
        ],
    ],

    [
        'slug' => 'simple-interest-calculator',
        'name' => 'Simple Interest Calculator',
        'category' => 'finance',
        'description' => 'Calculate simple interest and the total amount owed over time.',
        'icon' => 'percent',
        'featured' => false,
        'keywords' => ['simple interest', 'interest', 'principal', 'loan'],
        'inputs' => [
            ['name' => 'principal', 'label' => 'Principal', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate', 'type' => 'number', 'default' => 5, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Percentage per year.'],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 3, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const principal = h.n(v.principal);
            const rate = h.n(v.rate);
            const years = h.n(v.years);
            if (principal < 0) throw new Error('The principal cannot be negative.');
            if (rate < 0) throw new Error('The interest rate cannot be negative.');
            if (years < 0) throw new Error('The number of years cannot be negative.');
            const interest = principal * (rate / 100) * years;
            return [
                { label: 'Interest', value: interest },
                { label: 'Total amount', value: principal + interest },
            ];
        JS,
        'tests' => [
            ['input' => ['principal' => 1000, 'rate' => 5, 'years' => 3], 'expect' => ['Interest' => 150, 'Total amount' => 1150]],
            ['input' => ['principal' => 2500, 'rate' => 4.5, 'years' => 2], 'expect' => ['Interest' => 225, 'Total amount' => 2725]],
        ],
    ],

    [
        'slug' => 'tip-calculator',
        'name' => 'Tip Calculator',
        'category' => 'finance',
        'description' => 'Work out the tip, total bill and amount each person pays when splitting.',
        'icon' => 'receipt',
        'featured' => false,
        'keywords' => ['tip', 'gratuity', 'bill', 'split', 'restaurant'],
        'inputs' => [
            ['name' => 'bill', 'label' => 'Bill amount', 'type' => 'number', 'default' => 80, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'tip', 'label' => 'Tip percentage', 'type' => 'number', 'default' => 15, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'people', 'label' => 'Split between', 'type' => 'number', 'default' => 4, 'min' => 1, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const bill = h.n(v.bill);
            const tipPercent = h.n(v.tip);
            const people = Math.floor(h.n(v.people));
            if (bill < 0) throw new Error('The bill cannot be negative.');
            if (tipPercent < 0) throw new Error('The tip percentage cannot be negative.');
            if (!Number.isInteger(people) || people < 1) throw new Error('Split the bill between at least one person.');
            const tip = bill * (tipPercent / 100);
            const total = bill + tip;
            return [
                { label: 'Tip amount', value: tip },
                { label: 'Total bill', value: total },
                { label: 'Per person', value: total / people },
                { label: 'Tip per person', value: tip / people },
            ];
        JS,
        'tests' => [
            ['input' => ['bill' => 80, 'tip' => 15, 'people' => 4], 'expect' => [
                'Tip amount' => 12,
                'Total bill' => 92,
                'Per person' => 23,
            ]],
            ['input' => ['bill' => 45.5, 'tip' => 20, 'people' => 2], 'expect' => [
                'Tip amount' => 9.1,
                'Total bill' => 54.6,
                'Per person' => 27.3,
            ]],
        ],
    ],

    [
        'slug' => 'discount-calculator',
        'name' => 'Discount Calculator',
        'category' => 'finance',
        'description' => 'Find the discount amount, final price and money saved on a purchase.',
        'icon' => 'tag',
        'featured' => false,
        'keywords' => ['discount', 'sale', 'price', 'savings', 'percent off'],
        'inputs' => [
            ['name' => 'price', 'label' => 'Original price', 'type' => 'number', 'default' => 120, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'discount', 'label' => 'Discount percentage', 'type' => 'number', 'default' => 25, 'min' => 0, 'max' => 100, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const price = h.n(v.price);
            const discountPercent = h.n(v.discount);
            if (price < 0) throw new Error('The original price cannot be negative.');
            if (discountPercent < 0 || discountPercent > 100) throw new Error('The discount must be between 0 and 100 percent.');
            const amount = price * (discountPercent / 100);
            return [
                { label: 'Discount amount', value: amount },
                { label: 'Final price', value: price - amount },
                { label: 'Amount saved', value: amount },
            ];
        JS,
        'tests' => [
            ['input' => ['price' => 120, 'discount' => 25], 'expect' => [
                'Discount amount' => 30,
                'Final price' => 90,
                'Amount saved' => 30,
            ]],
            ['input' => ['price' => 80, 'discount' => 10], 'expect' => [
                'Discount amount' => 8,
                'Final price' => 72,
                'Amount saved' => 8,
            ]],
        ],
    ],

    [
        'slug' => 'vat-calculator',
        'name' => 'VAT Calculator',
        'category' => 'finance',
        'description' => 'Add or remove VAT and see the net, VAT and gross amounts.',
        'icon' => 'receipt-text',
        'featured' => false,
        'keywords' => ['vat', 'tax', 'gross', 'net', 'sales tax'],
        'inputs' => [
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'default' => 100, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'VAT rate', 'type' => 'number', 'default' => 20, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Percentage, for example 20.'],
            [
                'name' => 'mode',
                'label' => 'Calculation',
                'type' => 'select',
                'default' => 'add',
                'options' => [
                    ['value' => 'add', 'label' => 'Add VAT to a net amount'],
                    ['value' => 'remove', 'label' => 'Remove VAT from a gross amount'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const amount = h.n(v.amount);
            const rate = h.n(v.rate);
            if (amount < 0) throw new Error('The amount cannot be negative.');
            if (rate < 0) throw new Error('The VAT rate cannot be negative.');
            const factor = 1 + rate / 100;
            if (v.mode === 'remove') {
                if (factor === 0) throw new Error('That VAT rate is not valid.');
                const net = amount / factor;
                return [
                    { label: 'VAT amount', value: amount - net },
                    { label: 'Net amount', value: net },
                    { label: 'Gross amount', value: amount },
                ];
            }
            const vat = amount * (rate / 100);
            return [
                { label: 'VAT amount', value: vat },
                { label: 'Net amount', value: amount },
                { label: 'Gross amount', value: amount + vat },
            ];
        JS,
        'tests' => [
            ['input' => ['amount' => 100, 'rate' => 20, 'mode' => 'add'], 'expect' => [
                'VAT amount' => 20,
                'Net amount' => 100,
                'Gross amount' => 120,
            ]],
            ['input' => ['amount' => 120, 'rate' => 20, 'mode' => 'remove'], 'expect' => [
                'VAT amount' => 20,
                'Net amount' => 100,
                'Gross amount' => 120,
            ]],
            ['input' => ['amount' => 50, 'rate' => 5, 'mode' => 'add'], 'expect' => [
                'VAT amount' => 2.5,
                'Gross amount' => 52.5,
            ]],
        ],
    ],

    [
        'slug' => 'roi-calculator',
        'name' => 'ROI Calculator',
        'category' => 'finance',
        'description' => 'Measure net profit, return on investment and annualised ROI.',
        'icon' => 'trending-up',
        'featured' => false,
        'keywords' => ['roi', 'return on investment', 'profit', 'investment'],
        'inputs' => [
            ['name' => 'initial', 'label' => 'Initial investment', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'final', 'label' => 'Final value', 'type' => 'number', 'default' => 1500, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'costs', 'label' => 'Additional costs', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 'any'],
            ['name' => 'years', 'label' => 'Holding period (years)', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 'any'],
        ],
        'compute' => <<<'JS'
            const initial = h.n(v.initial);
            const finalValue = h.n(v.final);
            const costs = v.costs === undefined || v.costs === '' ? 0 : h.n(v.costs);
            const years = v.years === undefined || v.years === '' ? 0 : h.n(v.years);
            if (initial <= 0) throw new Error('The initial investment must be greater than zero.');
            if (finalValue < 0) throw new Error('The final value cannot be negative.');
            if (costs < 0) throw new Error('Additional costs cannot be negative.');
            if (years < 0) throw new Error('The holding period cannot be negative.');
            const invested = initial + costs;
            const profit = finalValue - invested;
            const roi = (profit / invested) * 100;
            const results = [
                { label: 'Net profit', value: profit },
                { label: 'ROI', value: roi, unit: '%' },
            ];
            if (years > 0) {
                const annualised = (Math.pow(finalValue / invested, 1 / years) - 1) * 100;
                results.push({ label: 'Annualised ROI', value: annualised, unit: '%' });
            }
            return results;
        JS,
        'tests' => [
            ['input' => ['initial' => 1000, 'final' => 1500, 'costs' => 0, 'years' => 0], 'expect' => [
                'Net profit' => 500,
                'ROI' => 50,
            ]],
            ['input' => ['initial' => 1000, 'final' => 1500, 'costs' => 100, 'years' => 5], 'expect' => [
                'Net profit' => 400,
                'ROI' => 36.3636363636,
                'Annualised ROI' => 6.3995312815,
            ]],
        ],
    ],

    [
        'slug' => 'savings-goal-calculator',
        'name' => 'Savings Goal Calculator',
        'category' => 'finance',
        'description' => 'Find how many months it takes to reach a savings goal.',
        'icon' => 'target',
        'featured' => false,
        'keywords' => ['savings', 'goal', 'monthly contribution', 'interest'],
        'inputs' => [
            ['name' => 'goal', 'label' => 'Savings goal', 'type' => 'number', 'default' => 10000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'current', 'label' => 'Current savings', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'monthly', 'label' => 'Monthly contribution', 'type' => 'number', 'default' => 500, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Percentage per year, compounded monthly.'],
            ['name' => 'months', 'label' => 'Projection horizon (months)', 'type' => 'number', 'default' => 24, 'min' => 1, 'max' => 1200, 'step' => 1, 'hint' => 'Used for the projected balance, not the months-to-goal result.'],
        ],
        'compute' => <<<'JS'
            const goal = h.n(v.goal);
            const current = h.n(v.current);
            const monthly = h.n(v.monthly);
            const annualRate = h.n(v.rate);
            const horizon = v.months === undefined || v.months === '' ? 120 : Math.floor(h.n(v.months));
            if (goal <= 0) throw new Error('The savings goal must be greater than zero.');
            if (current < 0) throw new Error('Current savings cannot be negative.');
            if (monthly < 0) throw new Error('The monthly contribution cannot be negative.');
            if (annualRate < 0) throw new Error('The interest rate cannot be negative.');
            if (!Number.isInteger(horizon) || horizon < 1 || horizon > 1200) throw new Error('Choose a projection horizon between 1 and 1200 months.');
            const monthlyRate = annualRate / 100 / 12;
            const project = (limit) => {
                let balance = current;
                let months = 0;
                while (balance < goal && months < limit) {
                    balance = balance * (1 + monthlyRate) + monthly;
                    months++;
                }
                return { balance, months };
            };
            if (current >= goal) {
                return [
                    { label: 'Months to goal', value: 0, decimals: 0 },
                    { label: 'Total contributed', value: 0 },
                    { label: 'Interest earned', value: 0 },
                    { label: 'Projected balance', value: current, hint: 'The goal is already met.' },
                ];
            }
            if (monthly <= 0) throw new Error('Add a monthly contribution to reach this goal.');
            const goalProjection = project(1200);
            if (goalProjection.balance < goal) throw new Error('The goal is not reachable within 100 years at this contribution.');
            const monthsToGoal = goalProjection.months;
            const contributed = monthly * monthsToGoal;
            const interest = goalProjection.balance - current - contributed;
            const horizonProjection = project(horizon);
            return [
                { label: 'Months to goal', value: monthsToGoal, decimals: 0 },
                { label: 'Total contributed', value: contributed },
                { label: 'Interest earned', value: interest },
                { label: 'Projected balance', value: horizonProjection.balance, hint: `Balance after ${horizon} month${horizon === 1 ? '' : 's'}.` },
            ];
        JS,
        'tests' => [
            ['input' => ['goal' => 10000, 'current' => 0, 'monthly' => 500, 'rate' => 0, 'months' => 24], 'expect' => [
                'Months to goal' => 20,
                'Total contributed' => 10000,
                'Interest earned' => 0,
            ]],
            ['input' => ['goal' => 5000, 'current' => 1000, 'monthly' => 200, 'rate' => 6, 'months' => 60], 'expect' => [
                'Months to goal' => 19,
                'Total contributed' => 3800,
                'Interest earned' => 275.3419548815,
            ]],
            ['input' => ['goal' => 1000, 'current' => 1500, 'monthly' => 100, 'rate' => 0, 'months' => 12], 'expect' => [
                'Months to goal' => 0,
                'Total contributed' => 0,
                'Interest earned' => 0,
            ]],
        ],
    ],

    [
        'slug' => 'inflation-calculator',
        'name' => 'Inflation Calculator',
        'category' => 'finance',
        'description' => 'See how inflation changes prices and the purchasing power of money.',
        'icon' => 'chart-line',
        'featured' => false,
        'keywords' => ['inflation', 'purchasing power', 'future cost', 'money'],
        'inputs' => [
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual inflation rate', 'type' => 'number', 'default' => 3, 'min' => 0, 'step' => 'any', 'required' => true, 'hint' => 'Percentage per year.'],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const amount = h.n(v.amount);
            const rate = h.n(v.rate);
            const years = h.n(v.years);
            if (amount < 0) throw new Error('The amount cannot be negative.');
            if (rate < 0) throw new Error('The inflation rate cannot be negative.');
            if (years < 0) throw new Error('The number of years cannot be negative.');
            const factor = Math.pow(1 + rate / 100, years);
            return [
                { label: 'Future cost', value: amount * factor },
                { label: 'Purchasing power', value: 100 / factor, unit: '%' },
                { label: "Value in today's money", value: amount / factor },
            ];
        JS,
        'tests' => [
            ['input' => ['amount' => 1000, 'rate' => 3, 'years' => 10], 'expect' => [
                'Future cost' => 1343.9163793441,
                'Purchasing power' => 74.4093914897,
                'Value in today\'s money' => 744.0939148967,
            ]],
            ['input' => ['amount' => 500, 'rate' => 2, 'years' => 5], 'expect' => [
                'Future cost' => 552.0404016,
                'Purchasing power' => 90.573081,
                'Value in today\'s money' => 452.8654049,
            ]],
        ],
    ],

    [
        'slug' => 'salary-hourly-converter',
        'name' => 'Salary ↔ Hourly Converter',
        'category' => 'finance',
        'description' => 'Convert between annual salary and hourly pay, with weekly and monthly figures.',
        'icon' => 'clock',
        'featured' => false,
        'keywords' => ['salary', 'hourly', 'wage', 'annual', 'pay'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Conversion',
                'type' => 'select',
                'default' => 'annual-to-hourly',
                'options' => [
                    ['value' => 'annual-to-hourly', 'label' => 'Annual salary → hourly'],
                    ['value' => 'hourly-to-annual', 'label' => 'Hourly rate → annual'],
                ],
            ],
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'default' => 52000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'hours', 'label' => 'Hours per week', 'type' => 'number', 'default' => 40, 'min' => 0, 'max' => 168, 'step' => 'any', 'required' => true],
            ['name' => 'weeks', 'label' => 'Weeks per year', 'type' => 'number', 'default' => 52, 'min' => 0, 'max' => 52, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const amount = h.n(v.amount);
            const hours = v.hours === undefined || v.hours === '' ? 40 : h.n(v.hours);
            const weeks = v.weeks === undefined || v.weeks === '' ? 52 : h.n(v.weeks);
            if (amount < 0) throw new Error('The amount cannot be negative.');
            if (hours <= 0 || hours > 168) throw new Error('Hours per week must be between 0 and 168.');
            if (weeks <= 0 || weeks > 52) throw new Error('Weeks per year must be between 0 and 52.');
            const annual = v.mode === 'hourly-to-annual' ? amount * hours * weeks : amount;
            const hourly = annual / (hours * weeks);
            return [
                { label: 'Hourly', value: hourly },
                { label: 'Weekly', value: hourly * hours },
                { label: 'Monthly', value: annual / 12 },
                { label: 'Annual', value: annual },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'annual-to-hourly', 'amount' => 52000, 'hours' => 40, 'weeks' => 52], 'expect' => [
                'Hourly' => 25,
                'Weekly' => 1000,
                'Monthly' => 4333.3333333333,
                'Annual' => 52000,
            ]],
            ['input' => ['mode' => 'hourly-to-annual', 'amount' => 25, 'hours' => 40, 'weeks' => 52], 'expect' => [
                'Hourly' => 25,
                'Weekly' => 1000,
                'Monthly' => 4333.3333333333,
                'Annual' => 52000,
            ]],
        ],
    ],

    [
        'slug' => 'break-even-calculator',
        'name' => 'Break-Even Calculator',
        'category' => 'finance',
        'description' => 'Find the break-even point in units and revenue from fixed and variable costs.',
        'icon' => 'scale',
        'featured' => false,
        'keywords' => ['break-even', 'fixed costs', 'variable costs', 'margin'],
        'inputs' => [
            ['name' => 'fixed', 'label' => 'Fixed costs', 'type' => 'number', 'default' => 5000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'price', 'label' => 'Price per unit', 'type' => 'number', 'default' => 25, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'variable', 'label' => 'Variable cost per unit', 'type' => 'number', 'default' => 15, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const fixed = h.n(v.fixed);
            const price = h.n(v.price);
            const variable = h.n(v.variable);
            if (fixed < 0) throw new Error('Fixed costs cannot be negative.');
            if (price <= 0) throw new Error('The price per unit must be greater than zero.');
            if (variable < 0) throw new Error('The variable cost cannot be negative.');
            if (price <= variable) throw new Error('The price per unit must be greater than the variable cost per unit.');
            const margin = price - variable;
            const units = Math.ceil(fixed / margin);
            return [
                { label: 'Break-even units', value: units, decimals: 0 },
                { label: 'Break-even revenue', value: units * price },
                { label: 'Contribution margin', value: margin },
            ];
        JS,
        'tests' => [
            ['input' => ['fixed' => 5000, 'price' => 25, 'variable' => 15], 'expect' => [
                'Break-even units' => 500,
                'Break-even revenue' => 12500,
                'Contribution margin' => 10,
            ]],
            ['input' => ['fixed' => 10000, 'price' => 50, 'variable' => 30], 'expect' => [
                'Break-even units' => 500,
                'Break-even revenue' => 25000,
                'Contribution margin' => 20,
            ]],
        ],
    ],

    [
        'slug' => 'profit-margin-calculator',
        'name' => 'Profit Margin Calculator',
        'category' => 'finance',
        'description' => 'Calculate gross profit, profit margin and markup from revenue and cost.',
        'icon' => 'badge-percent',
        'featured' => false,
        'keywords' => ['profit margin', 'markup', 'gross profit', 'revenue', 'cost'],
        'inputs' => [
            ['name' => 'revenue', 'label' => 'Revenue', 'type' => 'number', 'default' => 200, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'cost', 'label' => 'Cost', 'type' => 'number', 'default' => 150, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
            const revenue = h.n(v.revenue);
            const cost = h.n(v.cost);
            if (revenue <= 0) throw new Error('Revenue must be greater than zero.');
            if (cost < 0) throw new Error('Cost cannot be negative.');
            const profit = revenue - cost;
            return [
                { label: 'Gross profit', value: profit },
                { label: 'Profit margin', value: (profit / revenue) * 100, unit: '%' },
                { label: 'Markup', value: cost === 0 ? '—' : (profit / cost) * 100, unit: cost === 0 ? '' : '%' },
            ];
        JS,
        'tests' => [
            ['input' => ['revenue' => 200, 'cost' => 150], 'expect' => [
                'Gross profit' => 50,
                'Profit margin' => 25,
                'Markup' => 33.3333333333,
            ]],
            ['input' => ['revenue' => 100, 'cost' => 80], 'expect' => [
                'Gross profit' => 20,
                'Profit margin' => 20,
                'Markup' => 25,
            ]],
        ],
    ],
];
