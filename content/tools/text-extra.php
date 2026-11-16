<?php
/**
 * UniVerse — Text and writing (expanded catalogue).
 * 12 additional tools. See docs/TOOL_SCHEMA.md.
 */

return [
    [
        'slug' => 'sentence-counter',
        'name' => 'Sentence Counter',
        'category' => 'text',
        'description' => 'Count sentences, words, paragraphs and characters in your text.',
        'icon' => 'type',
        'keywords' => ['sentences', 'paragraphs', 'words', 'characters', 'writing'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Hello world. This is a test!', 'required' => true],
        ],
        'compute' => <<<'JS'
    const text = String(v.text ?? '');
    const trimmed = text.trim();
    const words = trimmed ? trimmed.split(/\s+/).filter(Boolean) : [];
    const sentences = trimmed ? trimmed.split(/[.!?]+/).map((s) => s.trim()).filter(Boolean).length : 0;
    const paragraphs = trimmed ? trimmed.split(/\n\s*\n/).map((p) => p.trim()).filter(Boolean).length : 0;
    return [
        { label: 'Sentences', value: sentences, decimals: 0 },
        { label: 'Words', value: words.length, decimals: 0 },
        { label: 'Paragraphs', value: paragraphs, decimals: 0 },
        { label: 'Characters', value: text.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'Hello world. This is a test!'], 'expect' => ['Sentences' => 2, 'Words' => 6, 'Paragraphs' => 1, 'Characters' => 28]],
            ['input' => ['text' => 'One two three'], 'expect' => ['Sentences' => 1, 'Words' => 3, 'Paragraphs' => 1, 'Characters' => 13]],
            ['input' => ['text' => "First.\n\nSecond?"], 'expect' => ['Sentences' => 2, 'Words' => 2, 'Paragraphs' => 2, 'Characters' => 15]],
        ],
    ],
    [
        'slug' => 'vowel-counter',
        'name' => 'Vowel and Consonant Counter',
        'category' => 'text',
        'description' => 'Count vowels, consonants, letters and digits in a piece of text.',
        'icon' => 'type',
        'keywords' => ['vowels', 'consonants', 'letters', 'digits', 'text analysis'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Hello World 123!', 'required' => true],
        ],
        'compute' => <<<'JS'
    const text = String(v.text ?? '');
    const letters = text.match(/[a-z]/gi) || [];
    const vowels = text.match(/[aeiou]/gi) || [];
    const digits = text.match(/\d/g) || [];
    return [
        { label: 'Vowels', value: vowels.length, decimals: 0 },
        { label: 'Consonants', value: letters.length - vowels.length, decimals: 0 },
        { label: 'Letters', value: letters.length, decimals: 0 },
        { label: 'Digits', value: digits.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'Hello World 123!'], 'expect' => ['Vowels' => 3, 'Consonants' => 7, 'Letters' => 10, 'Digits' => 3]],
            ['input' => ['text' => 'AEIOU'], 'expect' => ['Vowels' => 5, 'Consonants' => 0, 'Letters' => 5, 'Digits' => 0]],
            ['input' => ['text' => 'xyz'], 'expect' => ['Vowels' => 0, 'Consonants' => 3]],
        ],
    ],
    [
        'slug' => 'text-statistics',
        'name' => 'Text Statistics',
        'category' => 'text',
        'description' => 'Measure unique words, average word length, longest word and more.',
        'icon' => 'chart-line',
        'keywords' => ['text statistics', 'unique words', 'word length', 'reading'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'The quick brown fox', 'required' => true],
        ],
        'compute' => <<<'JS'
    const text = String(v.text ?? '');
    const words = (text.match(/[A-Za-z0-9'’-]+/g) || []);
    const unique = new Set(words.map((w) => w.toLowerCase()));
    const totalLetters = words.reduce((sum, w) => sum + w.replace(/[^A-Za-z]/g, '').length, 0);
    const longest = words.reduce((best, w) => (w.length > best.length ? w : best), '');
    return [
        { label: 'Words', value: words.length, decimals: 0 },
        { label: 'Unique words', value: unique.size, decimals: 0 },
        { label: 'Average word length', value: words.length ? totalLetters / words.length : 0 },
        { label: 'Longest word', value: longest || '—' },
        { label: 'Characters', value: text.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'The quick brown fox'], 'expect' => ['Words' => 4, 'Unique words' => 4, 'Average word length' => 4, 'Longest word' => 'quick', 'Characters' => 19]],
            ['input' => ['text' => 'a a b'], 'expect' => ['Words' => 3, 'Unique words' => 2, 'Average word length' => 1, 'Longest word' => 'a']],
        ],
    ],
    [
        'slug' => 'strip-html-tags',
        'name' => 'Strip HTML Tags',
        'category' => 'text',
        'description' => 'Remove HTML markup and decode common entities to leave plain text.',
        'icon' => 'code',
        'keywords' => ['html', 'strip', 'plain text', 'tags', 'clean'],
        'inputs' => [
            ['name' => 'html', 'label' => 'HTML', 'type' => 'textarea', 'default' => '<p>Hello <strong>world</strong></p>', 'required' => true],
        ],
        'compute' => <<<'JS'
    let text = String(v.html ?? '').replace(/<script[\s\S]*?<\/script>/gi, ' ').replace(/<style[\s\S]*?<\/style>/gi, ' ');
    text = text.replace(/<[^>]*>/g, ' ');
    const entities = { '&nbsp;': ' ', '&amp;': '&', '&lt;': '<', '&gt;': '>', '&quot;': '"', '&#39;': "'" };
    text = text.replace(/&(?:nbsp|amp|lt|gt|quot|#39);/g, (m) => entities[m] || m);
    text = text.replace(/[ \t]+/g, ' ').replace(/\s*\n\s*/g, '\n').trim();
    const words = text ? text.split(/\s+/).filter(Boolean) : [];
    return [
        { label: 'Text', value: text },
        { label: 'Characters', value: text.length, decimals: 0 },
        { label: 'Words', value: words.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['html' => '<p>Hello <strong>world</strong></p>'], 'expect' => ['Text' => 'Hello world', 'Characters' => 11, 'Words' => 2]],
            ['input' => ['html' => 'A &amp; B'], 'expect' => ['Text' => 'A & B', 'Characters' => 5, 'Words' => 3]],
        ],
    ],
    [
        'slug' => 'line-numberer',
        'name' => 'Line Numberer',
        'category' => 'text',
        'description' => 'Add sequential numbers to every line with a choice of separators.',
        'icon' => 'list',
        'keywords' => ['line numbers', 'numbering', 'list', 'text'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Lines', 'type' => 'textarea', 'default' => "alpha\nbeta", 'required' => true],
            ['name' => 'start', 'label' => 'Start at', 'type' => 'number', 'default' => 1, 'step' => '1', 'required' => true],
            ['name' => 'format', 'label' => 'Format', 'type' => 'select', 'default' => 'dot', 'options' => [
                ['value' => 'dot', 'label' => '1. text'],
                ['value' => 'colon', 'label' => '1: text'],
                ['value' => 'paren', 'label' => '1) text'],
            ]],
        ],
        'compute' => <<<'JS'
    const lines = String(v.text ?? '').split(/\r?\n/);
    const start = Math.trunc(h.n(v.start));
    const separators = { dot: '. ', colon: ': ', paren: ') ' };
    const separator = separators[v.format] || '. ';
    const numbered = lines.map((line, i) => `${start + i}${separator}${line}`);
    return [
        { label: 'Numbered text', value: numbered.join('\n') },
        { label: 'Lines', value: lines.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => "alpha\nbeta", 'start' => 1, 'format' => 'dot'], 'expect' => ['Numbered text' => "1. alpha\n2. beta", 'Lines' => 2]],
            ['input' => ['text' => "alpha\nbeta", 'start' => 5, 'format' => 'colon'], 'expect' => ['Numbered text' => "5: alpha\n6: beta"]],
        ],
    ],
    [
        'slug' => 'text-padding',
        'name' => 'Text Padding',
        'category' => 'text',
        'description' => 'Pad text to a fixed width on the left, right or both sides.',
        'icon' => 'type',
        'keywords' => ['padding', 'align', 'width', 'format', 'text'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'text', 'default' => 'cat', 'required' => true],
            ['name' => 'width', 'label' => 'Total width', 'type' => 'number', 'default' => 6, 'step' => '1', 'required' => true],
            ['name' => 'align', 'label' => 'Alignment', 'type' => 'select', 'default' => 'left', 'options' => [
                ['value' => 'left', 'label' => 'Left (pad right)'],
                ['value' => 'right', 'label' => 'Right (pad left)'],
                ['value' => 'center', 'label' => 'Center'],
            ]],
            ['name' => 'pad', 'label' => 'Pad character', 'type' => 'text', 'default' => '.'],
        ],
        'compute' => <<<'JS'
    const text = String(v.text ?? '');
    const width = Math.max(0, Math.trunc(h.n(v.width)));
    const pad = String(v.pad ?? ' ') || ' ';
    if (text.length >= width) {
        return [
            { label: 'Padded text', value: text },
            { label: 'Original length', value: text.length, decimals: 0 },
            { label: 'Final length', value: text.length, decimals: 0 },
        ];
    }
    const total = width - text.length;
    let padded = text;
    if (v.align === 'right') {
        padded = pad.repeat(total) + text;
    } else if (v.align === 'center') {
        const left = Math.floor(total / 2);
        padded = pad.repeat(left) + text + pad.repeat(total - left);
    } else {
        padded = text + pad.repeat(total);
    }
    return [
        { label: 'Padded text', value: padded },
        { label: 'Original length', value: text.length, decimals: 0 },
        { label: 'Final length', value: padded.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'cat', 'width' => 6, 'align' => 'left', 'pad' => '.'], 'expect' => ['Padded text' => 'cat...', 'Original length' => 3, 'Final length' => 6]],
            ['input' => ['text' => 'cat', 'width' => 6, 'align' => 'right', 'pad' => '.'], 'expect' => ['Padded text' => '...cat']],
            ['input' => ['text' => 'cat', 'width' => 6, 'align' => 'center', 'pad' => '.'], 'expect' => ['Padded text' => '.cat..']],
        ],
    ],
    [
        'slug' => 'truncate-text',
        'name' => 'Truncate Text',
        'category' => 'text',
        'description' => 'Shorten text to a maximum length and add a custom suffix.',
        'icon' => 'type',
        'keywords' => ['truncate', 'shorten', 'ellipsis', 'limit', 'text'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'The quick brown fox', 'required' => true],
            ['name' => 'max', 'label' => 'Maximum length', 'type' => 'number', 'default' => 10, 'step' => '1', 'required' => true],
            ['name' => 'suffix', 'label' => 'Suffix', 'type' => 'text', 'default' => '…'],
        ],
        'compute' => <<<'JS'
    const text = String(v.text ?? '');
    const max = Math.max(1, Math.trunc(h.n(v.max)));
    const suffix = String(v.suffix ?? '');
    if (text.length <= max) {
        return [
            { label: 'Truncated text', value: text },
            { label: 'Original length', value: text.length, decimals: 0 },
            { label: 'Truncated length', value: text.length, decimals: 0 },
        ];
    }
    const keep = Math.max(0, max - suffix.length);
    const output = suffix.length >= max ? suffix.slice(0, max) : text.slice(0, keep) + suffix;
    return [
        { label: 'Truncated text', value: output },
        { label: 'Original length', value: text.length, decimals: 0 },
        { label: 'Truncated length', value: output.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'The quick brown fox', 'max' => 10, 'suffix' => '…'], 'expect' => ['Truncated text' => 'The quick…', 'Original length' => 19, 'Truncated length' => 10]],
            ['input' => ['text' => 'The quick brown fox', 'max' => 10, 'suffix' => '...'], 'expect' => ['Truncated text' => 'The qui...', 'Truncated length' => 10]],
            ['input' => ['text' => 'short', 'max' => 10, 'suffix' => '…'], 'expect' => ['Truncated text' => 'short', 'Truncated length' => 5]],
        ],
    ],
    [
        'slug' => 'wrap-text',
        'name' => 'Text Wrapper',
        'category' => 'text',
        'description' => 'Wrap long text into lines of a chosen character width.',
        'icon' => 'type',
        'keywords' => ['wrap', 'line length', 'format', 'text'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'The quick brown fox jumps', 'required' => true],
            ['name' => 'width', 'label' => 'Line width', 'type' => 'number', 'default' => 10, 'step' => '1', 'required' => true],
        ],
        'compute' => <<<'JS'
    const words = String(v.text ?? '').trim().split(/\s+/).filter(Boolean);
    const width = Math.max(1, Math.trunc(h.n(v.width)));
    const lines = [];
    let current = '';
    for (const word of words) {
        if (current === '') {
            current = word;
        } else if (current.length + 1 + word.length <= width) {
            current += ' ' + word;
        } else {
            lines.push(current);
            current = word;
        }
    }
    if (current !== '') lines.push(current);
    return [
        { label: 'Wrapped text', value: lines.join('\n') },
        { label: 'Lines', value: lines.length, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'The quick brown fox jumps', 'width' => 10], 'expect' => ['Wrapped text' => "The quick\nbrown fox\njumps", 'Lines' => 3]],
            ['input' => ['text' => 'The quick brown fox jumps', 'width' => 20], 'expect' => ['Wrapped text' => "The quick brown fox\njumps", 'Lines' => 2]],
        ],
    ],
    [
        'slug' => 'find-and-replace',
        'name' => 'Find and Replace',
        'category' => 'text',
        'description' => 'Replace every occurrence of a word or phrase, with case control.',
        'icon' => 'search',
        'keywords' => ['find', 'replace', 'substitute', 'text'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'The cat sat on the mat', 'required' => true],
            ['name' => 'find', 'label' => 'Find', 'type' => 'text', 'default' => 'cat', 'required' => true],
            ['name' => 'replace', 'label' => 'Replace with', 'type' => 'text', 'default' => 'dog'],
            ['name' => 'caseSensitive', 'label' => 'Match case', 'type' => 'checkbox', 'default' => false],
        ],
        'compute' => <<<'JS'
    const text = String(v.text ?? '');
    const find = String(v.find ?? '');
    if (find === '') throw new Error('Enter something to find.');
    const replace = String(v.replace ?? '');
    const flags = v.caseSensitive ? 'g' : 'gi';
    const escaped = find.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(escaped, flags);
    const matches = text.match(regex);
    return [
        { label: 'Result', value: text.replace(regex, replace) },
        { label: 'Replacements', value: matches ? matches.length : 0, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'The cat sat on the mat', 'find' => 'cat', 'replace' => 'dog', 'caseSensitive' => false], 'expect' => ['Result' => 'The dog sat on the mat', 'Replacements' => 1]],
            ['input' => ['text' => 'The cat sat on the mat', 'find' => 'a', 'replace' => 'o', 'caseSensitive' => false], 'expect' => ['Result' => 'The cot sot on the mot', 'Replacements' => 3]],
            ['input' => ['text' => 'Cat cat', 'find' => 'cat', 'replace' => 'dog', 'caseSensitive' => true], 'expect' => ['Result' => 'Cat dog', 'Replacements' => 1]],
        ],
    ],
    [
        'slug' => 'caesar-cipher',
        'name' => 'Caesar Cipher',
        'category' => 'text',
        'description' => 'Encode or decode text with a Caesar shift while preserving case.',
        'icon' => 'lock',
        'keywords' => ['caesar', 'cipher', 'encrypt', 'decrypt', 'shift'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Hello, World!', 'required' => true],
            ['name' => 'shift', 'label' => 'Shift', 'type' => 'number', 'default' => 3, 'step' => '1', 'required' => true],
            ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'default' => 'encode', 'options' => [
                ['value' => 'encode', 'label' => 'Encode'],
                ['value' => 'decode', 'label' => 'Decode'],
            ]],
        ],
        'compute' => <<<'JS'
    const shift = ((Math.trunc(h.n(v.shift)) % 26) + 26) % 26;
    const amount = v.mode === 'decode' ? (26 - shift) % 26 : shift;
    const result = String(v.text ?? '').replace(/[a-z]/gi, (ch) => {
        const base = ch >= 'a' && ch <= 'z' ? 97 : 65;
        return String.fromCharCode(((ch.charCodeAt(0) - base + amount) % 26) + base);
    });
    return [
        { label: 'Result', value: result },
        { label: 'Shift', value: shift, decimals: 0 },
    ];
