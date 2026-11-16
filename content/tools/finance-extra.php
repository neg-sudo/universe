<?php
/**
 * UniVerse — Finance (expanded catalogue).
 * 12 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'debt-to-income-calculator',
        'name' => 'Debt-to-Income Calculator',
        'category' => 'finance',
        'description' => 'Compare monthly debt payments with gross income and rate the result.',
        'icon' => 'percent',
        'keywords' => ['debt to income', 'dti', 'mortgage', 'budget', 'credit'],
        'inputs' => [
            ['name' => 'debt', 'label' => 'Monthly debt payments', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'income', 'label' => 'Gross monthly income', 'type' => 'number', 'default' => 5000, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const debt = h.n(v.debt), income = h.n(v.income);
    if (debt < 0) throw new Error('Debt payments cannot be negative.');
    if (income <= 0) throw new Error('Income must be greater than zero.');
    const dti = (debt / income) * 100;
    const status = dti <= 20 ? 'Excellent' : dti <= 36 ? 'Good' : dti <= 43 ? 'Manageable' : 'High';
    return [
        { label: 'Debt-to-income ratio', value: dti, unit: '%' },
        { label: 'Status', value: status },
        { label: 'Recommended maximum debt', value: income * 0.36 },
        { label: 'Remaining income', value: income - debt },
    ];
JS,
        'tests' => [
            ['input' => ['debt' => 1000, 'income' => 5000], 'expect' => ['Debt-to-income ratio' => 20, 'Status' => 'Excellent', 'Recommended maximum debt' => 1800, 'Remaining income' => 4000]],
            ['input' => ['debt' => 2000, 'income' => 5000], 'expect' => ['Debt-to-income ratio' => 40, 'Status' => 'Manageable', 'Recommended maximum debt' => 1800, 'Remaining income' => 3000]],
            ['input' => ['debt' => 3000, 'income' => 5000], 'expect' => ['Debt-to-income ratio' => 60, 'Status' => 'High']],
        ],
    ],
    [
        'slug' => 'cagr-calculator',
        'name' => 'CAGR Calculator',
        'category' => 'finance',
        'description' => 'Find the compound annual growth rate between a starting and ending value.',
        'icon' => 'trending-up',
        'keywords' => ['cagr', 'compound annual growth rate', 'investment', 'growth'],
        'inputs' => [
            ['name' => 'begin', 'label' => 'Starting value', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'end', 'label' => 'Ending value', 'type' => 'number', 'default' => 2000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const begin = h.n(v.begin), end = h.n(v.end), years = h.n(v.years);
    if (begin <= 0) throw new Error('The starting value must be greater than zero.');
    if (end < 0) throw new Error('The ending value cannot be negative.');
    if (years <= 0) throw new Error('Years must be greater than zero.');
    const cagr = (Math.pow(end / begin, 1 / years) - 1) * 100;
    return [
        { label: 'CAGR', value: cagr, unit: '%' },
        { label: 'Total growth', value: ((end - begin) / begin) * 100, unit: '%' },
        { label: 'Multiple', value: end / begin, unit: '×' },
    ];
JS,
        'tests' => [
            ['input' => ['begin' => 1000, 'end' => 2000, 'years' => 10], 'expect' => ['CAGR' => 7.177346253629313, 'Total growth' => 100, 'Multiple' => 2]],
            ['input' => ['begin' => 100, 'end' => 121, 'years' => 2], 'expect' => ['CAGR' => 10.000000000000009, 'Total growth' => 21, 'Multiple' => 1.21]],
        ],
    ],
    [
        'slug' => 'dividend-yield-calculator',
        'name' => 'Dividend Yield Calculator',
        'category' => 'finance',
        'description' => 'Calculate dividend yield, annual income and the value of a share holding.',
        'icon' => 'chart-line',
        'keywords' => ['dividend', 'yield', 'shares', 'income', 'investment'],
        'inputs' => [
            ['name' => 'dividend', 'label' => 'Annual dividend per share', 'type' => 'number', 'default' => 2, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'price', 'label' => 'Share price', 'type' => 'number', 'default' => 50, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'shares', 'label' => 'Shares owned', 'type' => 'number', 'default' => 100, 'min' => 0, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const dividend = h.n(v.dividend), price = h.n(v.price), shares = Math.trunc(h.n(v.shares));
    if (dividend < 0 || price < 0 || shares < 0) throw new Error('Values cannot be negative.');
    if (price <= 0) throw new Error('The share price must be greater than zero.');
    const annualIncome = dividend * shares;
    return [
        { label: 'Dividend yield', value: (dividend / price) * 100, unit: '%' },
        { label: 'Annual income', value: annualIncome },
        { label: 'Quarterly income', value: annualIncome / 4 },
        { label: 'Holding value', value: price * shares },
    ];
JS,
        'tests' => [
            ['input' => ['dividend' => 2, 'price' => 50, 'shares' => 100], 'expect' => ['Dividend yield' => 4, 'Annual income' => 200, 'Quarterly income' => 50, 'Holding value' => 5000]],
            ['input' => ['dividend' => 1.5, 'price' => 30, 'shares' => 200], 'expect' => ['Dividend yield' => 5, 'Annual income' => 300, 'Quarterly income' => 75, 'Holding value' => 6000]],
        ],
    ],
    [
        'slug' => 'future-value-calculator',
        'name' => 'Future Value Calculator',
        'category' => 'finance',
        'description' => 'Project the future value of a lump sum with compound interest.',
        'icon' => 'trending-up',
        'keywords' => ['future value', 'compound interest', 'investment', 'growth'],
        'inputs' => [
            ['name' => 'present', 'label' => 'Present value', 'type' => 'number', 'default' => 1000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate (%)', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'compounds', 'label' => 'Compounds per year', 'type' => 'number', 'default' => 1, 'min' => 1, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const present = h.n(v.present), rate = h.n(v.rate) / 100, years = h.n(v.years), compounds = Math.max(1, Math.trunc(h.n(v.compounds)));
    if (present < 0) throw new Error('The present value cannot be negative.');
    const future = present * Math.pow(1 + rate / compounds, compounds * years);
    return [
        { label: 'Future value', value: future },
        { label: 'Interest earned', value: future - present },
        { label: 'Growth', value: present === 0 ? 0 : ((future - present) / present) * 100, unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['present' => 1000, 'rate' => 5, 'years' => 10, 'compounds' => 12], 'expect' => ['Future value' => 1647.00949769028, 'Interest earned' => 647.0094976902801, 'Growth' => 64.70094976902801]],
            ['input' => ['present' => 1000, 'rate' => 5, 'years' => 10, 'compounds' => 1], 'expect' => ['Future value' => 1628.894626777442, 'Interest earned' => 628.894626777442]],
        ],
    ],
    [
        'slug' => 'present-value-calculator',
        'name' => 'Present Value Calculator',
        'category' => 'finance',
        'description' => 'Discount a future amount to today’s value at a chosen interest rate.',
        'icon' => 'arrow-up-down',
        'keywords' => ['present value', 'discount', 'future value', 'investment'],
        'inputs' => [
            ['name' => 'future', 'label' => 'Future value', 'type' => 'number', 'default' => 1628.894626777442, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate (%)', 'type' => 'number', 'default' => 5, 'step' => 'any', 'required' => true],
            ['name' => 'years', 'label' => 'Years', 'type' => 'number', 'default' => 10, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'compounds', 'label' => 'Compounds per year', 'type' => 'number', 'default' => 1, 'min' => 1, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const future = h.n(v.future), rate = h.n(v.rate) / 100, years = h.n(v.years), compounds = Math.max(1, Math.trunc(h.n(v.compounds)));
    if (future < 0) throw new Error('The future value cannot be negative.');
    const present = future / Math.pow(1 + rate / compounds, compounds * years);
    return [
        { label: 'Present value', value: present },
        { label: 'Discount amount', value: future - present },
        { label: 'Discount rate', value: future === 0 ? 0 : ((future - present) / future) * 100, unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['future' => 1628.894626777442, 'rate' => 5, 'years' => 10, 'compounds' => 1], 'expect' => ['Present value' => 1000, 'Discount amount' => 628.894626777442, 'Discount rate' => 38.60867464592408]],
            ['input' => ['future' => 1000, 'rate' => 0, 'years' => 5, 'compounds' => 12], 'expect' => ['Present value' => 1000, 'Discount amount' => 0]],
        ],
    ],
    [
        'slug' => 'debt-payoff-calculator',
        'name' => 'Debt Payoff Calculator',
        'category' => 'finance',
        'description' => 'See how long a fixed monthly payment takes to clear a debt and its interest.',
        'icon' => 'calendar-days',
        'keywords' => ['debt payoff', 'credit card', 'interest', 'monthly payment', 'loan'],
        'inputs' => [
            ['name' => 'balance', 'label' => 'Current balance', 'type' => 'number', 'default' => 5000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'apr', 'label' => 'Annual interest rate (%)', 'type' => 'number', 'default' => 18, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'payment', 'label' => 'Monthly payment', 'type' => 'number', 'default' => 200, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    let balance = h.n(v.balance);
    const apr = h.n(v.apr), payment = h.n(v.payment);
    if (balance < 0 || apr < 0) throw new Error('Balance and interest rate cannot be negative.');
    if (payment <= 0) throw new Error('The monthly payment must be greater than zero.');
    const rate = apr / 100 / 12;
    if (payment <= balance * rate) throw new Error('The payment does not cover the monthly interest.');
    let months = 0;
    let interest = 0;
    while (balance > 0 && months < 1200) {
        const charged = balance * rate;
        let principal = payment - charged;
        if (principal > balance) principal = balance;
        balance -= principal;
        interest += charged;
        months++;
    }
    return [
        { label: 'Months to payoff', value: months, decimals: 0 },
        { label: 'Years to payoff', value: months / 12 },
        { label: 'Total interest', value: interest },
        { label: 'Total paid', value: interest + h.n(v.balance) },
    ];
JS,
        'tests' => [
            ['input' => ['balance' => 5000, 'apr' => 18, 'payment' => 200], 'expect' => ['Months to payoff' => 32, 'Years to payoff' => 32 / 12, 'Total interest' => 1313.9639987067321, 'Total paid' => 6313.963998706732]],
            ['input' => ['balance' => 1000, 'apr' => 12, 'payment' => 100], 'expect' => ['Months to payoff' => 11, 'Total interest' => 58.98488001215102, 'Total paid' => 1058.984880012151]],
            ['input' => ['balance' => 3000, 'apr' => 0, 'payment' => 100], 'expect' => ['Months to payoff' => 30, 'Total interest' => 0, 'Total paid' => 3000]],
        ],
    ],
    [
        'slug' => 'sales-tax-calculator',
        'name' => 'Sales Tax Calculator',
        'category' => 'finance',
        'description' => 'Add sales tax to a price or extract tax from a tax-inclusive total.',
        'icon' => 'percent',
        'keywords' => ['sales tax', 'vat', 'gst', 'tax', 'price'],
        'inputs' => [
            ['name' => 'amount', 'label' => 'Amount', 'type' => 'number', 'default' => 100, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Tax rate (%)', 'type' => 'number', 'default' => 8.25, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'mode', 'label' => 'Amount is', 'type' => 'select', 'default' => 'net', 'options' => [
                ['value' => 'net', 'label' => 'Before tax'],
                ['value' => 'gross', 'label' => 'Including tax'],
            ]],
        ],
        'compute' => <<<'JS'
    const amount = h.n(v.amount), rate = h.n(v.rate) / 100;
    if (amount < 0 || rate < 0) throw new Error('Amount and rate cannot be negative.');
    if (v.mode === 'gross') {
        const net = amount / (1 + rate);
        return [
            { label: 'Net price', value: net },
            { label: 'Tax', value: amount - net },
            { label: 'Gross price', value: amount },
        ];
    }
    const tax = amount * rate;
    return [
        { label: 'Net price', value: amount },
        { label: 'Tax', value: tax },
        { label: 'Gross price', value: amount + tax },
    ];
JS,
        'tests' => [
            ['input' => ['amount' => 100, 'rate' => 8.25, 'mode' => 'net'], 'expect' => ['Net price' => 100, 'Tax' => 8.25, 'Gross price' => 108.25]],
            ['input' => ['amount' => 108.25, 'rate' => 8.25, 'mode' => 'gross'], 'expect' => ['Net price' => 100, 'Tax' => 8.25, 'Gross price' => 108.25]],
        ],
    ],
    [
        'slug' => 'loan-to-value-calculator',
        'name' => 'Loan-to-Value Calculator',
        'category' => 'finance',
        'description' => 'Compare a loan amount with a property value and show the equity.',
        'icon' => 'home',
        'keywords' => ['ltv', 'loan to value', 'mortgage', 'equity', 'property'],
        'inputs' => [
            ['name' => 'loan', 'label' => 'Loan amount', 'type' => 'number', 'default' => 200000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'value', 'label' => 'Property value', 'type' => 'number', 'default' => 250000, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const loan = h.n(v.loan), value = h.n(v.value);
    if (loan < 0) throw new Error('The loan amount cannot be negative.');
    if (value <= 0) throw new Error('The property value must be greater than zero.');
    const ltv = (loan / value) * 100;
    const equity = value - loan;
    return [
        { label: 'Loan-to-value', value: ltv, unit: '%' },
        { label: 'Equity', value: equity },
        { label: 'Equity share', value: (equity / value) * 100, unit: '%' },
        { label: 'Status', value: ltv <= 80 ? 'Standard' : ltv <= 90 ? 'Higher risk' : 'High risk' },
    ];
JS,
        'tests' => [
            ['input' => ['loan' => 200000, 'value' => 250000], 'expect' => ['Loan-to-value' => 80, 'Equity' => 50000, 'Equity share' => 20, 'Status' => 'Standard']],
            ['input' => ['loan' => 225000, 'value' => 250000], 'expect' => ['Loan-to-value' => 90, 'Equity' => 25000, 'Equity share' => 10, 'Status' => 'Higher risk']],
            ['input' => ['loan' => 240000, 'value' => 250000], 'expect' => ['Loan-to-value' => 96, 'Equity' => 10000, 'Equity share' => 4, 'Status' => 'High risk']],
        ],
    ],
    [
        'slug' => 'credit-utilization-calculator',
        'name' => 'Credit Utilization Calculator',
        'category' => 'finance',
        'description' => 'Work out credit utilisation and how much credit remains available.',
        'icon' => 'scale',
        'keywords' => ['credit utilization', 'credit score', 'balances', 'credit limit'],
        'inputs' => [
            ['name' => 'balance', 'label' => 'Total balances', 'type' => 'number', 'default' => 1500, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'limit', 'label' => 'Total credit limit', 'type' => 'number', 'default' => 5000, 'min' => 0, 'step' => 'any', 'required' => true],
        ],
        'compute' => <<<'JS'
    const balance = h.n(v.balance), limit = h.n(v.limit);
    if (balance < 0) throw new Error('Balances cannot be negative.');
    if (limit <= 0) throw new Error('The credit limit must be greater than zero.');
    const utilization = (balance / limit) * 100;
    return [
        { label: 'Utilisation', value: utilization, unit: '%' },
        { label: 'Available credit', value: Math.max(0, limit - balance) },
        { label: 'Status', value: utilization <= 30 ? 'Good' : utilization <= 50 ? 'Fair' : 'High' },
        { label: 'Balance to reach 30%', value: Math.max(0, limit * 0.3 - balance) },
    ];
JS,
        'tests' => [
            ['input' => ['balance' => 1500, 'limit' => 5000], 'expect' => ['Utilisation' => 30, 'Available credit' => 3500, 'Status' => 'Good', 'Balance to reach 30%' => 0]],
            ['input' => ['balance' => 2500, 'limit' => 5000], 'expect' => ['Utilisation' => 50, 'Available credit' => 2500, 'Status' => 'Fair']],
            ['input' => ['balance' => 4000, 'limit' => 5000], 'expect' => ['Utilisation' => 80, 'Available credit' => 1000, 'Status' => 'High', 'Balance to reach 30%' => 0]],
        ],
    ],
    [
        'slug' => 'mortgage-calculator',
        'name' => 'Mortgage Calculator',
        'category' => 'finance',
        'description' => 'Estimate monthly mortgage payments, total interest and down payment.',
        'icon' => 'home',
        'featured' => true,
        'keywords' => ['mortgage', 'home loan', 'monthly payment', 'down payment', 'interest'],
        'inputs' => [
            ['name' => 'price', 'label' => 'Home price', 'type' => 'number', 'default' => 250000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'down', 'label' => 'Down payment (%)', 'type' => 'number', 'default' => 20, 'min' => 0, 'max' => 100, 'step' => 'any', 'required' => true],
            ['name' => 'rate', 'label' => 'Annual interest rate (%)', 'type' => 'number', 'default' => 6, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'years', 'label' => 'Loan term (years)', 'type' => 'number', 'default' => 30, 'min' => 1, 'max' => 50, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const price = h.n(v.price), downPercent = h.n(v.down), rate = h.n(v.rate), years = h.n(v.years);
    if (price <= 0) throw new Error('Home price must be greater than zero.');
    if (downPercent < 0 || downPercent >= 100) throw new Error('Down payment must be between 0 and 100 percent.');
    if (rate < 0) throw new Error('Interest rate cannot be negative.');
    if (years <= 0) throw new Error('Loan term must be greater than zero.');
    const downPayment = (price * downPercent) / 100;
    const loan = price - downPayment;
    const months = years * 12;
    const monthlyRate = rate / 100 / 12;
    const monthly = monthlyRate === 0 ? loan / months : (loan * monthlyRate) / (1 - Math.pow(1 + monthlyRate, -months));
    const totalPaid = monthly * months;
    return [
        { label: 'Monthly payment', value: monthly },
        { label: 'Loan amount', value: loan },
        { label: 'Down payment', value: downPayment },
        { label: 'Total interest', value: totalPaid - loan },
        { label: 'Total cost', value: totalPaid + downPayment },
    ];
JS,
        'tests' => [
            ['input' => ['price' => 250000, 'down' => 20, 'rate' => 6, 'years' => 30], 'expect' => ['Monthly payment' => 1199.1010503055138, 'Loan amount' => 200000, 'Down payment' => 50000, 'Total interest' => 231676.37810998497, 'Total cost' => 481676.37810998497]],
            ['input' => ['price' => 300000, 'down' => 20, 'rate' => 5, 'years' => 30], 'expect' => ['Monthly payment' => 1288.3718952291356, 'Loan amount' => 240000, 'Down payment' => 60000, 'Total interest' => 223813.8822824888]],
        ],
    ],
    [
        'slug' => 'emergency-fund-calculator',
        'name' => 'Emergency Fund Calculator',
        'category' => 'finance',
        'description' => 'Set an emergency fund target and see how long it takes to reach it.',
        'icon' => 'shield',
        'keywords' => ['emergency fund', 'savings goal', 'safety net', 'monthly expenses'],
        'inputs' => [
            ['name' => 'expenses', 'label' => 'Monthly expenses', 'type' => 'number', 'default' => 2000, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'months', 'label' => 'Months of cover', 'type' => 'number', 'default' => 6, 'min' => 1, 'max' => 24, 'step' => '1', 'required' => true],
            ['name' => 'current', 'label' => 'Current savings', 'type' => 'number', 'default' => 3000, 'min' => 0, 'step' => 'any'],
            ['name' => 'monthly', 'label' => 'Monthly savings', 'type' => 'number', 'default' => 500, 'min' => 0, 'step' => 'any'],
        ],
        'compute' => <<<'JS'
    const expenses = h.n(v.expenses), months = Math.max(1, Math.trunc(h.n(v.months)));
    const current = h.n(v.current || 0), monthly = h.n(v.monthly || 0);
    if (expenses < 0 || current < 0 || monthly < 0) throw new Error('Values cannot be negative.');
    const target = expenses * months;
    const shortfall = Math.max(0, target - current);
    const monthsToGoal = shortfall === 0 ? 0 : monthly > 0 ? Math.ceil(shortfall / monthly) : null;
    return [
        { label: 'Target', value: target },
        { label: 'Shortfall', value: shortfall },
        { label: 'Months to goal', value: monthsToGoal === null ? 'Set a monthly saving' : monthsToGoal, decimals: 0 },
        { label: 'Progress', value: target === 0 ? 100 : Math.min(100, (current / target) * 100), unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['expenses' => 2000, 'months' => 6, 'current' => 3000, 'monthly' => 500], 'expect' => ['Target' => 12000, 'Shortfall' => 9000, 'Months to goal' => 18, 'Progress' => 25]],
            ['input' => ['expenses' => 1500, 'months' => 3, 'current' => 5000, 'monthly' => 200], 'expect' => ['Target' => 4500, 'Shortfall' => 0, 'Months to goal' => 0, 'Progress' => 100]],
        ],
    ],
    [
        'slug' => 'stock-profit-calculator',
        'name' => 'Stock Profit Calculator',
        'category' => 'finance',
        'description' => 'Calculate share trading profit, return and break-even price after fees.',
        'icon' => 'chart-line',
        'keywords' => ['stock', 'shares', 'profit', 'commission', 'trading'],
        'inputs' => [
            ['name' => 'buy', 'label' => 'Buy price per share', 'type' => 'number', 'default' => 50, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'sell', 'label' => 'Sell price per share', 'type' => 'number', 'default' => 75, 'min' => 0, 'step' => 'any', 'required' => true],
            ['name' => 'shares', 'label' => 'Number of shares', 'type' => 'number', 'default' => 100, 'min' => 1, 'step' => '1', 'required' => true],
            ['name' => 'commission', 'label' => 'Total commission', 'type' => 'number', 'default' => 0, 'min' => 0, 'step' => 'any'],
        ],
        'compute' => <<<'JS'
    const buy = h.n(v.buy), sell = h.n(v.sell), shares = Math.trunc(h.n(v.shares)), commission = h.n(v.commission || 0);
    if (buy < 0 || sell < 0) throw new Error('Prices cannot be negative.');
    if (shares <= 0) throw new Error('Shares must be greater than zero.');
    if (commission < 0) throw new Error('Commission cannot be negative.');
    const cost = buy * shares + commission;
    const proceeds = sell * shares - commission;
    const profit = proceeds - cost;
    const roi = cost === 0 ? 0 : (profit / cost) * 100;
    return [
        { label: 'Profit', value: profit },
        { label: 'Total cost', value: cost },
        { label: 'Total proceeds', value: proceeds },
        { label: 'ROI', value: roi, unit: '%' },
        { label: 'Break-even price', value: shares === 0 ? 0 : cost / shares },
    ];
JS,
        'tests' => [
            ['input' => ['buy' => 50, 'sell' => 75, 'shares' => 100, 'commission' => 0], 'expect' => ['Profit' => 2500, 'Total cost' => 5000, 'Total proceeds' => 7500, 'ROI' => 50, 'Break-even price' => 50]],
            ['input' => ['buy' => 100, 'sell' => 150, 'shares' => 10, 'commission' => 5], 'expect' => ['Profit' => 490, 'Total cost' => 1005, 'Total proceeds' => 1495, 'ROI' => 48.756218905472636, 'Break-even price' => 100.5]],
        ],
    ],
];
