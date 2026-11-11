<?php

return [
    'about' => [
        'title' => 'About UniVerse',
        'description' => 'Why UniVerse exists, how it is built and what it stands for.',
        'updated' => '11 November 2026',
        'intro' => 'UniVerse brings practical tools and reliable knowledge together in one calm, fast and accessible place.',
        'sections' => [
            [
                'heading' => 'Our mission',
                'body' => [
                    'Useful tools and trustworthy knowledge should be easy to find, free to use and available to everyone. UniVerse combines UtilityWorld — a growing library of interactive calculators, converters and generators — with KnowledgeWorld, a searchable library of educational articles.',
                    'The platform is designed to grow from the first hundred tools and articles into a much larger library without changing how people find their way around.',
                ],
            ],
            [
                'heading' => 'What makes UniVerse different',
                'body' => [
                    'Most tools run entirely in your browser. That means calculations are instant, and the numbers you type never need to leave your device.',
                    'Knowledge articles are sourced, dated and credited. When an article is based on encyclopedic material, the original source and licence are shown on the page so you can check it yourself.',
                    'There is no requirement to create an account to use an ordinary tool or read an article. Accounts will exist for people who want bookmarks, reading history and learning progress — not as a gate in front of the basics.',
                ],
            ],
            [
                'heading' => 'How the platform is built',
                'body' => [
                    'UniVerse is a modular monolith built with Laravel and PHP. Tools are declared once in a central registry, which drives routing, search, categories and the interface. Knowledge articles follow a separate publishing pipeline with metadata, source attribution and review dates.',
                    'Search spans both worlds, so a single query can return a calculator, an explanation and a related learning resource.',
                ],
            ],
            [
                'heading' => 'Principles',
                'body' => [
                    'Simplicity: find what you need quickly, without noise.',
                    'Accessibility: keyboard navigation, readable typography and responsive layouts.',
                    'Reliability: tested calculations and sourced information.',
                    'Privacy: collect as little personal data as possible.',
                    'Quality over quantity: a smaller, well-reviewed library beats a larger, unreliable one.',
                ],
            ],
        ],
    ],

    'privacy' => [
        'title' => 'Privacy',
        'description' => 'What UniVerse does and does not collect.',
        'updated' => '11 November 2026',
        'intro' => 'UniVerse is built to collect as little personal information as possible. You can use every public tool and read every article without an account.',
        'sections' => [
            [
                'heading' => 'Using tools without an account',
                'body' => [
                    'Tool calculations run in your browser. The values you enter are not sent to our servers and are not stored.',
                    'When a tool is used, we record an anonymous usage event so we can understand which tools are popular. This event contains the tool identifier and a one-way hash of your session — never your inputs, your name or your IP address.',
                ],
            ],
            [
                'heading' => 'Search',
                'body' => [
                    'Search terms are stored in aggregate so we can improve results. Search logs contain the term and the number of results, not who searched for it.',
                ],
            ],
            [
                'heading' => 'Cookies',
                'body' => [
                    'UniVerse uses a session cookie for basic site functionality and security. There are no advertising cookies and no third-party tracking scripts.',
                ],
            ],
            [
                'heading' => 'Accounts',
                'body' => [
                    'Accounts are optional. If you create one, we store your name, email address and a securely hashed password. We do not ask for your age, address or phone number, and we do not sell or share personal data.',
                ],
            ],
            [
                'heading' => 'Your rights',
                'body' => [
                    'You can request access to, correction of, or deletion of your account data at any time. Contact the project maintainers through the repository linked in the footer.',
                ],
            ],
        ],
    ],

    'terms' => [
        'title' => 'Terms of use',
        'description' => 'The terms that apply when you use UniVerse.',
        'updated' => '11 November 2026',
        'intro' => 'UniVerse is provided as a free educational and practical resource. Please use it responsibly.',
        'sections' => [
            [
                'heading' => 'Use of the service',
                'body' => [
                    'You may use UniVerse for personal, educational and commercial purposes. You may not attempt to disrupt the service, bypass security controls, scrape at a rate that degrades service for others, or use the platform to break the law.',
                ],
            ],
            [
                'heading' => 'No professional advice',
                'body' => [
                    'Tools and articles are provided for general information. Financial, medical, legal and engineering results are estimates, not professional advice. Always verify important calculations and consult a qualified professional where appropriate.',
                ],
            ],
            [
                'heading' => 'Accuracy',
                'body' => [
                    'Calculations are covered by automated tests, and articles are reviewed before publication. Even so, UniVerse cannot guarantee that every result or statement is free of error. If you find a problem, please report it.',
                ],
            ],
            [
                'heading' => 'Third-party content',
                'body' => [
                    'Some KnowledgeWorld articles are adapted from Wikipedia and are used under the Creative Commons Attribution-ShareAlike 4.0 licence. Each article displays its source, licence and retrieval date. Such content remains subject to that licence.',
                ],
            ],
            [
                'heading' => 'Changes',
                'body' => [
                    'These terms may be updated as the platform grows. Continued use after an update means you accept the revised terms.',
                ],
            ],
        ],
    ],

    'accessibility' => [
        'title' => 'Accessibility',
        'description' => 'How UniVerse works to be usable by everyone.',
        'updated' => '11 November 2026',
        'intro' => 'UniVerse aims to meet WCAG 2.2 AA as a practical baseline, and accessibility is treated as part of the design system rather than an afterthought.',
        'sections' => [
            [
                'heading' => 'What is in place',
                'body' => [
                    'Every page can be navigated by keyboard, with a visible skip link and clear focus styles.',
                    'Text uses a readable scale and strong contrast against the page background. Layouts reflow down to small mobile screens and remain usable at 200% zoom.',
                    'Tool forms use real labels, grouped fields and live result regions so screen readers announce updates.',
                    'Animation is subtle and respects the reduced-motion preference.',
                ],
            ],
            [
                'heading' => 'Known limitations',
                'body' => [
                    'Long encyclopedic articles do not yet provide a generated table of contents. Some mathematical notation is presented as plain text rather than rendered equations.',
                    'If a tool result is not understandable with a screen reader, that is a bug worth reporting.',
                ],
            ],
            [
                'heading' => 'Reporting a problem',
                'body' => [
                    'Accessibility issues are treated as high-priority bugs. Please include the page, the assistive technology you use and what you expected to happen.',
                ],
            ],
        ],
    ],
];
