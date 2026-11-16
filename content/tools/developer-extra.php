<?php
/**
 * UniVerse — Developer (expanded catalogue).
 * 14 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'regex-tester',
        'name' => 'Regex Tester',
        'category' => 'developer',
        'description' => 'Test a regular expression against text and list every match.',
        'icon' => 'code',
        'keywords' => ['regex', 'regular expression', 'pattern', 'match', 'developer'],
        'inputs' => [
            ['name' => 'pattern', 'label' => 'Pattern', 'type' => 'text', 'default' => '\d+', 'required' => true, 'hint' => 'Write the pattern without surrounding slashes.'],
            ['name' => 'flags', 'label' => 'Flags', 'type' => 'select', 'default' => 'g', 'options' => [
                ['value' => 'g', 'label' => 'g — global'],
                ['value' => 'i', 'label' => 'i — case-insensitive'],
                ['value' => 'gi', 'label' => 'gi — global + case-insensitive'],
            ]],
            ['name' => 'text', 'label' => 'Test text', 'type' => 'textarea', 'default' => 'a1 b22 c333', 'required' => true],
        ],
        'compute' => <<<'JS'
    const pattern = String(v.pattern ?? '');
    if (pattern === '') throw new Error('Enter a regular expression.');
    const flags = String(v.flags ?? 'g');
    let regex;
    try {
        regex = new RegExp(pattern, flags.includes('g') ? flags : flags + 'g');
    } catch (error) {
        throw new Error('Invalid regular expression: ' + error.message);
    }
    const matches = [...String(v.text ?? '').matchAll(regex)].map((m) => m[0]);
    return [
        { label: 'Matches', value: matches.length, decimals: 0 },
        { label: 'First match', value: matches[0] ?? '—' },
        { label: 'All matches', value: matches.length ? matches.join(', ') : '—' },
    ];
JS,
        'tests' => [
            ['input' => ['pattern' => '\d+', 'flags' => 'g', 'text' => 'a1 b22 c333'], 'expect' => ['Matches' => 3, 'First match' => '1', 'All matches' => '1, 22, 333']],
            ['input' => ['pattern' => '[A-Z]', 'flags' => 'i', 'text' => 'aBc'], 'expect' => ['Matches' => 3, 'First match' => 'a', 'All matches' => 'a, B, c']],
            ['input' => ['pattern' => 'cat', 'flags' => 'g', 'text' => 'dog'], 'expect' => ['Matches' => 0, 'First match' => '—', 'All matches' => '—']],
        ],
    ],
    [
        'slug' => 'json-validator',
        'name' => 'JSON Validator',
        'category' => 'developer',
        'description' => 'Check whether text is valid JSON and summarise its structure.',
        'icon' => 'file-check',
        'keywords' => ['json', 'validate', 'parse', 'structure', 'developer'],
        'inputs' => [
            ['name' => 'json', 'label' => 'JSON', 'type' => 'textarea', 'default' => '{"name":"UniVerse","tools":200}', 'required' => true],
        ],
        'compute' => <<<'JS'
    const text = String(v.json ?? '');
    try {
        const data = JSON.parse(text);
        const type = Array.isArray(data) ? 'Array' : data === null ? 'Null' : typeof data === 'object' ? 'Object' : typeof data;
        const entries = Array.isArray(data) ? data.length : data && typeof data === 'object' ? Object.keys(data).length : 0;
        return [
            { label: 'Valid', value: 'Yes' },
            { label: 'Type', value: type },
            { label: 'Entries', value: entries, decimals: 0 },
            { label: 'Size', value: text.length, decimals: 0 },
        ];
    } catch (error) {
        return [
            { label: 'Valid', value: 'No' },
            { label: 'Type', value: '—' },
            { label: 'Entries', value: 0, decimals: 0 },
            { label: 'Error', value: error.message },
        ];
    }
JS,
        'tests' => [
            ['input' => ['json' => '{"a":1,"b":[2,3]}'], 'expect' => ['Valid' => 'Yes', 'Type' => 'Object', 'Entries' => 2]],
            ['input' => ['json' => '[1,2,3]'], 'expect' => ['Valid' => 'Yes', 'Type' => 'Array', 'Entries' => 3]],
            ['input' => ['json' => '{bad}'], 'expect' => ['Valid' => 'No', 'Type' => '—', 'Entries' => 0]],
        ],
    ],
    [
        'slug' => 'cidr-calculator',
        'name' => 'CIDR Calculator',
        'category' => 'developer',
        'description' => 'Work out the network, broadcast, netmask and host range for a CIDR block.',
        'icon' => 'network',
        'keywords' => ['cidr', 'subnet', 'netmask', 'network', 'ipv4'],
        'inputs' => [
            ['name' => 'cidr', 'label' => 'CIDR block', 'type' => 'text', 'default' => '192.168.1.10/24', 'required' => true, 'hint' => 'Example: 10.0.0.0/8 or 192.168.1.0/24.'],
        ],
        'compute' => <<<'JS'
    const input = String(v.cidr ?? '').trim();
    const parts = input.split('/');
    if (parts.length !== 2) throw new Error('Enter a CIDR block such as 192.168.1.0/24.');
    const octets = parts[0].split('.').map(Number);
    if (octets.length !== 4 || octets.some((o) => !Number.isInteger(o) || o < 0 || o > 255)) throw new Error('Enter a valid IPv4 address.');
    const prefix = Number(parts[1]);
    if (!Number.isInteger(prefix) || prefix < 0 || prefix > 32) throw new Error('The prefix must be between 0 and 32.');
    const ipToInt = (list) => list.reduce((total, o) => total * 256 + o, 0);
    const intToIp = (n) => [Math.floor(n / 16777216) % 256, Math.floor(n / 65536) % 256, Math.floor(n / 256) % 256, n % 256].join('.');
    const ipInt = ipToInt(octets);
    const mask = prefix === 0 ? 0 : (0xFFFFFFFF << (32 - prefix)) >>> 0;
    const network = (ipInt & mask) >>> 0;
    const broadcast = (ipInt | (~mask >>> 0)) >>> 0;
    const hosts = prefix === 32 ? 1 : prefix === 31 ? 2 : Math.pow(2, 32 - prefix) - 2;
    return [
        { label: 'Network', value: intToIp(network) },
        { label: 'Broadcast', value: intToIp(broadcast) },
        { label: 'Netmask', value: intToIp(mask) },
        { label: 'Usable hosts', value: hosts, decimals: 0 },
        { label: 'First host', value: intToIp(prefix >= 31 ? network : network + 1) },
        { label: 'Last host', value: intToIp(prefix >= 31 ? broadcast : broadcast - 1) },
        { label: 'CIDR', value: intToIp(network) + '/' + prefix },
    ];
JS,
        'tests' => [
            ['input' => ['cidr' => '192.168.1.10/24'], 'expect' => ['Network' => '192.168.1.0', 'Broadcast' => '192.168.1.255', 'Netmask' => '255.255.255.0', 'Usable hosts' => 254, 'First host' => '192.168.1.1', 'Last host' => '192.168.1.254', 'CIDR' => '192.168.1.0/24']],
            ['input' => ['cidr' => '10.0.0.0/8'], 'expect' => ['Network' => '10.0.0.0', 'Broadcast' => '10.255.255.255', 'Netmask' => '255.0.0.0', 'Usable hosts' => 16777214]],
            ['input' => ['cidr' => '192.168.1.0/30'], 'expect' => ['Network' => '192.168.1.0', 'Broadcast' => '192.168.1.3', 'Usable hosts' => 2, 'First host' => '192.168.1.1', 'Last host' => '192.168.1.2']],
        ],
    ],
    [
        'slug' => 'ip-address-info',
        'name' => 'IP Address Info',
        'category' => 'developer',
        'description' => 'Classify an IPv4 address and show its class, scope and integer value.',
        'icon' => 'network',
        'keywords' => ['ip address', 'ipv4', 'private', 'public', 'network class'],
        'inputs' => [
            ['name' => 'ip', 'label' => 'IPv4 address', 'type' => 'text', 'default' => '192.168.1.1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const input = String(v.ip ?? '').trim();
    const octets = input.split('.').map(Number);
    if (octets.length !== 4 || octets.some((o) => !Number.isInteger(o) || o < 0 || o > 255)) {
        return [
            { label: 'Valid', value: 'No' },
            { label: 'Class', value: '—' },
            { label: 'Scope', value: '—' },
            { label: 'Integer', value: '—' },
        ];
    }
    const first = octets[0];
    const cls = first < 128 ? 'A' : first < 192 ? 'B' : first < 224 ? 'C' : first < 240 ? 'D' : 'E';
    let scope = 'Public';
    if (first === 10 || (first === 172 && octets[1] >= 16 && octets[1] <= 31) || (first === 192 && octets[1] === 168)) scope = 'Private';
    else if (first === 127) scope = 'Loopback';
    else if (first === 169 && octets[1] === 254) scope = 'Link-local';
    else if (first >= 224) scope = 'Multicast / reserved';
    const integer = octets.reduce((total, o) => total * 256 + o, 0);
    return [
        { label: 'Valid', value: 'Yes' },
        { label: 'Class', value: cls },
        { label: 'Scope', value: scope },
        { label: 'Integer', value: integer, decimals: 0 },
        { label: 'Binary first octet', value: first.toString(2).padStart(8, '0') },
    ];
JS,
        'tests' => [
            ['input' => ['ip' => '192.168.1.1'], 'expect' => ['Valid' => 'Yes', 'Class' => 'C', 'Scope' => 'Private', 'Integer' => 3232235777]],
            ['input' => ['ip' => '8.8.8.8'], 'expect' => ['Valid' => 'Yes', 'Class' => 'A', 'Scope' => 'Public', 'Integer' => 134744072, 'Binary first octet' => '00001000']],
            ['input' => ['ip' => '127.0.0.1'], 'expect' => ['Valid' => 'Yes', 'Class' => 'A', 'Scope' => 'Loopback']],
            ['input' => ['ip' => '999.1.1.1'], 'expect' => ['Valid' => 'No', 'Class' => '—']],
        ],
    ],
    [
        'slug' => 'url-parser',
        'name' => 'URL Parser',
        'category' => 'developer',
        'description' => 'Break a URL into protocol, host, port, path, query and fragment.',
        'icon' => 'external-link',
        'keywords' => ['url', 'parse', 'query string', 'hostname', 'developer'],
        'inputs' => [
            ['name' => 'url', 'label' => 'URL', 'type' => 'text', 'default' => 'https://example.com:8080/path/page?x=1&y=2#top', 'required' => true],
        ],
        'compute' => <<<'JS'
    let url;
    try {
        url = new URL(String(v.url ?? '').trim());
    } catch (error) {
        throw new Error('Enter a valid absolute URL including http:// or https://.');
    }
    return [
        { label: 'Protocol', value: url.protocol },
        { label: 'Hostname', value: url.hostname },
        { label: 'Port', value: url.port || '—' },
        { label: 'Path', value: url.pathname || '/' },
        { label: 'Query', value: url.search ? url.search.slice(1) : '—' },
        { label: 'Hash', value: url.hash || '—' },
        { label: 'Origin', value: url.origin },
    ];
JS,
        'tests' => [
            ['input' => ['url' => 'https://example.com:8080/path/page?x=1&y=2#top'], 'expect' => ['Protocol' => 'https:', 'Hostname' => 'example.com', 'Port' => '8080', 'Path' => '/path/page', 'Query' => 'x=1&y=2', 'Hash' => '#top', 'Origin' => 'https://example.com:8080']],
            ['input' => ['url' => 'https://example.com'], 'expect' => ['Protocol' => 'https:', 'Hostname' => 'example.com', 'Port' => '—', 'Path' => '/', 'Query' => '—', 'Hash' => '—', 'Origin' => 'https://example.com']],
        ],
    ],
    [
        'slug' => 'query-string-parser',
        'name' => 'Query String Parser',
        'category' => 'developer',
        'description' => 'Decode a URL query string into readable key and value pairs.',
        'icon' => 'external-link',
        'keywords' => ['query string', 'url params', 'decode', 'urlsearchparams'],
        'inputs' => [
            ['name' => 'query', 'label' => 'Query string', 'type' => 'textarea', 'default' => 'a=1&b=hello%20world&c=3', 'required' => true],
        ],
        'compute' => <<<'JS'
    const raw = String(v.query ?? '').trim().replace(/^\?/, '');
    const params = new URLSearchParams(raw);
    const entries = [...params.entries()];
    return [
        { label: 'Parameters', value: entries.length, decimals: 0 },
        { label: 'Keys', value: entries.length ? entries.map(([key]) => key).join(', ') : '—' },
        { label: 'Decoded', value: entries.length ? entries.map(([key, value]) => `${key}=${value}`).join('\n') : '—' },
    ];
JS,
        'tests' => [
            ['input' => ['query' => 'a=1&b=hello%20world&c=3'], 'expect' => ['Parameters' => 3, 'Keys' => 'a, b, c', 'Decoded' => "a=1\nb=hello world\nc=3"]],
            ['input' => ['query' => '?x=1&x=2'], 'expect' => ['Parameters' => 2, 'Keys' => 'x, x', 'Decoded' => "x=1\nx=2"]],
            ['input' => ['query' => ''], 'expect' => ['Parameters' => 0, 'Keys' => '—', 'Decoded' => '—']],
        ],
    ],
    [
        'slug' => 'html-minifier',
        'name' => 'HTML Minifier',
        'category' => 'developer',
        'description' => 'Remove HTML comments and collapse whitespace between tags.',
        'icon' => 'code',
        'keywords' => ['html', 'minify', 'compress', 'whitespace', 'comments'],
        'inputs' => [
            ['name' => 'html', 'label' => 'HTML', 'type' => 'textarea', 'default' => '<div>  <p>Hello</p>  </div>', 'required' => true],
        ],
        'compute' => <<<'JS'
    const original = String(v.html ?? '');
    const minified = original
        .replace(/<!--[\s\S]*?-->/g, '')
        .replace(/>\s+</g, '><')
        .replace(/\s{2,}/g, ' ')
        .trim();
    const saved = original.length === 0 ? 0 : ((original.length - minified.length) / original.length) * 100;
    return [
        { label: 'Minified', value: minified },
        { label: 'Original size', value: original.length, decimals: 0 },
        { label: 'Minified size', value: minified.length, decimals: 0 },
        { label: 'Saved', value: saved, unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['html' => '<div>  <p>Hello</p>  </div>'], 'expect' => ['Minified' => '<div><p>Hello</p></div>', 'Original size' => 27, 'Minified size' => 23]],
            ['input' => ['html' => '<!-- c --><p>Hi</p>'], 'expect' => ['Minified' => '<p>Hi</p>', 'Original size' => 19, 'Minified size' => 9]],
        ],
    ],
    [
        'slug' => 'css-minifier',
        'name' => 'CSS Minifier',
        'category' => 'developer',
        'description' => 'Strip comments and redundant whitespace from a CSS stylesheet.',
        'icon' => 'code',
        'keywords' => ['css', 'minify', 'compress', 'stylesheet', 'developer'],
        'inputs' => [
            ['name' => 'css', 'label' => 'CSS', 'type' => 'textarea', 'default' => 'body { color: red; }', 'required' => true],
        ],
        'compute' => <<<'JS'
    const original = String(v.css ?? '');
    let minified = original.replace(/\/\*[\s\S]*?\*\//g, '');
    minified = minified.replace(/\s+/g, ' ').trim();
    minified = minified.replace(/\s*([{}:;,>])\s*/g, '$1');
    minified = minified.replace(/;}/g, '}');
    const saved = original.length === 0 ? 0 : ((original.length - minified.length) / original.length) * 100;
    return [
        { label: 'Minified', value: minified },
        { label: 'Original size', value: original.length, decimals: 0 },
        { label: 'Minified size', value: minified.length, decimals: 0 },
        { label: 'Saved', value: saved, unit: '%' },
    ];
