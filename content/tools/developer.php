<?php
/**
 * UtilityWorld — Developer
 * 14 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'base64-encoder-decoder',
        'name' => 'Base64 Encoder / Decoder',
        'category' => 'developer',
        'description' => 'Encode text to Base64 or decode Base64 back to UTF-8 text.',
        'icon' => 'code',
        'featured' => true,
        'keywords' => ['base64', 'encode', 'decode', 'utf-8', 'text'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Mode',
                'type' => 'select',
                'default' => 'encode',
                'options' => [
                    ['value' => 'encode', 'label' => 'Encode text to Base64'],
                    ['value' => 'decode', 'label' => 'Decode Base64 to text'],
                ],
            ],
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Hello, UniVerse!', 'required' => true, 'hint' => 'UTF-8 is supported. Decoding accepts URL-safe characters and missing padding.'],
        ],
        'compute' => <<<'JS'
            const text = String(v.text ?? '');
            if (v.mode === 'encode') {
                const bytes = new TextEncoder().encode(text);
                let binary = '';
                const chunkSize = 0x8000;
                for (let i = 0; i < bytes.length; i += chunkSize) {
                    binary += String.fromCharCode(...bytes.subarray(i, i + chunkSize));
                }
                return { label: 'Base64', value: btoa(binary) };
            }
            const clean = text.replace(/\s+/g, '').replace(/-/g, '+').replace(/_/g, '/');
            if (!/^[A-Za-z0-9+/]*={0,2}$/.test(clean)) throw new Error('That does not look like valid Base64 text.');
            const padded = clean + '='.repeat((4 - (clean.length % 4)) % 4);
            let binary;
            try {
                binary = atob(padded);
            } catch {
                throw new Error('That does not look like valid Base64 text.');
            }
            const bytes = Uint8Array.from(binary, (c) => c.charCodeAt(0));
            try {
                return { label: 'Decoded text', value: new TextDecoder('utf-8', { fatal: true }).decode(bytes) };
            } catch {
                throw new Error('The decoded bytes are not valid UTF-8 text.');
            }
        JS,
        'tests' => [
            ['input' => ['mode' => 'encode', 'text' => 'abc'], 'expect' => ['Base64' => 'YWJj']],
            ['input' => ['mode' => 'decode', 'text' => 'SGVsbG8='], 'expect' => ['Decoded text' => 'Hello']],
            ['input' => ['mode' => 'encode', 'text' => 'Hello, UniVerse!'], 'expect' => ['Base64' => 'SGVsbG8sIFVuaVZlcnNlIQ==']],
            ['input' => ['mode' => 'decode', 'text' => 'SGVsbG8sIFVuaVZlcnNlIQ'], 'expect' => ['Decoded text' => 'Hello, UniVerse!']],
        ],
    ],

    [
        'slug' => 'url-encoder-decoder',
        'name' => 'URL Encoder / Decoder',
        'category' => 'developer',
        'description' => 'Percent-encode or decode URL components and complete URLs.',
        'icon' => 'link',
        'featured' => false,
        'keywords' => ['url', 'encode', 'decode', 'percent encoding', 'uri'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Mode',
                'type' => 'select',
                'default' => 'encode',
                'options' => [
                    ['value' => 'encode', 'label' => 'Encode'],
                    ['value' => 'decode', 'label' => 'Decode'],
                ],
            ],
            [
                'name' => 'scope',
                'label' => 'Scope',
                'type' => 'select',
                'default' => 'component',
                'options' => [
                    ['value' => 'component', 'label' => 'Component (encodeURIComponent)'],
                    ['value' => 'full', 'label' => 'Full URL (encodeURI)'],
                ],
            ],
            ['name' => 'text', 'label' => 'Text or URL', 'type' => 'textarea', 'default' => 'a b&c', 'required' => true],
        ],
        'compute' => <<<'JS'
            const text = String(v.text ?? '');
            const component = v.scope !== 'full';
            try {
                if (v.mode === 'decode') {
                    return { label: 'Decoded text', value: component ? decodeURIComponent(text) : decodeURI(text) };
                }
                return { label: 'Encoded URL', value: component ? encodeURIComponent(text) : encodeURI(text) };
            } catch {
                throw new Error('That input contains an invalid percent-encoding sequence.');
            }
        JS,
        'tests' => [
            ['input' => ['mode' => 'encode', 'scope' => 'component', 'text' => 'a b&c'], 'expect' => ['Encoded URL' => 'a%20b%26c']],
            ['input' => ['mode' => 'decode', 'scope' => 'component', 'text' => 'a%20b%26c'], 'expect' => ['Decoded text' => 'a b&c']],
            ['input' => ['mode' => 'encode', 'scope' => 'full', 'text' => 'https://example.com/a b?x=1&y=2'], 'expect' => ['Encoded URL' => 'https://example.com/a%20b?x=1&y=2']],
        ],
    ],

    [
        'slug' => 'hex-decimal-converter',
        'name' => 'Hex ↔ Decimal Converter',
        'category' => 'developer',
        'description' => 'Convert between hexadecimal and decimal, with binary and octal output.',
        'icon' => 'binary',
        'featured' => false,
        'keywords' => ['hex', 'decimal', 'binary', 'octal', 'number base'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Conversion',
                'type' => 'select',
                'default' => 'hex-to-decimal',
                'options' => [
                    ['value' => 'hex-to-decimal', 'label' => 'Hexadecimal → decimal'],
                    ['value' => 'decimal-to-hex', 'label' => 'Decimal → hexadecimal'],
                ],
            ],
            ['name' => 'value', 'label' => 'Value', 'type' => 'text', 'default' => 'FF', 'required' => true, 'hint' => 'Use digits 0-9 and A-F for hexadecimal.'],
        ],
        'compute' => <<<'JS'
            const raw = String(v.value ?? '').trim();
            if (!raw) throw new Error('Enter a value to convert.');
            if (v.mode === 'hex-to-decimal') {
                const hex = raw.replace(/^0x/i, '');
                if (!/^[0-9a-f]+$/i.test(hex)) throw new Error('Enter a valid hexadecimal number using 0-9 and A-F.');
                const decimal = parseInt(hex, 16);
                if (!Number.isSafeInteger(decimal)) throw new Error('That number is too large to convert safely.');
                return [
                    { label: 'Decimal', value: decimal, decimals: 0 },
                    { label: 'Hexadecimal', value: '0x' + hex.toUpperCase().replace(/^0+(?=.)/, '') },
                    { label: 'Binary', value: '0b' + decimal.toString(2) },
                    { label: 'Octal', value: '0o' + decimal.toString(8) },
                ];
            }
            if (!/^\d+$/.test(raw)) throw new Error('Enter a valid non-negative decimal integer.');
            const decimal = Number(raw);
            if (!Number.isSafeInteger(decimal)) throw new Error('That number is too large to convert safely.');
            return [
                { label: 'Decimal', value: decimal, decimals: 0 },
                { label: 'Hexadecimal', value: '0x' + decimal.toString(16).toUpperCase() },
                { label: 'Binary', value: '0b' + decimal.toString(2) },
                { label: 'Octal', value: '0o' + decimal.toString(8) },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'hex-to-decimal', 'value' => 'FF'], 'expect' => [
                'Decimal' => 255,
                'Hexadecimal' => '0xFF',
                'Binary' => '0b11111111',
                'Octal' => '0o377',
            ]],
            ['input' => ['mode' => 'decimal-to-hex', 'value' => '255'], 'expect' => [
                'Decimal' => 255,
                'Hexadecimal' => '0xFF',
                'Binary' => '0b11111111',
                'Octal' => '0o377',
            ]],
            ['input' => ['mode' => 'hex-to-decimal', 'value' => '0x10'], 'expect' => [
                'Decimal' => 16,
                'Hexadecimal' => '0x10',
                'Binary' => '0b10000',
                'Octal' => '0o20',
            ]],
        ],
    ],

    [
        'slug' => 'binary-converter',
        'name' => 'Binary Converter',
        'category' => 'developer',
        'description' => 'Convert between decimal, binary and UTF-8 text.',
        'icon' => 'binary',
        'featured' => false,
        'keywords' => ['binary', 'decimal', 'text', 'utf-8', 'bits'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Conversion',
                'type' => 'select',
                'default' => 'decimal-to-binary',
                'options' => [
                    ['value' => 'decimal-to-binary', 'label' => 'Decimal → binary'],
                    ['value' => 'binary-to-decimal', 'label' => 'Binary → decimal'],
                    ['value' => 'text-to-binary', 'label' => 'Text → binary'],
                    ['value' => 'binary-to-text', 'label' => 'Binary → text'],
                ],
            ],
            ['name' => 'value', 'label' => 'Value', 'type' => 'textarea', 'default' => '10', 'required' => true, 'hint' => 'Binary text uses groups of 8 bits; spaces between groups are optional.'],
        ],
        'compute' => <<<'JS'
            const raw = String(v.value ?? '').trim();
            if (!raw) throw new Error('Enter a value to convert.');
            if (v.mode === 'decimal-to-binary') {
                if (!/^\d+$/.test(raw)) throw new Error('Enter a non-negative whole number.');
                const number = Number(raw);
                if (!Number.isSafeInteger(number)) throw new Error('That number is too large to convert safely.');
                return { label: 'Binary', value: number.toString(2) };
            }
            if (v.mode === 'binary-to-decimal') {
                const bits = raw.replace(/\s+/g, '');
                if (!/^[01]+$/.test(bits)) throw new Error('Enter a binary number containing only 0 and 1.');
                const decimal = parseInt(bits, 2);
                if (!Number.isSafeInteger(decimal)) throw new Error('That binary number is too large to convert safely.');
                return { label: 'Decimal', value: decimal, decimals: 0 };
            }
            if (v.mode === 'text-to-binary') {
                const bytes = new TextEncoder().encode(raw);
                const groups = Array.from(bytes, (byte) => byte.toString(2).padStart(8, '0'));
                return { label: 'Binary', value: groups.join(' ') };
            }
            const bits = raw.replace(/\s+/g, '');
            if (!/^[01]+$/.test(bits) || bits.length % 8 !== 0) throw new Error('Binary text must contain groups of 8 bits using only 0 and 1.');
            const bytes = new Uint8Array(bits.length / 8);
            for (let i = 0; i < bytes.length; i++) bytes[i] = parseInt(bits.slice(i * 8, i * 8 + 8), 2);
            try {
                return { label: 'Text', value: new TextDecoder('utf-8', { fatal: true }).decode(bytes) };
            } catch {
                throw new Error('Those bytes are not valid UTF-8 text.');
            }
        JS,
        'tests' => [
            ['input' => ['mode' => 'decimal-to-binary', 'value' => '10'], 'expect' => ['Binary' => '1010']],
            ['input' => ['mode' => 'text-to-binary', 'value' => 'A'], 'expect' => ['Binary' => '01000001']],
            ['input' => ['mode' => 'binary-to-text', 'value' => '01000001'], 'expect' => ['Text' => 'A']],
            ['input' => ['mode' => 'binary-to-decimal', 'value' => '1010'], 'expect' => ['Decimal' => 10]],
        ],
    ],

    [
        'slug' => 'uuid-generator',
        'name' => 'UUID Generator',
        'category' => 'developer',
        'description' => 'Generate random RFC 4122 version 4 UUIDs in batches.',
        'icon' => 'fingerprint',
        'featured' => false,
        'keywords' => ['uuid', 'guid', 'random', 'identifier', 'v4'],
        'inputs' => [
            ['name' => 'count', 'label' => 'How many UUIDs?', 'type' => 'number', 'default' => 5, 'min' => 1, 'max' => 20, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const count = Math.floor(h.n(v.count));
            if (!Number.isInteger(count) || count < 1 || count > 20) throw new Error('Choose between 1 and 20 UUIDs.');
            const fallback = () => 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
                const random = Math.floor(Math.random() * 16);
                const value = c === 'x' ? random : (random & 0x3) | 0x8;
                return value.toString(16);
            });
            const makeUuid = () => {
                if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
                    try {
                        return crypto.randomUUID();
                    } catch {
                        // Fall through to the manual RFC 4122 v4 generator.
                    }
                }
                return fallback();
            };
            const uuids = Array.from({ length: count }, makeUuid);
            return [
                { label: 'UUIDs', value: uuids.join('\n') },
                { label: 'Count', value: count, decimals: 0 },
                { label: 'Version', value: '4' },
            ];
        JS,
        'tests' => [
            ['input' => ['count' => 3], 'expect' => ['Count' => 3, 'Version' => '4']],
            ['input' => ['count' => 1], 'expect' => ['Count' => 1, 'Version' => '4']],
        ],
    ],

    [
        'slug' => 'json-formatter',
        'name' => 'JSON Formatter',
        'category' => 'developer',
        'description' => 'Pretty-print, minify or validate JSON and inspect its structure.',
        'icon' => 'braces',
        'featured' => true,
        'keywords' => ['json', 'format', 'pretty print', 'minify', 'validate'],
        'inputs' => [
            ['name' => 'json', 'label' => 'JSON', 'type' => 'textarea', 'default' => '{"name":"UniVerse","count":12,"tags":["a","b"]}', 'required' => true],
            [
                'name' => 'mode',
                'label' => 'Mode',
                'type' => 'select',
                'default' => 'pretty',
                'options' => [
                    ['value' => 'pretty', 'label' => 'Pretty-print (2 spaces)'],
                    ['value' => 'minify', 'label' => 'Minify'],
                    ['value' => 'validate', 'label' => 'Validate'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.json ?? '').trim();
            if (!raw) throw new Error('Paste some JSON to format.');
            let data;
            try {
                data = JSON.parse(raw);
            } catch (error) {
                throw new Error('Invalid JSON: ' + error.message);
            }
            const pretty = JSON.stringify(data, null, 2);
            const minified = JSON.stringify(data);
            const output = v.mode === 'minify' || v.mode === 'validate' ? minified : pretty;
            const type = data === null ? 'null' : Array.isArray(data) ? 'array' : typeof data;
            const entries = data !== null && typeof data === 'object'
                ? (Array.isArray(data) ? data.length : Object.keys(data).length)
                : 0;
            const size = new TextEncoder().encode(output).length;
            const results = [
                { label: 'Formatted JSON', value: output },
                { label: 'Size', value: size, unit: 'bytes', decimals: 0 },
                { label: 'Top-level type', value: type },
                { label: 'Top-level keys', value: entries, decimals: 0 },
            ];
            if (v.mode === 'validate') results.unshift({ label: 'Status', value: 'Valid JSON' });
            return results;
        JS,
        'tests' => [
            ['input' => ['json' => '{"a":1,"b":[2,3]}', 'mode' => 'pretty'], 'expect' => [
                'Formatted JSON' => "{\n  \"a\": 1,\n  \"b\": [\n    2,\n    3\n  ]\n}",
                'Size' => 39,
                'Top-level type' => 'object',
                'Top-level keys' => 2,
            ]],
            ['input' => ['json' => "{\n  \"a\": 1,\n  \"b\": [2, 3]\n}", 'mode' => 'minify'], 'expect' => [
                'Formatted JSON' => '{"a":1,"b":[2,3]}',
                'Size' => 17,
            ]],
            ['input' => ['json' => '[1,2,3]', 'mode' => 'validate'], 'expect' => [
                'Status' => 'Valid JSON',
                'Formatted JSON' => '[1,2,3]',
                'Top-level type' => 'array',
                'Top-level keys' => 3,
            ]],
        ],
    ],

    [
        'slug' => 'hash-generator',
        'name' => 'Hash Generator',
        'category' => 'developer',
        'description' => 'Generate SHA-1, SHA-256 and SHA-512 hashes from text.',
        'icon' => 'hash',
        'featured' => false,
        'keywords' => ['hash', 'sha256', 'sha1', 'sha512', 'digest'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'abc', 'required' => true],
            [
                'name' => 'algorithm',
                'label' => 'Algorithm',
                'type' => 'select',
                'default' => 'SHA-256',
                'options' => [
                    ['value' => 'SHA-256', 'label' => 'SHA-256'],
                    ['value' => 'SHA-1', 'label' => 'SHA-1'],
                    ['value' => 'SHA-512', 'label' => 'SHA-512'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const text = String(v.text ?? '');
            const algorithm = v.algorithm === 'SHA-1' ? 'SHA-1' : v.algorithm === 'SHA-512' ? 'SHA-512' : 'SHA-256';
            if (typeof crypto === 'undefined' || !crypto.subtle) throw new Error('Hashing is not available in this browser.');
            const data = new TextEncoder().encode(text);
            return crypto.subtle.digest(algorithm, data).then((buffer) => {
                const bytes = new Uint8Array(buffer);
                let hex = '';
                for (let i = 0; i < bytes.length; i++) hex += bytes[i].toString(16).padStart(2, '0');
                return [
                    { label: 'Hash', value: hex },
                    { label: 'Algorithm', value: algorithm },
                    { label: 'Bit length', value: bytes.length * 8, decimals: 0 },
                ];
            });
        JS,
        'tests' => [
            ['input' => ['text' => 'abc', 'algorithm' => 'SHA-256'], 'expect' => [
                'Hash' => 'ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad',
                'Algorithm' => 'SHA-256',
                'Bit length' => 256,
            ]],
            ['input' => ['text' => 'abc', 'algorithm' => 'SHA-1'], 'expect' => [
                'Hash' => 'a9993e364706816aba3e25717850c26c9cd0d89d',
                'Algorithm' => 'SHA-1',
                'Bit length' => 160,
            ]],
            ['input' => ['text' => 'abc', 'algorithm' => 'SHA-512'], 'expect' => [
                'Hash' => 'ddaf35a193617abacc417349ae20413112e6fa4e89a97ea20a9eeee64b55d39a2192992a274fc1a836ba3c23a3feebbd454d4423643ce80e2a9ac94fa54ca49f',
                'Bit length' => 512,
            ]],
        ],
    ],

    [
        'slug' => 'color-converter',
        'name' => 'Color Converter',
        'category' => 'developer',
        'description' => 'Convert a hex colour to RGB and HSL with a contrast hint.',
        'icon' => 'palette',
        'featured' => false,
        'keywords' => ['color', 'hex', 'rgb', 'hsl', 'contrast'],
        'inputs' => [
            ['name' => 'hex', 'label' => 'Hex colour', 'type' => 'text', 'default' => '#ff0000', 'required' => true, 'placeholder' => '#ff0000 or #f00', 'hint' => 'Accepts 3-digit and 6-digit hex colours.'],
        ],
        'compute' => <<<'JS'
            const raw = String(v.hex ?? '').trim().replace(/^#/, '');
            let hex;
            if (/^[0-9a-f]{3}$/i.test(raw)) {
                hex = raw.split('').map((c) => c + c).join('');
            } else if (/^[0-9a-f]{6}$/i.test(raw)) {
                hex = raw;
            } else {
                throw new Error('Enter a hex colour such as #ff0000 or #f00.');
            }
            const r = parseInt(hex.slice(0, 2), 16);
            const g = parseInt(hex.slice(2, 4), 16);
            const b = parseInt(hex.slice(4, 6), 16);
            const rn = r / 255;
            const gn = g / 255;
            const bn = b / 255;
            const max = Math.max(rn, gn, bn);
            const min = Math.min(rn, gn, bn);
            const lightness = (max + min) / 2;
            let hue = 0;
            let saturation = 0;
            if (max !== min) {
                const delta = max - min;
                saturation = lightness > 0.5 ? delta / (2 - max - min) : delta / (max + min);
                if (max === rn) hue = (gn - bn) / delta + (gn < bn ? 6 : 0);
                else if (max === gn) hue = (bn - rn) / delta + 2;
                else hue = (rn - gn) / delta + 4;
                hue *= 60;
            }
            const fmt = (value) => String(Math.round(value * 10) / 10);
            const toLinear = (channel) => channel <= 0.04045
                ? channel / 12.92
                : Math.pow((channel + 0.055) / 1.055, 2.4);
            const luminance = 0.2126 * toLinear(rn) + 0.7152 * toLinear(gn) + 0.0722 * toLinear(bn);
            const contrast = (a, b) => (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
            const note = luminance > 0.179
                ? 'Use dark text (black) for the best contrast.'
                : 'Use light text (white) for the best contrast.';
            return [
                { label: 'HEX', value: '#' + hex.toUpperCase() },
                { label: 'RGB', value: `rgb(${r}, ${g}, ${b})` },
                { label: 'HSL', value: `hsl(${fmt(hue)}, ${fmt(saturation * 100)}%, ${fmt(lightness * 100)}%)` },
                { label: 'Luminance', value: h.round(luminance, 4), decimals: 4 },
                { label: 'Contrast with white', value: h.round(contrast(luminance, 1), 2), decimals: 2 },
                { label: 'Contrast with black', value: h.round(contrast(luminance, 0), 2), decimals: 2 },
                { label: 'Contrast note', value: note },
            ];
        JS,
        'tests' => [
            ['input' => ['hex' => '#ff0000'], 'expect' => [
                'HEX' => '#FF0000',
                'RGB' => 'rgb(255, 0, 0)',
                'HSL' => 'hsl(0, 100%, 50%)',
                'Luminance' => 0.2126,
            ]],
            ['input' => ['hex' => '#ffffff'], 'expect' => [
                'HEX' => '#FFFFFF',
                'RGB' => 'rgb(255, 255, 255)',
                'HSL' => 'hsl(0, 0%, 100%)',
                'Luminance' => 1,
            ]],
            ['input' => ['hex' => '#0f0'], 'expect' => [
                'HEX' => '#00FF00',
                'RGB' => 'rgb(0, 255, 0)',
                'HSL' => 'hsl(120, 100%, 50%)',
            ]],
        ],
    ],

    [
        'slug' => 'unix-timestamp-converter',
        'name' => 'Unix Timestamp Converter',
        'category' => 'developer',
        'description' => 'Convert Unix timestamps to UTC dates and dates back to timestamps.',
        'icon' => 'clock',
        'featured' => false,
        'keywords' => ['unix timestamp', 'epoch', 'date', 'utc', 'iso 8601'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Mode',
                'type' => 'select',
                'default' => 'timestamp-to-date',
                'options' => [
                    ['value' => 'timestamp-to-date', 'label' => 'Timestamp → date'],
                    ['value' => 'date-to-timestamp', 'label' => 'Date → timestamp'],
                ],
            ],
            ['name' => 'value', 'label' => 'Timestamp or date', 'type' => 'text', 'default' => '0', 'required' => true, 'hint' => 'Timestamps are in seconds. Dates such as 2024-01-01 are treated as UTC.'],
        ],
        'compute' => <<<'JS'
            const raw = String(v.value ?? '').trim();
            if (!raw) throw new Error('Enter a timestamp or date.');
            let date;
            if (v.mode === 'date-to-timestamp') {
                const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/);
                date = match
                    ? new Date(Date.UTC(Number(match[1]), Number(match[2]) - 1, Number(match[3]), Number(match[4] || 0), Number(match[5] || 0), Number(match[6] || 0)))
                    : new Date(raw);
            } else {
                if (!/^-?\d+(?:\.\d+)?$/.test(raw)) throw new Error('Enter a Unix timestamp in seconds.');
                const seconds = Number(raw);
                if (!Number.isFinite(seconds)) throw new Error('Enter a valid Unix timestamp.');
                date = new Date(seconds * 1000);
            }
            if (Number.isNaN(date.getTime())) throw new Error('Enter a valid date, such as 2024-01-01 or 2024-01-01T12:00:00Z.');
            const iso = date.toISOString();
            const seconds = Math.floor(date.getTime() / 1000);
            return [
                { label: 'ISO 8601 (UTC)', value: iso },
                { label: 'Unix timestamp', value: seconds, decimals: 0 },
                { label: 'Human readable (UTC)', value: iso.slice(0, 10) + ' ' + iso.slice(11, 19) + ' UTC' },
            ];
        JS,
        'tests' => [
            ['input' => ['mode' => 'timestamp-to-date', 'value' => '0'], 'expect' => [
                'ISO 8601 (UTC)' => '1970-01-01T00:00:00.000Z',
                'Unix timestamp' => 0,
                'Human readable (UTC)' => '1970-01-01 00:00:00 UTC',
            ]],
            ['input' => ['mode' => 'date-to-timestamp', 'value' => '2024-01-01'], 'expect' => [
                'ISO 8601 (UTC)' => '2024-01-01T00:00:00.000Z',
                'Unix timestamp' => 1704067200,
            ]],
            ['input' => ['mode' => 'timestamp-to-date', 'value' => '1704067200'], 'expect' => [
                'ISO 8601 (UTC)' => '2024-01-01T00:00:00.000Z',
                'Unix timestamp' => 1704067200,
            ]],
        ],
    ],

    [
        'slug' => 'html-entity-encoder',
        'name' => 'HTML Entity Encoder / Decoder',
        'category' => 'developer',
        'description' => 'Encode and decode HTML entities, including numeric references.',
        'icon' => 'code-xml',
        'featured' => false,
        'keywords' => ['html', 'entities', 'encode', 'decode', 'escape'],
        'inputs' => [
            [
                'name' => 'mode',
                'label' => 'Mode',
                'type' => 'select',
                'default' => 'encode',
                'options' => [
                    ['value' => 'encode', 'label' => 'Encode'],
                    ['value' => 'decode', 'label' => 'Decode'],
                ],
            ],
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => '<a href="x">', 'required' => true],
        ],
        'compute' => <<<'JS'
            const text = String(v.text ?? '');
            if (v.mode === 'encode') {
                const named = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
                let output = '';
                for (const character of text) {
                    if (named[character]) {
                        output += named[character];
                    } else {
                        const codePoint = character.codePointAt(0);
                        output += codePoint > 127 ? `&#${codePoint};` : character;
                    }
                }
                return { label: 'Encoded', value: output };
            }
            const named = { amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", '#39': "'" };
            const decoded = text.replace(/&(#x?[0-9a-f]+|[a-z][a-z0-9]*);/gi, (match, entity) => {
                const key = entity.toLowerCase();
                if (Object.prototype.hasOwnProperty.call(named, key)) return named[key];
                if (key.startsWith('#x')) {
                    const codePoint = parseInt(key.slice(2), 16);
                    if (!Number.isFinite(codePoint)) return match;
                    try { return String.fromCodePoint(codePoint); } catch { return match; }
                }
                if (key.startsWith('#')) {
                    const codePoint = parseInt(key.slice(1), 10);
                    if (!Number.isFinite(codePoint)) return match;
                    try { return String.fromCodePoint(codePoint); } catch { return match; }
                }
                return match;
            });
            return { label: 'Decoded', value: decoded };
        JS,
        'tests' => [
            ['input' => ['mode' => 'encode', 'text' => '<a href="x">'], 'expect' => ['Encoded' => '&lt;a href=&quot;x&quot;&gt;']],
            ['input' => ['mode' => 'decode', 'text' => '&lt;a href=&quot;x&quot;&gt;'], 'expect' => ['Decoded' => '<a href="x">']],
            ['input' => ['mode' => 'encode', 'text' => 'café & tea'], 'expect' => ['Encoded' => 'caf&#233; &amp; tea']],
        ],
    ],

    [
        'slug' => 'password-generator',
        'name' => 'Password Generator',
        'category' => 'developer',
        'description' => 'Generate random passwords with a chosen length and character set.',
        'icon' => 'key-round',
        'featured' => false,
        'keywords' => ['password', 'generator', 'random', 'secure', 'entropy'],
        'inputs' => [
            ['name' => 'length', 'label' => 'Length', 'type' => 'number', 'default' => 16, 'min' => 8, 'max' => 64, 'step' => 1, 'required' => true],
            ['name' => 'uppercase', 'label' => 'Uppercase letters (A-Z)', 'type' => 'checkbox', 'default' => true],
            ['name' => 'lowercase', 'label' => 'Lowercase letters (a-z)', 'type' => 'checkbox', 'default' => true],
            ['name' => 'numbers', 'label' => 'Numbers (0-9)', 'type' => 'checkbox', 'default' => true],
            ['name' => 'symbols', 'label' => 'Symbols', 'type' => 'checkbox', 'default' => true],
            ['name' => 'count', 'label' => 'How many passwords?', 'type' => 'number', 'default' => 3, 'min' => 1, 'max' => 10, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const length = Math.floor(h.n(v.length));
            const count = Math.floor(h.n(v.count));
            if (!Number.isInteger(length) || length < 8 || length > 64) throw new Error('Choose a length between 8 and 64 characters.');
            if (!Number.isInteger(count) || count < 1 || count > 10) throw new Error('Choose between 1 and 10 passwords.');
            const enabled = (value) => value === undefined || value === null || value === true || value === '1' || value === 'on';
            const classes = [];
            if (enabled(v.uppercase)) classes.push('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            if (enabled(v.lowercase)) classes.push('abcdefghijklmnopqrstuvwxyz');
            if (enabled(v.numbers)) classes.push('0123456789');
            if (enabled(v.symbols)) classes.push('!@#$%^&*()_+-=[]{}|;:,.<>?');
            if (classes.length === 0) throw new Error('Enable at least one character type.');
            if (length < classes.length) throw new Error('The password is too short for the selected character types.');
            const pool = classes.join('');
            const randomIndex = (max) => Math.floor(Math.random() * max);
            const makePassword = () => {
                const characters = classes.map((characters) => characters[randomIndex(characters.length)]);
                while (characters.length < length) characters.push(pool[randomIndex(pool.length)]);
                for (let i = characters.length - 1; i > 0; i--) {
                    const j = randomIndex(i + 1);
                    const temp = characters[i];
                    characters[i] = characters[j];
                    characters[j] = temp;
                }
                return characters.join('');
            };
            const passwords = Array.from({ length: count }, makePassword);
            return [
                { label: 'Passwords', value: passwords.join('\n') },
                { label: 'Length', value: length, decimals: 0 },
                { label: 'Entropy', value: length * Math.log2(pool.length), unit: 'bits', decimals: 2 },
                { label: 'Count', value: count, decimals: 0 },
                { label: 'Pool size', value: pool.length, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['length' => 16, 'uppercase' => true, 'lowercase' => true, 'numbers' => true, 'symbols' => false, 'count' => 2], 'expect' => [
                'Length' => 16,
                'Entropy' => 95.2671409662,
                'Count' => 2,
                'Pool size' => 62,
            ]],
            ['input' => ['length' => 8, 'uppercase' => true, 'lowercase' => true, 'numbers' => true, 'symbols' => true, 'count' => 1], 'expect' => [
                'Length' => 8,
                'Entropy' => 51.6754529491,
                'Count' => 1,
                'Pool size' => 88,
            ]],
            ['input' => ['length' => 12, 'uppercase' => false, 'lowercase' => true, 'numbers' => false, 'symbols' => false, 'count' => 3], 'expect' => [
                'Length' => 12,
                'Entropy' => 56.4052766177,
                'Count' => 3,
                'Pool size' => 26,
            ]],
        ],
    ],

    [
        'slug' => 'jwt-decoder',
        'name' => 'JWT Decoder',
        'category' => 'developer',
        'description' => 'Decode a JWT header and payload without verifying the signature.',
        'icon' => 'key-round',
        'featured' => false,
        'keywords' => ['jwt', 'token', 'decode', 'json web token', 'claims'],
        'inputs' => [
            ['name' => 'token', 'label' => 'JWT', 'type' => 'textarea', 'default' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.signature', 'required' => true, 'hint' => 'The signature is never verified; treat the decoded claims as untrusted.'],
        ],
        'compute' => <<<'JS'
            const token = String(v.token ?? '').trim();
            const parts = token.split('.');
            if (parts.length !== 3) throw new Error('A JWT must have three dot-separated parts.');
            const decodePart = (part, name) => {
                const clean = part.replace(/\s+/g, '').replace(/-/g, '+').replace(/_/g, '/');
                const padded = clean + '='.repeat((4 - (clean.length % 4)) % 4);
                let binary;
                try {
                    binary = atob(padded);
                } catch {
                    throw new Error('The ' + name + ' is not valid Base64URL.');
                }
                const bytes = Uint8Array.from(binary, (c) => c.charCodeAt(0));
                try {
                    return JSON.parse(new TextDecoder('utf-8', { fatal: true }).decode(bytes));
                } catch {
                    throw new Error('The ' + name + ' is not valid JSON.');
                }
            };
            const header = decodePart(parts[0], 'header');
            const payload = decodePart(parts[1], 'payload');
            const formatTime = (value) => {
                const number = Number(value);
                if (!Number.isFinite(number)) return '—';
                try {
                    const iso = new Date(number * 1000).toISOString();
                    return iso.slice(0, 10) + ' ' + iso.slice(11, 19) + ' UTC';
                } catch {
                    return '—';
                }
            };
            return [
                { label: 'Header', value: JSON.stringify(header, null, 2) },
                { label: 'Payload', value: JSON.stringify(payload, null, 2) },
                { label: 'Subject', value: payload && payload.sub !== undefined ? String(payload.sub) : '—' },
                { label: 'Name', value: payload && payload.name !== undefined ? String(payload.name) : '—' },
                { label: 'Issued at', value: payload && payload.iat !== undefined ? formatTime(payload.iat) : '—' },
                { label: 'Expires at', value: payload && payload.exp !== undefined ? formatTime(payload.exp) : 'No expiration' },
                { label: 'Status', value: 'Not verified — the signature is not checked.' },
            ];
        JS,
        'tests' => [
            ['input' => ['token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.signature'], 'expect' => [
                'Header' => "{\n  \"alg\": \"HS256\",\n  \"typ\": \"JWT\"\n}",
                'Subject' => '1234567890',
                'Name' => 'John Doe',
                'Expires at' => 'No expiration',
                'Status' => 'Not verified — the signature is not checked.',
            ]],
            ['input' => ['token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiI0MiIsIm5hbWUiOiJKYW5lIiwiaWF0IjoxNzAwMDAwMDAwLCJleHAiOjE3MDAwMDM2MDB9.sig'], 'expect' => [
                'Name' => 'Jane',
                'Issued at' => '2023-11-14 22:13:20 UTC',
                'Expires at' => '2023-11-14 23:13:20 UTC',
            ]],
        ],
    ],

    [
        'slug' => 'markdown-table-generator',
        'name' => 'Markdown Table Generator',
        'category' => 'developer',
        'description' => 'Turn CSV or TSV data into a Markdown table with column alignment.',
        'icon' => 'table',
        'featured' => false,
        'keywords' => ['markdown', 'table', 'csv', 'tsv', 'generator'],
        'inputs' => [
            ['name' => 'csv', 'label' => 'CSV or TSV data', 'type' => 'textarea', 'default' => "name,age\nAlice,30\nBob,25", 'required' => true, 'hint' => 'The first row becomes the table header.'],
            [
                'name' => 'delimiter',
                'label' => 'Delimiter',
                'type' => 'select',
                'default' => 'comma',
                'options' => [
                    ['value' => 'comma', 'label' => 'Comma'],
                    ['value' => 'tab', 'label' => 'Tab'],
                    ['value' => 'semicolon', 'label' => 'Semicolon'],
                ],
            ],
            [
                'name' => 'alignment',
                'label' => 'Alignment',
                'type' => 'select',
                'default' => 'left',
                'options' => [
                    ['value' => 'left', 'label' => 'Left'],
                    ['value' => 'center', 'label' => 'Center'],
                    ['value' => 'right', 'label' => 'Right'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.csv ?? '').replace(/\r\n?/g, '\n');
            const lines = raw.split('\n').filter((line) => line.trim() !== '');
            if (lines.length === 0) throw new Error('Paste some CSV or TSV data.');
            const delimiter = v.delimiter === 'tab' ? '\t' : v.delimiter === 'semicolon' ? ';' : ',';
            const parseLine = (line) => {
                const cells = [];
                let current = '';
                let quoted = false;
                for (let i = 0; i < line.length; i++) {
                    const character = line[i];
                    if (quoted) {
                        if (character === '"') {
                            if (line[i + 1] === '"') {
                                current += '"';
                                i++;
                            } else {
                                quoted = false;
                            }
                        } else {
                            current += character;
                        }
                    } else if (character === '"') {
                        quoted = true;
                    } else if (character === delimiter) {
                        cells.push(current);
                        current = '';
                    } else {
                        current += character;
                    }
                }
                cells.push(current);
                return cells.map((cell) => cell.trim());
            };
            const rows = lines.map(parseLine);
            const width = Math.max(...rows.map((row) => row.length));
            const normalized = rows.map((row) => {
                const copy = row.slice();
                while (copy.length < width) copy.push('');
                return copy;
            });
            const escapeCell = (cell) => cell.replace(/\|/g, '\\|').replace(/\n/g, '<br>');
            const marker = v.alignment === 'center' ? ':---:' : v.alignment === 'right' ? '---:' : ':---';
            const header = normalized[0];
            const body = normalized.slice(1);
            const output = [];
            output.push('| ' + header.map(escapeCell).join(' | ') + ' |');
            output.push('| ' + Array.from({ length: width }, () => marker).join(' | ') + ' |');
            for (const row of body) output.push('| ' + row.map(escapeCell).join(' | ') + ' |');
            return [
                { label: 'Markdown table', value: output.join('\n') },
                { label: 'Rows', value: body.length, decimals: 0 },
                { label: 'Columns', value: width, decimals: 0 },
                { label: 'Total rows', value: normalized.length, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['csv' => "name,age\nAlice,30\nBob,25", 'delimiter' => 'comma', 'alignment' => 'left'], 'expect' => [
                'Markdown table' => "| name | age |\n| :--- | :--- |\n| Alice | 30 |\n| Bob | 25 |",
                'Rows' => 2,
                'Columns' => 2,
                'Total rows' => 3,
            ]],
            ['input' => ['csv' => "product\tprice\nWidget\t9.99", 'delimiter' => 'tab', 'alignment' => 'right'], 'expect' => [
                'Markdown table' => "| product | price |\n| ---: | ---: |\n| Widget | 9.99 |",
                'Rows' => 1,
                'Columns' => 2,
            ]],
            ['input' => ['csv' => "name,note\n\"Doe, John\",ok", 'delimiter' => 'comma', 'alignment' => 'center'], 'expect' => [
                'Markdown table' => "| name | note |\n| :---: | :---: |\n| Doe, John | ok |",
                'Rows' => 1,
                'Columns' => 2,
            ]],
        ],
    ],

    [
        'slug' => 'number-base-converter',
        'name' => 'Number Base Converter',
        'category' => 'developer',
        'description' => 'Convert numbers between any bases from 2 to 36.',
        'icon' => 'binary',
        'featured' => false,
        'keywords' => ['number base', 'radix', 'binary', 'hexadecimal', 'convert'],
        'inputs' => [
            ['name' => 'value', 'label' => 'Value', 'type' => 'text', 'default' => 'ff', 'required' => true, 'hint' => 'Digits 0-9 and letters A-Z are accepted, depending on the source base.'],
            ['name' => 'fromBase', 'label' => 'From base', 'type' => 'number', 'default' => 16, 'min' => 2, 'max' => 36, 'step' => 1, 'required' => true],
            ['name' => 'toBase', 'label' => 'To base', 'type' => 'number', 'default' => 2, 'min' => 2, 'max' => 36, 'step' => 1, 'required' => true],
        ],
        'compute' => <<<'JS'
            const digits = '0123456789abcdefghijklmnopqrstuvwxyz';
            const fromBase = Math.floor(h.n(v.fromBase));
            const toBase = Math.floor(h.n(v.toBase));
            if (!Number.isInteger(fromBase) || fromBase < 2 || fromBase > 36) throw new Error('The source base must be between 2 and 36.');
            if (!Number.isInteger(toBase) || toBase < 2 || toBase > 36) throw new Error('The target base must be between 2 and 36.');
            let raw = String(v.value ?? '').trim().toLowerCase();
            if (!raw) throw new Error('Enter a value to convert.');
            let negative = false;
            if (raw.startsWith('-')) {
                negative = true;
                raw = raw.slice(1);
            }
            if (raw.startsWith('0x') || raw.startsWith('0b') || raw.startsWith('0o')) {
                const prefixBase = raw.startsWith('0x') ? 16 : raw.startsWith('0b') ? 2 : 8;
                if (prefixBase === fromBase) raw = raw.slice(2);
            }
            if (!raw) throw new Error('Enter a value to convert.');
            let value = 0n;
            const sourceBase = BigInt(fromBase);
            for (const character of raw) {
                const digit = digits.indexOf(character);
                if (digit < 0 || digit >= fromBase) throw new Error(`"${character}" is not a valid digit in base ${fromBase}.`);
                value = value * sourceBase + BigInt(digit);
            }
            if (negative) value = -value;
            const toDigits = (number, base) => {
                if (number === 0n) return '0';
                const radix = BigInt(base);
                const isNegative = number < 0n;
                if (isNegative) number = -number;
                let output = '';
                while (number > 0n) {
                    output = digits[Number(number % radix)] + output;
                    number /= radix;
                }
                return isNegative ? '-' + output : output;
            };
            const converted = toDigits(value, toBase);
            const decimal = value >= BigInt(Number.MIN_SAFE_INTEGER) && value <= BigInt(Number.MAX_SAFE_INTEGER)
                ? Number(value)
                : value.toString();
            return [
                { label: 'Converted value', value: converted },
                { label: 'Decimal value', value: decimal },
                { label: 'Digit count', value: raw.length, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['value' => 'ff', 'fromBase' => 16, 'toBase' => 2], 'expect' => [
                'Converted value' => '11111111',
                'Decimal value' => 255,
                'Digit count' => 2,
            ]],
            ['input' => ['value' => '1010', 'fromBase' => 2, 'toBase' => 10], 'expect' => [
                'Converted value' => '10',
                'Decimal value' => 10,
                'Digit count' => 4,
            ]],
            ['input' => ['value' => '0xff', 'fromBase' => 16, 'toBase' => 10], 'expect' => [
                'Converted value' => '255',
                'Decimal value' => 255,
                'Digit count' => 2,
            ]],
        ],
    ],
];
