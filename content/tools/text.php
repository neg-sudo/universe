<?php
/**
 * UtilityWorld — Text
 * 12 tools. See docs/TOOL_SCHEMA.md for the definition format.
 */

return [
    [
        'slug' => 'word-counter',
        'name' => 'Word Counter',
        'category' => 'text',
        'description' => 'Count words, characters, sentences and paragraphs, and estimate reading time at 200 words per minute.',
        'icon' => 'type',
        'featured' => true,
        'keywords' => ['word count', 'words', 'reading time', 'paragraphs', 'sentences'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Your text',
                'type' => 'textarea',
                'default' => 'The quick brown fox jumps over the lazy dog.',
                'placeholder' => 'Paste or type your text here',
                'required' => true,
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '');
            if (raw.trim() === '') throw new Error('Enter some text to count.');
            const words = raw.trim().split(/\s+/).filter(Boolean);
            const sentences = raw.split(/[.!?]+/).map((s) => s.trim()).filter(Boolean).length;
            const paragraphs = raw.trim().split(/\n\s*\n/).filter((p) => p.trim() !== '').length;
            return [
                { label: 'Words', value: words.length, decimals: 0 },
                { label: 'Characters', value: Array.from(raw).length, decimals: 0 },
                { label: 'Characters without spaces', value: Array.from(raw.replace(/\s/g, '')).length, decimals: 0 },
                { label: 'Sentences', value: sentences, decimals: 0 },
                { label: 'Paragraphs', value: paragraphs, decimals: 0 },
                { label: 'Reading time', value: words.length / 200, unit: 'min', decimals: 2, hint: 'Based on 200 words per minute.' },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => 'The quick brown fox. It jumps over the lazy dog!'],
                'expect' => ['Words' => 10, 'Characters' => 48, 'Characters without spaces' => 39, 'Sentences' => 2, 'Paragraphs' => 1, 'Reading time' => 0.05],
            ],
            [
                'input' => ['text' => "Hello world!\n\nThis is a second paragraph. It has three sentences? Yes."],
                'expect' => ['Words' => 12, 'Sentences' => 4, 'Paragraphs' => 2, 'Reading time' => 0.06],
            ],
            [
                'input' => ['text' => 'Hello'],
                'expect' => ['Words' => 1, 'Characters' => 5, 'Sentences' => 1, 'Paragraphs' => 1],
            ],
        ],
    ],

    [
        'slug' => 'character-counter',
        'name' => 'Character Counter',
        'category' => 'text',
        'description' => 'Count total characters, letters, digits, spaces, punctuation marks and lines in your text.',
        'icon' => 'hash',
        'keywords' => ['character count', 'letters', 'digits', 'punctuation', 'lines'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Your text',
                'type' => 'textarea',
                'default' => 'Hello, World! 123',
                'placeholder' => 'Paste or type your text here',
                'required' => true,
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '');
            if (raw === '') throw new Error('Enter some text to count.');
            const count = (re) => (raw.match(re) || []).length;
            return [
                { label: 'Total characters', value: Array.from(raw).length, decimals: 0 },
                { label: 'Letters', value: count(/\p{L}/gu), decimals: 0 },
                { label: 'Digits', value: count(/\p{N}/gu), decimals: 0 },
                { label: 'Spaces', value: count(/ /g), decimals: 0 },
                { label: 'Punctuation', value: count(/\p{P}/gu), decimals: 0 },
                { label: 'Lines', value: raw.split(/\r?\n/).length, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => 'Hello, World! 123'],
                'expect' => ['Total characters' => 17, 'Letters' => 10, 'Digits' => 3, 'Spaces' => 2, 'Punctuation' => 2, 'Lines' => 1],
            ],
            [
                'input' => ['text' => "One\nTwo\nThree"],
                'expect' => ['Total characters' => 13, 'Letters' => 11, 'Digits' => 0, 'Spaces' => 0, 'Punctuation' => 0, 'Lines' => 3],
            ],
            [
                'input' => ['text' => 'abc'],
                'expect' => ['Total characters' => 3, 'Letters' => 3, 'Digits' => 0, 'Lines' => 1],
            ],
        ],
    ],

    [
        'slug' => 'case-converter',
        'name' => 'Case Converter',
        'category' => 'text',
        'description' => 'Convert text to uppercase, lowercase, title, sentence, camel, snake, kebab or alternating case.',
        'icon' => 'case-sensitive',
        'featured' => true,
        'keywords' => ['uppercase', 'lowercase', 'title case', 'camelcase', 'snake case', 'kebab case'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Your text',
                'type' => 'textarea',
                'default' => 'hello world example',
                'placeholder' => 'Paste or type your text here',
                'required' => true,
            ],
            [
                'name' => 'mode',
                'label' => 'Case style',
                'type' => 'select',
                'default' => 'upper',
                'options' => [
                    ['value' => 'upper', 'label' => 'UPPERCASE'],
                    ['value' => 'lower', 'label' => 'lowercase'],
                    ['value' => 'title', 'label' => 'Title Case'],
                    ['value' => 'sentence', 'label' => 'Sentence case'],
                    ['value' => 'camel', 'label' => 'camelCase'],
                    ['value' => 'snake', 'label' => 'snake_case'],
                    ['value' => 'kebab', 'label' => 'kebab-case'],
                    ['value' => 'alternating', 'label' => 'aLtErNaTiNg'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '');
            if (raw.trim() === '') throw new Error('Enter some text to convert.');
            const words = raw.trim().split(/[^A-Za-z0-9]+/).filter(Boolean);
            let out;
            if (v.mode === 'upper') {
                out = raw.toUpperCase();
            } else if (v.mode === 'lower') {
                out = raw.toLowerCase();
            } else if (v.mode === 'title') {
                out = raw.toLowerCase().replace(/\S+/g, (w) => w.charAt(0).toUpperCase() + w.slice(1));
            } else if (v.mode === 'sentence') {
                out = raw.toLowerCase().replace(/(^\s*[a-z])|([.!?]\s+[a-z])/g, (m) => m.toUpperCase());
            } else if (v.mode === 'camel') {
                out = words.map((w, i) => i === 0 ? w.toLowerCase() : w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join('');
            } else if (v.mode === 'snake') {
                out = words.map((w) => w.toLowerCase()).join('_');
            } else if (v.mode === 'kebab') {
                out = words.map((w) => w.toLowerCase()).join('-');
            } else if (v.mode === 'alternating') {
                let lower = true;
                out = '';
                for (const ch of raw) {
                    if (/[a-z]/i.test(ch)) {
                        out += lower ? ch.toLowerCase() : ch.toUpperCase();
                        lower = !lower;
                    } else {
                        out += ch;
                    }
                }
            } else {
                throw new Error('Choose a case style.');
            }
            return { label: 'Converted text', value: out };
        JS,
        'tests' => [
            ['input' => ['text' => 'Hello World', 'mode' => 'upper'], 'expect' => ['Converted text' => 'HELLO WORLD']],
            ['input' => ['text' => 'hello world', 'mode' => 'title'], 'expect' => ['Converted text' => 'Hello World']],
            ['input' => ['text' => 'hello world example', 'mode' => 'camel'], 'expect' => ['Converted text' => 'helloWorldExample']],
            ['input' => ['text' => 'Hello World Example', 'mode' => 'snake'], 'expect' => ['Converted text' => 'hello_world_example']],
        ],
    ],

    [
        'slug' => 'slug-generator',
        'name' => 'Slug Generator',
        'category' => 'text',
        'description' => 'Turn any text into a clean URL slug with optional maximum length and hyphen or underscore separators.',
        'icon' => 'link',
        'keywords' => ['slug', 'url', 'permalink', 'seo', 'hyphen'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Text',
                'type' => 'text',
                'default' => 'Hello World!',
                'placeholder' => 'e.g. My First Blog Post',
                'required' => true,
            ],
            [
                'name' => 'maxLength',
                'label' => 'Maximum length',
                'type' => 'number',
                'default' => 0,
                'min' => 0,
                'step' => 1,
                'hint' => 'Use 0 for no limit.',
            ],
            [
                'name' => 'separator',
                'label' => 'Separator',
                'type' => 'select',
                'default' => '-',
                'options' => [
                    ['value' => '-', 'label' => 'Hyphen (-)'],
                    ['value' => '_', 'label' => 'Underscore (_)'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '').trim();
            if (raw === '') throw new Error('Enter some text to convert.');
            const sep = v.separator === '_' ? '_' : '-';
            const maxLength = Math.floor(h.n(v.maxLength ?? 0));
            if (maxLength < 0) throw new Error('The maximum length cannot be negative.');
            let slug = raw
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, sep);
            slug = slug.split(sep).filter(Boolean).join(sep);
            if (maxLength > 0 && slug.length > maxLength) {
                slug = slug.slice(0, maxLength).split(sep).filter(Boolean).join(sep);
            }
            if (slug === '') throw new Error('The text must contain letters or numbers.');
            return [
                { label: 'Slug', value: slug },
                { label: 'Length', value: slug.length, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['text' => 'Hello World!', 'maxLength' => 0, 'separator' => '-'], 'expect' => ['Slug' => 'hello-world', 'Length' => 11]],
            ['input' => ['text' => 'Café au lait', 'maxLength' => 0, 'separator' => '-'], 'expect' => ['Slug' => 'cafe-au-lait']],
            ['input' => ['text' => 'Hello World', 'maxLength' => 0, 'separator' => '_'], 'expect' => ['Slug' => 'hello_world']],
            ['input' => ['text' => 'The quick brown fox jumps', 'maxLength' => 9, 'separator' => '-'], 'expect' => ['Slug' => 'the-quick', 'Length' => 9]],
        ],
    ],

    [
        'slug' => 'reverse-text',
        'name' => 'Text Reverser',
        'category' => 'text',
        'description' => 'Reverse text by characters, words or lines instantly.',
        'icon' => 'arrow-left-right',
        'keywords' => ['reverse', 'backwards', 'mirror', 'flip'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Your text',
                'type' => 'textarea',
                'default' => 'Hello World',
                'placeholder' => 'Paste or type your text here',
                'required' => true,
            ],
            [
                'name' => 'mode',
                'label' => 'Reverse by',
                'type' => 'select',
                'default' => 'characters',
                'options' => [
                    ['value' => 'characters', 'label' => 'Characters'],
                    ['value' => 'words', 'label' => 'Words'],
                    ['value' => 'lines', 'label' => 'Lines'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '');
            if (raw.trim() === '') throw new Error('Enter some text to reverse.');
            let out;
            if (v.mode === 'characters') {
                out = Array.from(raw).reverse().join('');
            } else if (v.mode === 'words') {
                out = raw.trim().split(/\s+/).reverse().join(' ');
            } else if (v.mode === 'lines') {
                out = raw.split(/\r?\n/).reverse().join('\n');
            } else {
                throw new Error('Choose a reverse mode.');
            }
            return { label: 'Reversed text', value: out };
        JS,
        'tests' => [
            ['input' => ['text' => 'abc', 'mode' => 'characters'], 'expect' => ['Reversed text' => 'cba']],
            ['input' => ['text' => 'Hello', 'mode' => 'characters'], 'expect' => ['Reversed text' => 'olleH']],
            ['input' => ['text' => 'one two three', 'mode' => 'words'], 'expect' => ['Reversed text' => 'three two one']],
            ['input' => ['text' => "a\nb\nc", 'mode' => 'lines'], 'expect' => ['Reversed text' => "c\nb\na"]],
        ],
    ],

    [
        'slug' => 'remove-duplicate-lines',
        'name' => 'Duplicate Line Remover',
        'category' => 'text',
        'description' => 'Remove duplicate lines from a list while keeping the first occurrence of each line.',
        'icon' => 'filter',
        'keywords' => ['duplicate lines', 'deduplicate', 'unique lines', 'clean list'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Lines',
                'type' => 'textarea',
                'default' => "apple\nbanana\napple\ncherry\nbanana",
                'placeholder' => 'One item per line',
                'required' => true,
            ],
            [
                'name' => 'caseInsensitive',
                'label' => 'Ignore case when comparing',
                'type' => 'checkbox',
                'default' => false,
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '').replace(/\r\n?/g, '\n');
            if (raw.trim() === '') throw new Error('Enter some lines of text.');
            const lines = raw.replace(/\n$/, '').split('\n');
            const ci = v.caseInsensitive === true || v.caseInsensitive === '1' || v.caseInsensitive === 'on';
            const seen = new Set();
            const kept = [];
            for (const line of lines) {
                const key = ci ? line.toLowerCase() : line;
                if (seen.has(key)) continue;
                seen.add(key);
                kept.push(line);
            }
            return [
                { label: 'Cleaned text', value: kept.join('\n') },
                { label: 'Lines removed', value: lines.length - kept.length, decimals: 0 },
                { label: 'Lines remaining', value: kept.length, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => "a\nb\na\nc\nb", 'caseInsensitive' => false],
                'expect' => ['Cleaned text' => "a\nb\nc", 'Lines removed' => 2, 'Lines remaining' => 3],
            ],
            [
                'input' => ['text' => "Apple\napple\nBanana", 'caseInsensitive' => true],
                'expect' => ['Cleaned text' => "Apple\nBanana", 'Lines removed' => 1, 'Lines remaining' => 2],
            ],
            [
                'input' => ['text' => "Apple\napple\nBanana", 'caseInsensitive' => false],
                'expect' => ['Cleaned text' => "Apple\napple\nBanana", 'Lines removed' => 0, 'Lines remaining' => 3],
            ],
        ],
    ],

    [
        'slug' => 'sort-lines',
        'name' => 'Line Sorter',
        'category' => 'text',
        'description' => 'Sort lines alphabetically, in reverse, or by length, with optional case-insensitive matching.',
        'icon' => 'sort-asc',
        'keywords' => ['sort lines', 'alphabetical', 'line order', 'ascending', 'descending'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Lines',
                'type' => 'textarea',
                'default' => "banana\nApple\ncherry",
                'placeholder' => 'One item per line',
                'required' => true,
            ],
            [
                'name' => 'mode',
                'label' => 'Sort order',
                'type' => 'select',
                'default' => 'az',
                'options' => [
                    ['value' => 'az', 'label' => 'A → Z'],
                    ['value' => 'za', 'label' => 'Z → A'],
                    ['value' => 'shortest', 'label' => 'Shortest first'],
                    ['value' => 'longest', 'label' => 'Longest first'],
                ],
            ],
            [
                'name' => 'caseInsensitive',
                'label' => 'Ignore case when sorting',
                'type' => 'checkbox',
                'default' => false,
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '').replace(/\r\n?/g, '\n');
            if (raw.trim() === '') throw new Error('Enter some lines of text.');
            const lines = raw.replace(/\n$/, '').split('\n');
            const ci = v.caseInsensitive === true || v.caseInsensitive === '1' || v.caseInsensitive === 'on';
            const key = (line) => ci ? line.toLowerCase() : line;
            const sorted = [...lines].sort((a, b) => {
                if (v.mode === 'az' || v.mode === 'za') {
                    const ka = key(a);
                    const kb = key(b);
                    const cmp = ka < kb ? -1 : ka > kb ? 1 : 0;
                    return v.mode === 'az' ? cmp : -cmp;
                }
                if (v.mode === 'shortest') return a.length - b.length;
                if (v.mode === 'longest') return b.length - a.length;
                throw new Error('Choose a sort order.');
            });
            return [
                { label: 'Sorted text', value: sorted.join('\n') },
                { label: 'Line count', value: lines.length, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => "banana\nApple\ncherry", 'mode' => 'az', 'caseInsensitive' => true],
                'expect' => ['Sorted text' => "Apple\nbanana\ncherry", 'Line count' => 3],
            ],
            [
                'input' => ['text' => "a\nc\nb", 'mode' => 'za', 'caseInsensitive' => false],
                'expect' => ['Sorted text' => "c\nb\na"],
            ],
            [
                'input' => ['text' => "ccc\na\nbb", 'mode' => 'shortest', 'caseInsensitive' => false],
                'expect' => ['Sorted text' => "a\nbb\nccc"],
            ],
            [
                'input' => ['text' => "a\nccc\nbb", 'mode' => 'longest', 'caseInsensitive' => false],
                'expect' => ['Sorted text' => "ccc\nbb\na"],
            ],
        ],
    ],

    [
        'slug' => 'whitespace-remover',
        'name' => 'Whitespace Remover',
        'category' => 'text',
        'description' => 'Trim lines, collapse repeated spaces, delete blank lines or strip all whitespace from text.',
        'icon' => 'eraser',
        'keywords' => ['whitespace', 'trim', 'spaces', 'blank lines', 'clean text'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Your text',
                'type' => 'textarea',
                'default' => "  hello  \n  world  ",
                'placeholder' => 'Paste or type your text here',
                'required' => true,
            ],
            [
                'name' => 'mode',
                'label' => 'Cleanup mode',
                'type' => 'select',
                'default' => 'trim',
                'options' => [
                    ['value' => 'trim', 'label' => 'Trim each line'],
                    ['value' => 'collapse', 'label' => 'Collapse repeated spaces'],
                    ['value' => 'blank', 'label' => 'Remove blank lines'],
                    ['value' => 'all', 'label' => 'Remove all spaces'],
                ],
            ],
        ],
        'compute' => <<<'JS'
            const raw = String(v.text ?? '');
            if (raw.trim() === '') throw new Error('Enter some text to clean.');
            let cleaned;
            if (v.mode === 'trim') {
                cleaned = raw.replace(/\r\n?/g, '\n').split('\n').map((line) => line.trim()).join('\n');
            } else if (v.mode === 'collapse') {
                cleaned = raw.replace(/[ \t]+/g, ' ');
            } else if (v.mode === 'blank') {
                cleaned = raw.replace(/\r\n?/g, '\n').split('\n').filter((line) => line.trim() !== '').join('\n');
            } else if (v.mode === 'all') {
                cleaned = raw.replace(/[ \t]+/g, '');
            } else {
                throw new Error('Choose a cleanup mode.');
            }
            return [
                { label: 'Cleaned text', value: cleaned },
                { label: 'Characters saved', value: Array.from(raw).length - Array.from(cleaned).length, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => "  hello  \n  world  ", 'mode' => 'trim'],
                'expect' => ['Cleaned text' => "hello\nworld", 'Characters saved' => 8],
            ],
            [
                'input' => ['text' => "a   b\tc", 'mode' => 'collapse'],
                'expect' => ['Cleaned text' => 'a b c', 'Characters saved' => 2],
            ],
            [
                'input' => ['text' => "a\n\nb\n\nc", 'mode' => 'blank'],
                'expect' => ['Cleaned text' => "a\nb\nc", 'Characters saved' => 2],
            ],
            [
                'input' => ['text' => 'a b c', 'mode' => 'all'],
                'expect' => ['Cleaned text' => 'abc', 'Characters saved' => 2],
            ],
        ],
    ],

    [
        'slug' => 'lorem-ipsum-generator',
        'name' => 'Lorem Ipsum Generator',
        'category' => 'text',
        'description' => 'Generate random lorem ipsum placeholder text with your chosen paragraphs and sentences.',
        'icon' => 'align-left',
        'keywords' => ['lorem ipsum', 'placeholder', 'dummy text', 'filler text'],
        'inputs' => [
            [
                'name' => 'paragraphs',
                'label' => 'Paragraphs',
                'type' => 'number',
                'default' => 3,
                'min' => 1,
                'max' => 10,
                'step' => 1,
                'required' => true,
            ],
            [
                'name' => 'sentences',
                'label' => 'Sentences per paragraph',
                'type' => 'number',
                'default' => 4,
                'min' => 1,
                'max' => 8,
                'step' => 1,
                'required' => true,
            ],
        ],
        'compute' => <<<'JS'
            const paragraphs = Math.floor(h.n(v.paragraphs));
            const sentences = Math.floor(h.n(v.sentences));
            if (paragraphs < 1 || paragraphs > 10) throw new Error('Choose between 1 and 10 paragraphs.');
            if (sentences < 1 || sentences > 8) throw new Error('Choose between 1 and 8 sentences per paragraph.');
            const pool = 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua enim ad minim veniam quis nostrud exercitation ullamco laboris nisi aliquip ex ea commodo consequat duis aute irure in reprehenderit voluptate velit esse cillum eu fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt culpa qui officia deserunt mollit anim id est laborum'.split(' ');
            const makeSentence = () => {
                const length = 8 + Math.floor(Math.random() * 7);
                const words = [];
                for (let i = 0; i < length; i++) {
                    words.push(pool[Math.floor(Math.random() * pool.length)]);
                }
                words[0] = words[0].charAt(0).toUpperCase() + words[0].slice(1);
                return words.join(' ') + '.';
            };
            const blocks = [];
            for (let i = 0; i < paragraphs; i++) {
                const lines = [];
                for (let j = 0; j < sentences; j++) lines.push(makeSentence());
                blocks.push(lines.join(' '));
            }
            return [
                { label: 'Generated text', value: blocks.join('\n\n') },
                { label: 'Paragraphs', value: paragraphs, decimals: 0 },
                { label: 'Sentences', value: paragraphs * sentences, decimals: 0 },
            ];
        JS,
        'tests' => [
            ['input' => ['paragraphs' => 2, 'sentences' => 3], 'expect' => ['Paragraphs' => 2, 'Sentences' => 6]],
            ['input' => ['paragraphs' => 1, 'sentences' => 1], 'expect' => ['Paragraphs' => 1, 'Sentences' => 1]],
            ['input' => ['paragraphs' => 10, 'sentences' => 8], 'expect' => ['Paragraphs' => 10, 'Sentences' => 80]],
        ],
    ],

    [
        'slug' => 'palindrome-checker',
        'name' => 'Palindrome Checker',
        'category' => 'text',
        'description' => 'Check whether text reads the same forwards and backwards, ignoring case and punctuation.',
        'icon' => 'repeat',
        'keywords' => ['palindrome', 'reverse', 'mirror', 'word game'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Text to check',
                'type' => 'textarea',
                'default' => 'A man, a plan, a canal: Panama',
                'placeholder' => 'e.g. Never odd or even',
                'required' => true,
            ],
        ],
        'compute' => <<<'JS'
            const cleaned = String(v.text ?? '').toLowerCase().replace(/[^a-z0-9]/g, '');
            if (cleaned === '') throw new Error('Enter some letters or digits to check.');
            const reversed = Array.from(cleaned).reverse().join('');
            return [
                { label: 'Is palindrome', value: cleaned === reversed ? 'Yes' : 'No' },
                { label: 'Cleaned text', value: cleaned },
                { label: 'Reversed text', value: reversed },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => 'A man, a plan, a canal: Panama'],
                'expect' => ['Is palindrome' => 'Yes', 'Cleaned text' => 'amanaplanacanalpanama', 'Reversed text' => 'amanaplanacanalpanama'],
            ],
            [
                'input' => ['text' => 'Hello'],
                'expect' => ['Is palindrome' => 'No', 'Cleaned text' => 'hello', 'Reversed text' => 'olleh'],
            ],
            [
                'input' => ['text' => 'Was it a car or a cat I saw?'],
                'expect' => ['Is palindrome' => 'Yes', 'Cleaned text' => 'wasitacaroracatisaw'],
            ],
            [
                'input' => ['text' => '12321'],
                'expect' => ['Is palindrome' => 'Yes', 'Reversed text' => '12321'],
            ],
        ],
    ],

    [
        'slug' => 'anagram-checker',
        'name' => 'Anagram Checker',
        'category' => 'text',
        'description' => 'Check whether two words or phrases are anagrams by comparing their sorted letters.',
        'icon' => 'shuffle',
        'keywords' => ['anagram', 'letters', 'word game', 'compare words'],
        'inputs' => [
            [
                'name' => 'a',
                'label' => 'First word or phrase',
                'type' => 'text',
                'default' => 'Listen',
                'placeholder' => 'e.g. listen',
                'required' => true,
            ],
            [
                'name' => 'b',
                'label' => 'Second word or phrase',
                'type' => 'text',
                'default' => 'Silent',
                'placeholder' => 'e.g. silent',
                'required' => true,
            ],
        ],
        'compute' => <<<'JS'
            const letters = (text) => String(text ?? '').toLowerCase().replace(/[^a-z]/g, '').split('').sort().join('');
            const a = letters(v.a);
            const b = letters(v.b);
            if (a === '' || b === '') throw new Error('Enter letters in both fields.');
            return [
                { label: 'Is anagram', value: a === b ? 'Yes' : 'No' },
                { label: 'Sorted letters A', value: a },
                { label: 'Sorted letters B', value: b },
            ];
        JS,
        'tests' => [
            [
                'input' => ['a' => 'Listen', 'b' => 'Silent'],
                'expect' => ['Is anagram' => 'Yes', 'Sorted letters A' => 'eilnst', 'Sorted letters B' => 'eilnst'],
            ],
            [
                'input' => ['a' => 'Dormitory', 'b' => 'dirty room'],
                'expect' => ['Is anagram' => 'Yes', 'Sorted letters A' => 'dimoorrty', 'Sorted letters B' => 'dimoorrty'],
            ],
            [
                'input' => ['a' => 'hello', 'b' => 'world'],
                'expect' => ['Is anagram' => 'No', 'Sorted letters A' => 'ehllo', 'Sorted letters B' => 'dlorw'],
            ],
        ],
    ],

    [
        'slug' => 'word-frequency-counter',
        'name' => 'Word Frequency Counter',
        'category' => 'text',
        'description' => 'Find the most frequent words in text, ranked by count and then alphabetically.',
        'icon' => 'bar-chart-3',
        'keywords' => ['word frequency', 'count words', 'most common words', 'text analysis'],
        'inputs' => [
            [
                'name' => 'text',
                'label' => 'Your text',
                'type' => 'textarea',
                'default' => 'The cat and the dog and the bird',
                'placeholder' => 'Paste or type your text here',
                'required' => true,
            ],
            [
                'name' => 'top',
                'label' => 'Number of words to show',
                'type' => 'number',
                'default' => 5,
                'min' => 1,
                'max' => 50,
                'step' => 1,
                'required' => true,
            ],
        ],
        'compute' => <<<'JS'
            const tokens = (String(v.text ?? '').toLowerCase().match(/[a-z0-9']+/g) || [])
                .map((word) => word.replace(/^'+|'+$/g, ''))
                .filter(Boolean);
            if (tokens.length === 0) throw new Error('Enter some words to count.');
            const top = Math.floor(h.n(v.top));
            if (top < 1) throw new Error('Choose at least one word to show.');
            const counts = new Map();
            for (const word of tokens) counts.set(word, (counts.get(word) || 0) + 1);
            const ranked = [...counts.entries()].sort((a, b) => {
                if (b[1] !== a[1]) return b[1] - a[1];
                return a[0] < b[0] ? -1 : a[0] > b[0] ? 1 : 0;
            });
            const shown = ranked.slice(0, top);
            return [
                { label: 'Top words', value: shown.map(([word, count]) => `${word} (${count})`).join(', ') },
                { label: 'Unique words', value: counts.size, decimals: 0 },
            ];
        JS,
        'tests' => [
            [
                'input' => ['text' => 'The cat and the dog and the bird', 'top' => 3],
                'expect' => ['Top words' => 'the (3), and (2), bird (1)', 'Unique words' => 5],
            ],
            [
                'input' => ['text' => 'a b b c c c', 'top' => 2],
                'expect' => ['Top words' => 'c (3), b (2)', 'Unique words' => 3],
            ],
            [
                'input' => ['text' => 'One one TWO two two', 'top' => 5],
                'expect' => ['Top words' => 'two (3), one (2)', 'Unique words' => 2],
            ],
        ],
    ],
];