JS,
        'tests' => [
            ['input' => ['css' => 'body { color: red; }'], 'expect' => ['Minified' => 'body{color:red}', 'Original size' => 20, 'Minified size' => 15]],
            ['input' => ['css' => '/* x */ .a { margin: 0  auto; }'], 'expect' => ['Minified' => '.a{margin:0 auto}']],
        ],
    ],
    [
        'slug' => 'uuid-validator',
        'name' => 'UUID Validator',
        'category' => 'developer',
        'description' => 'Validate a UUID, detect its version and normalise letter case.',
        'icon' => 'hash',
        'keywords' => ['uuid', 'guid', 'validate', 'version', 'identifier'],
        'inputs' => [
            ['name' => 'uuid', 'label' => 'UUID', 'type' => 'text', 'default' => '550e8400-e29b-41d4-a716-446655440000', 'required' => true],
        ],
        'compute' => <<<'JS'
    const input = String(v.uuid ?? '').trim();
    const valid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(input);
    if (!valid) {
        return [
            { label: 'Valid', value: 'No' },
            { label: 'Version', value: '—' },
            { label: 'Variant', value: '—' },
            { label: 'Normalised', value: '—' },
        ];
    }
    const normalised = input.toLowerCase();
    const variantChar = normalised[19];
    const variant = '89ab'.includes(variantChar) ? 'RFC 4122' : 'cdef'.includes(variantChar) ? 'Microsoft' : 'NCS / reserved';
    return [
        { label: 'Valid', value: 'Yes' },
        { label: 'Version', value: normalised[14] },
        { label: 'Variant', value: variant },
        { label: 'Normalised', value: normalised },
    ];
JS,
        'tests' => [
            ['input' => ['uuid' => '550e8400-e29b-41d4-a716-446655440000'], 'expect' => ['Valid' => 'Yes', 'Version' => '4', 'Variant' => 'RFC 4122', 'Normalised' => '550e8400-e29b-41d4-a716-446655440000']],
            ['input' => ['uuid' => '550E8400-E29B-41D4-A716-446655440000'], 'expect' => ['Valid' => 'Yes', 'Version' => '4', 'Normalised' => '550e8400-e29b-41d4-a716-446655440000']],
            ['input' => ['uuid' => 'not-a-uuid'], 'expect' => ['Valid' => 'No', 'Version' => '—', 'Normalised' => '—']],
        ],
    ],
    [
        'slug' => 'email-validator',
        'name' => 'Email Validator',
        'category' => 'developer',
        'description' => 'Check an email address and split it into local part, domain and TLD.',
        'icon' => 'mail',
        'keywords' => ['email', 'validate', 'address', 'domain', 'developer'],
        'inputs' => [
            ['name' => 'email', 'label' => 'Email address', 'type' => 'text', 'default' => 'user.name+tag@example.co.uk', 'required' => true],
        ],
        'compute' => <<<'JS'
    const input = String(v.email ?? '').trim();
    const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input) && !input.includes('..');
    if (!valid) {
        return [
            { label: 'Valid', value: 'No' },
            { label: 'Local part', value: '—' },
            { label: 'Domain', value: '—' },
            { label: 'TLD', value: '—' },
        ];
    }
    const at = input.lastIndexOf('@');
    const local = input.slice(0, at);
    const domain = input.slice(at + 1);
    const labels = domain.split('.');
    return [
        { label: 'Valid', value: 'Yes' },
        { label: 'Local part', value: local },
        { label: 'Domain', value: domain },
        { label: 'TLD', value: labels[labels.length - 1] },
    ];