JS,
        'tests' => [
            ['input' => ['text' => 'Hello, World!', 'shift' => 3, 'mode' => 'encode'], 'expect' => ['Result' => 'Khoor, Zruog!', 'Shift' => 3]],
            ['input' => ['text' => 'Khoor, Zruog!', 'shift' => 3, 'mode' => 'decode'], 'expect' => ['Result' => 'Hello, World!']],
            ['input' => ['text' => 'abc', 'shift' => 0, 'mode' => 'encode'], 'expect' => ['Result' => 'abc', 'Shift' => 0]],
        ],
    ],
    [
        'slug' => 'rot13-converter',
        'name' => 'ROT13 Converter',
        'category' => 'text',
        'description' => 'Apply the ROT13 letter substitution used for simple obfuscation.',
        'icon' => 'lock',
        'keywords' => ['rot13', 'cipher', 'obfuscate', 'text'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea', 'default' => 'Hello', 'required' => true],
        ],
        'compute' => <<<'JS'
    const result = String(v.text ?? '').replace(/[a-z]/gi, (ch) => {
        const base = ch >= 'a' && ch <= 'z' ? 97 : 65;
        return String.fromCharCode(((ch.charCodeAt(0) - base + 13) % 26) + base);
    });
    return { label: 'Result', value: result };
JS,
        'tests' => [
            ['input' => ['text' => 'Hello'], 'expect' => ['Result' => 'Uryyb']],
            ['input' => ['text' => 'Uryyb'], 'expect' => ['Result' => 'Hello']],
            ['input' => ['text' => '123!'], 'expect' => ['Result' => '123!']],
        ],
    ],
    [
        'slug' => 'morse-code-translator',
        'name' => 'Morse Code Translator',
        'category' => 'text',
        'description' => 'Translate text to Morse code and Morse code back to text.',
        'icon' => 'type',
        'keywords' => ['morse', 'code', 'translate', 'encode', 'decode'],
        'inputs' => [
            ['name' => 'text', 'label' => 'Text or Morse', 'type' => 'textarea', 'default' => 'SOS', 'required' => true],
            ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'default' => 'encode', 'options' => [
                ['value' => 'encode', 'label' => 'Text → Morse'],
                ['value' => 'decode', 'label' => 'Morse → Text'],
            ]],
        ],
        'compute' => <<<'JS'
    const map = {
        A: '.-', B: '-...', C: '-.-.', D: '-..', E: '.', F: '..-.', G: '--.', H: '....',
        I: '..', J: '.---', K: '-.-', L: '.-..', M: '--', N: '-.', O: '---', P: '.--.',
        Q: '--.-', R: '.-.', S: '...', T: '-', U: '..-', V: '...-', W: '.--', X: '-..-',
        Y: '-.--', Z: '--..', 0: '-----', 1: '.----', 2: '..---', 3: '...--', 4: '....-',
        5: '.....', 6: '-....', 7: '--...', 8: '---..', 9: '----.',
    };
    const reverse = Object.fromEntries(Object.entries(map).map(([k, val]) => [val, k]));
    if (v.mode === 'decode') {
        const decoded = String(v.text ?? '').trim().split(/\s*\/\s*/).map((word) =>
            word.trim().split(/\s+/).filter(Boolean).map((code) => reverse[code] || '?').join('')
        ).join(' ');
        return { label: 'Result', value: decoded };
    }
    const encoded = String(v.text ?? '').toUpperCase().split(/\s+/).filter(Boolean).map((word) =>
        [...word].map((ch) => map[ch] || '').filter(Boolean).join(' ')
    ).join(' / ');
    return { label: 'Result', value: encoded };
JS,
        'tests' => [
            ['input' => ['text' => 'SOS', 'mode' => 'encode'], 'expect' => ['Result' => '... --- ...']],
            ['input' => ['text' => '... --- ...', 'mode' => 'decode'], 'expect' => ['Result' => 'SOS']],
            ['input' => ['text' => 'HELLO WORLD', 'mode' => 'encode'], 'expect' => ['Result' => '.... . .-.. .-.. --- / .-- --- .-. .-.. -..']],
        ],
    ],
];