JS,
        'tests' => [
            ['input' => ['email' => 'user.name+tag@example.co.uk'], 'expect' => ['Valid' => 'Yes', 'Local part' => 'user.name+tag', 'Domain' => 'example.co.uk', 'TLD' => 'uk']],
            ['input' => ['email' => 'bad@@example.com'], 'expect' => ['Valid' => 'No', 'Local part' => '—', 'Domain' => '—', 'TLD' => '—']],
            ['input' => ['email' => 'user@localhost'], 'expect' => ['Valid' => 'No', 'TLD' => '—']],
        ],
    ],
    [
        'slug' => 'luhn-validator',
        'name' => 'Card Number Validator',
        'category' => 'developer',
        'description' => 'Validate a card number with the Luhn algorithm and detect its brand.',
        'icon' => 'file-check',
        'keywords' => ['luhn', 'credit card', 'validate', 'card number', 'checksum'],
        'inputs' => [
            ['name' => 'number', 'label' => 'Card number', 'type' => 'text', 'default' => '4111 1111 1111 1111', 'required' => true],
        ],
        'compute' => <<<'JS'
    const digits = String(v.number ?? '').replace(/[\s-]/g, '');
    if (!/^\d+$/.test(digits)) throw new Error('Enter digits only (spaces and dashes are allowed).');
    let sum = 0;
    let double = false;
    for (let i = digits.length - 1; i >= 0; i--) {
        let digit = Number(digits[i]);
        if (double) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        sum += digit;
        double = !double;
    }
    const valid = digits.length >= 12 && digits.length <= 19 && sum % 10 === 0;
    let brand = 'Unknown';
    if (/^3[47]/.test(digits) && digits.length === 15) brand = 'American Express';
    else if (/^4/.test(digits)) brand = 'Visa';
    else if (/^(5[1-5]|2(2[2-9]|[3-6]\d|7[01]|720))/.test(digits)) brand = 'Mastercard';
    else if (/^(6011|65)/.test(digits)) brand = 'Discover';
    return [
        { label: 'Valid', value: valid ? 'Yes' : 'No' },
        { label: 'Brand', value: brand },
        { label: 'Digits', value: digits.length, decimals: 0 },
        { label: 'Checksum', value: sum, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['number' => '4111 1111 1111 1111'], 'expect' => ['Valid' => 'Yes', 'Brand' => 'Visa', 'Digits' => 16]],
            ['input' => ['number' => '5555555555554444'], 'expect' => ['Valid' => 'Yes', 'Brand' => 'Mastercard', 'Digits' => 16]],
            ['input' => ['number' => '378282246310005'], 'expect' => ['Valid' => 'Yes', 'Brand' => 'American Express', 'Digits' => 15]],
            ['input' => ['number' => '1234567812345678'], 'expect' => ['Valid' => 'No', 'Brand' => 'Unknown', 'Digits' => 16]],
        ],
    ],
    [
        'slug' => 'semver-comparator',
        'name' => 'Semantic Version Comparator',
        'category' => 'developer',
        'description' => 'Compare two semantic versions and report which one is newer.',
        'icon' => 'code',
        'keywords' => ['semver', 'version', 'compare', 'release', 'developer'],
        'inputs' => [
            ['name' => 'a', 'label' => 'Version A', 'type' => 'text', 'default' => '2.0.0', 'required' => true],
            ['name' => 'b', 'label' => 'Version B', 'type' => 'text', 'default' => '1.9.9', 'required' => true],
        ],
        'compute' => <<<'JS'
    const parse = (value) => {
        const clean = String(value ?? '').trim().replace(/^v/, '').split('-')[0];
        const parts = clean.split('.').map((p) => Number(p));
        if (parts.length < 1 || parts.length > 3 || parts.some((p) => !Number.isInteger(p) || p < 0)) {
            throw new Error('Enter a version such as 1.2.3.');
        }
        return [parts[0] || 0, parts[1] || 0, parts[2] || 0];
    };
    const a = parse(v.a), b = parse(v.b);
    let comparison = 0;
    for (let i = 0; i < 3; i++) {
        if (a[i] !== b[i]) {
            comparison = a[i] > b[i] ? 1 : -1;
            break;
        }
    }
    return [
        { label: 'Result', value: comparison === 0 ? 'Equal' : comparison > 0 ? 'A is newer' : 'B is newer' },
        { label: 'Comparison', value: comparison, decimals: 0 },
        { label: 'Major difference', value: a[0] - b[0], decimals: 0 },
        { label: 'Minor difference', value: a[1] - b[1], decimals: 0 },
        { label: 'Patch difference', value: a[2] - b[2], decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['a' => '2.0.0', 'b' => '1.9.9'], 'expect' => ['Result' => 'A is newer', 'Comparison' => 1, 'Major difference' => 1, 'Minor difference' => -9, 'Patch difference' => -9]],
            ['input' => ['a' => '1.2.3', 'b' => '1.2.3'], 'expect' => ['Result' => 'Equal', 'Comparison' => 0, 'Major difference' => 0]],
            ['input' => ['a' => '1.0.0', 'b' => '1.0.1'], 'expect' => ['Result' => 'B is newer', 'Comparison' => -1, 'Patch difference' => -1]],
        ],
    ],
    [
        'slug' => 'http-status-lookup',
        'name' => 'HTTP Status Code Lookup',
        'category' => 'developer',
        'description' => 'Look up the name and meaning of an HTTP status code.',
        'icon' => 'globe',
        'keywords' => ['http', 'status code', '404', '500', 'api'],
        'inputs' => [
            ['name' => 'code', 'label' => 'Status code', 'type' => 'number', 'default' => 404, 'min' => 100, 'max' => 599, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const code = Math.trunc(h.n(v.code));
    const statuses = {
        100: ['Continue', 'The server received the request headers and the client should proceed.'],
        200: ['OK', 'The request succeeded and the response contains the result.'],
        201: ['Created', 'The request succeeded and a new resource was created.'],
        204: ['No Content', 'The request succeeded but there is no response body.'],
        301: ['Moved Permanently', 'The resource has permanently moved to a new URL.'],
        302: ['Found', 'The resource is temporarily available at another URL.'],
        304: ['Not Modified', 'The cached copy is still valid.'],
        400: ['Bad Request', 'The server cannot process the request because it is malformed.'],
        401: ['Unauthorized', 'Authentication is required and was not provided.'],
        403: ['Forbidden', 'The server understood the request but refuses to authorise it.'],
        404: ['Not Found', 'The requested resource could not be found.'],
        405: ['Method Not Allowed', 'The HTTP method is not supported for this resource.'],
        409: ['Conflict', 'The request conflicts with the current state of the resource.'],
        422: ['Unprocessable Content', 'The request is well formed but fails validation.'],
        429: ['Too Many Requests', 'The client has sent too many requests in a given time.'],
        500: ['Internal Server Error', 'The server encountered an unexpected condition.'],
        502: ['Bad Gateway', 'An upstream server returned an invalid response.'],
        503: ['Service Unavailable', 'The server is temporarily unable to handle the request.'],
        504: ['Gateway Timeout', 'An upstream server did not respond in time.'],
    };
    const category = code < 200 ? 'Informational' : code < 300 ? 'Success' : code < 400 ? 'Redirection' : code < 500 ? 'Client error' : 'Server error';
    const entry = statuses[code];
    return [
        { label: 'Code', value: code, decimals: 0 },
        { label: 'Name', value: entry ? entry[0] : 'Unknown status' },
        { label: 'Category', value: category },
        { label: 'Description', value: entry ? entry[1] : 'No standard description is available for this code.' },
    ];
JS,
        'tests' => [
            ['input' => ['code' => 200], 'expect' => ['Code' => 200, 'Name' => 'OK', 'Category' => 'Success']],
            ['input' => ['code' => 404], 'expect' => ['Name' => 'Not Found', 'Category' => 'Client error']],
            ['input' => ['code' => 500], 'expect' => ['Name' => 'Internal Server Error', 'Category' => 'Server error']],
            ['input' => ['code' => 301], 'expect' => ['Name' => 'Moved Permanently', 'Category' => 'Redirection']],
        ],
    ],
    [
        'slug' => 'color-contrast-checker',
        'name' => 'Colour Contrast Checker',
        'category' => 'developer',
        'description' => 'Check text and background colours against WCAG contrast thresholds.',
        'icon' => 'image',
        'keywords' => ['contrast', 'wcag', 'accessibility', 'colour', 'a11y'],
        'inputs' => [
            ['name' => 'foreground', 'label' => 'Text colour', 'type' => 'color', 'default' => '#000000', 'required' => true],
            ['name' => 'background', 'label' => 'Background colour', 'type' => 'color', 'default' => '#ffffff', 'required' => true],
        ],
        'compute' => <<<'JS'
    const expand = (value) => {
        const hex = String(value ?? '').trim().replace(/^#/, '');
        if (/^[0-9a-f]{3}$/i.test(hex)) return hex.split('').map((c) => c + c).join('');
        if (/^[0-9a-f]{6}$/i.test(hex)) return hex;
        throw new Error('Enter a hex colour such as #000000.');
    };
    const luminance = (hex) => {
        const channels = [0, 2, 4].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255).map((c) => (c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4)));
        return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
    };
    const l1 = luminance(expand(v.foreground));
    const l2 = luminance(expand(v.background));
    const ratio = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
    return [
        { label: 'Contrast ratio', value: ratio, decimals: 2 },
        { label: 'AA normal text', value: ratio >= 4.5 ? 'Pass' : 'Fail' },
        { label: 'AA large text', value: ratio >= 3 ? 'Pass' : 'Fail' },
        { label: 'AAA normal text', value: ratio >= 7 ? 'Pass' : 'Fail' },
        { label: 'AA interface', value: ratio >= 3 ? 'Pass' : 'Fail' },
    ];
JS,
        'tests' => [
            ['input' => ['foreground' => '#000000', 'background' => '#ffffff'], 'expect' => ['Contrast ratio' => 21, 'AA normal text' => 'Pass', 'AA large text' => 'Pass', 'AAA normal text' => 'Pass', 'AA interface' => 'Pass']],
            ['input' => ['foreground' => '#777777', 'background' => '#ffffff'], 'expect' => ['Contrast ratio' => 4.478089453577214, 'AA normal text' => 'Fail', 'AA large text' => 'Pass', 'AAA normal text' => 'Fail']],
            ['input' => ['foreground' => '#0000ff', 'background' => '#ffffff'], 'expect' => ['Contrast ratio' => 8.592471358428805, 'AA normal text' => 'Pass', 'AAA normal text' => 'Pass']],
        ],
    ],
];
