<?php
/**
 * Wikipedia category sources for the KnowledgeWorld harvest.
 *
 * Each subcategory lists Wikipedia categories. The harvester walks each
 * category tree breadth-first and assigns articles to the subcategory that
 * referenced it. Categories are only a starting point: the final catalogue is
 * de-duplicated, filtered and capped by scripts/harvest-wikipedia.php.
 */

return [
    'science' => [
        'physics' => [
            'Physics', 'Classical mechanics', 'Quantum mechanics', 'Thermodynamics',
            'Electromagnetism', 'Optics', 'Theory of relativity', 'Particle physics',
            'Condensed matter physics', 'Acoustics',
        ],
        'chemistry' => [
            'Chemistry', 'Chemical elements', 'Chemical compounds', 'Organic chemistry',
            'Inorganic chemistry', 'Physical chemistry', 'Analytical chemistry',
            'Biochemistry', 'Chemical reactions', 'Materials science',
        ],
        'biology' => [
            'Biology', 'Cell biology', 'Genetics', 'Evolutionary biology', 'Ecology',
            'Botany', 'Zoology', 'Microbiology', 'Anatomy', 'Physiology',
        ],
        'astronomy' => [
            'Astronomy', 'Planets', 'Stars', 'Galaxies', 'Astronomical objects',
            'Solar System', 'Cosmology', 'Space science', 'Observational astronomy',
        ],
        'earth-science' => [
            'Earth sciences', 'Geology', 'Meteorology', 'Oceanography', 'Climatology',
            'Plate tectonics', 'Volcanology', 'Seismology', 'Mineralogy', 'Hydrology',
        ],
    ],

    'technology' => [
        'computing' => [
            'Computing', 'Computer science', 'Programming languages', 'Software',
            'Computer hardware', 'Algorithms', 'Databases', 'Operating systems',
            'Data structures', 'Computer programming',
        ],
        'artificial-intelligence' => [
            'Artificial intelligence', 'Machine learning', 'Neural networks',
            'Natural language processing', 'Computer vision', 'Robotics',
            'Deep learning', 'Cognitive science', 'Automation',
        ],
        'internet' => [
            'Internet', 'World Wide Web', 'Computer networks', 'Web technology',
            'Internet protocols', 'Web browsers', 'Social media', 'E-commerce',
            'Digital media', 'Internet governance',
        ],
        'electronics' => [
            'Electronics', 'Electronic circuits', 'Semiconductors', 'Electronic components',
            'Integrated circuits', 'Signal processing', 'Telecommunications',
            'Consumer electronics', 'Microelectronics',
        ],
        'space-technology' => [
            'Spaceflight', 'Spacecraft', 'Rocketry', 'Space exploration',
            'Satellites', 'Human spaceflight', 'Space agencies', 'Launch vehicles',
        ],
        'energy-technology' => [
            'Energy technology', 'Power stations', 'Renewable energy', 'Electric power',
            'Energy storage', 'Solar power', 'Wind power', 'Nuclear technology',
            'Electrical grids', 'Batteries',
        ],
    ],

    'history' => [
        'ancient' => [
            'Ancient history', 'Ancient Greece', 'Ancient Rome', 'Mesopotamia',
            'Ancient Egypt', 'Indus Valley Civilisation', 'Ancient China',
            'Classical antiquity', 'Archaeology', 'Ancient Near East',
        ],
        'medieval' => [
            'Middle Ages', 'Byzantine Empire', 'Feudalism', 'Crusades',
            'Medieval Europe', 'Islamic Golden Age', 'Vikings', 'Mongol Empire',
            'Medieval Asia', 'Medieval Africa',
        ],
        'modern' => [
            'Modern history', 'Renaissance', 'Age of Enlightenment', 'Industrial Revolution',
            '19th century', '20th century', 'Revolutions', 'Colonialism',
            'History of science', 'History of technology',
        ],
        'world-wars' => [
            'World War I', 'World War II', 'Military history', 'Cold War',
            'Battle', 'Military alliances', 'Weapons', 'Warfare',
            'Holocaust', 'Nuclear warfare',
        ],
        'civilizations' => [
            'Civilizations', 'Cultural history', 'Archaeological cultures',
            'Ancient civilizations', 'Empires', 'Historical eras',
            'History of Africa', 'History of the Americas', 'History of Asia',
        ],
    ],

    'geography' => [
        'countries' => [
            'Countries', 'Countries in Africa', 'Countries in Asia',
            'Countries in Europe', 'Countries in North America',
            'Countries in South America', 'Countries in Oceania',
            'Geography by country', 'Territories', 'Capitals',
        ],
        'physical-geography' => [
            'Physical geography', 'Landforms', 'Bodies of water', 'Biomes',
            'Climate', 'Mountains', 'Rivers', 'Deserts', 'Islands',
            'Ecosystems',
        ],
        'cities' => [
            'Cities', 'Capitals', 'Metropolitan areas', 'Urban geography',
            'Towns', 'Ports and harbours', 'City planning', 'World cities',
            'Populated places', 'Urbanization',
        ],
        'oceans-and-seas' => [
            'Oceans', 'Seas', 'Oceanography', 'Marine geography',
            'Straits', 'Gulfs', 'Ocean currents', 'Coasts',
            'Marine biology', 'Water',
        ],
    ],

    'mathematics' => [
        'foundations' => [
            'Mathematical logic', 'Set theory', 'Number theory', 'Mathematical analysis',
            'Foundations of mathematics', 'Arithmetic', 'Algebra', 'Mathematical objects',
            'Mathematical concepts', 'Mathematical theorems',
        ],
        'geometry' => [
            'Geometry', 'Euclidean geometry', 'Trigonometry', 'Topology',
            'Differential geometry', 'Algebraic geometry', 'Polygons', 'Polyhedra',
            'Curves', 'Geometric shapes',
        ],
        'algebra' => [
            'Algebra', 'Linear algebra', 'Abstract algebra', 'Group theory',
            'Polynomials', 'Ring theory', 'Field theory', 'Matrices',
            'Equations', 'Functions and mappings',
        ],
        'probability-statistics' => [
            'Probability theory', 'Statistics', 'Statistical theory',
            'Probability distributions', 'Statistical inference', 'Random variables',
            'Data analysis', 'Regression analysis', 'Sampling (statistics)',
            'Stochastic processes',
        ],
    ],

    'economics' => [
        'microeconomics' => [
            'Microeconomics', 'Consumer theory', 'Game theory',
            'Industrial organization', 'Market (economics)', 'Supply and demand',
            'Pricing', 'Labour economics', 'Welfare economics', 'Public economics',
        ],
        'macroeconomics' => [
            'Macroeconomics', 'Economic growth', 'Monetary economics',
            'Fiscal policy', 'International economics', 'Business cycles',
            'Unemployment', 'Inflation', 'Economic indicators', 'Central banks',
        ],
        'finance' => [
            'Finance', 'Personal finance', 'Investment', 'Financial markets',
            'Banking', 'Insurance', 'Stock market', 'Bonds (finance)',
            'Accounting', 'Financial economics',
        ],
        'trade' => [
            'International trade', 'Trade', 'Commercial policy', 'Globalization',
            'Tariffs', 'Trade agreements', 'Export', 'Import',
            'Economic integration', 'Supply chain',
        ],
    ],

    'languages' => [
        'linguistics' => [
            'Linguistics', 'Grammar', 'Phonetics', 'Semantics',
            'Sociolinguistics', 'Syntax', 'Morphology (linguistics)',
            'Historical linguistics', 'Language acquisition', 'Writing systems',
        ],
        'english' => [
            'English language', 'English grammar', 'English phonology',
            'History of English', 'English dialects', 'English words',
            'English-language literature', 'English orthography',
            'English usage', 'English as a second language',
        ],
        'world-languages' => [
            'Languages', 'Language families', 'Official languages',
            'Languages of Africa', 'Languages of Asia', 'Languages of Europe',
            'Romance languages', 'Germanic languages', 'Slavic languages',
            'Constructed languages',
        ],
    ],

    'engineering' => [
        'civil' => [
            'Civil engineering', 'Structural engineering', 'Construction',
            'Transport infrastructure', 'Bridges', 'Dams', 'Roads',
            'Building engineering', 'Geotechnical engineering', 'Architecture',
        ],
        'mechanical' => [
            'Mechanical engineering', 'Machines', 'Engines', 'Mechanics',
            'Manufacturing', 'Machine tools', 'HVAC', 'Automotive engineering',
            'Aerospace engineering', 'Tribology',
        ],
        'electrical' => [
            'Electrical engineering', 'Power engineering', 'Control engineering',
            'Electronics', 'Electric power distribution', 'Electric motors',
            'Generators', 'Transformers', 'Electrical components',
            'Signals and systems',
        ],
        'materials' => [
            'Materials science', 'Metallurgy', 'Polymers', 'Ceramic materials',
            'Composite materials', 'Alloys', 'Nanomaterials', 'Crystallography',
            'Corrosion', 'Materials',
        ],
        'chemical' => [
            'Chemical engineering', 'Unit operations', 'Process engineering',
            'Industrial chemistry', 'Petroleum engineering', 'Biochemical engineering',
            'Distillation', 'Chemical reactors', 'Separation processes',
            'Process safety',
        ],
    ],

    'health' => [
        'human-body' => [
            'Human body', 'Human anatomy', 'Organ systems', 'Physiology',
            'Human organs', 'Circulatory system', 'Nervous system',
            'Digestive system', 'Musculoskeletal system', 'Endocrine system',
        ],
        'nutrition' => [
            'Nutrition', 'Diet (nutrition)', 'Nutrients', 'Food science',
            'Vitamins', 'Dietary minerals', 'Healthy diet', 'Food groups',
            'Malnutrition', 'Dietary supplements',
        ],
        'diseases' => [
            'Diseases', 'Medical conditions', 'Pathology', 'Infectious diseases',
            'Cardiovascular diseases', 'Cancer', 'Genetic disorders',
            'Mental disorders', 'Epidemiology', 'Public health',
        ],
        'mental-health' => [
            'Mental health', 'Psychiatry', 'Psychology', 'Mental disorders',
            'Psychotherapy', 'Stress (biology)', 'Anxiety', 'Depression (mood)',
            'Well-being', 'Sleep',
        ],
    ],

    'life-skills' => [
        'communication' => [
            'Communication', 'Interpersonal communication', 'Public speaking',
            'Writing', 'Conversation', 'Negotiation', 'Listening',
            'Nonverbal communication', 'Assertiveness', 'Conflict resolution',
        ],
        'personal-finance' => [
            'Personal finance', 'Budgeting', 'Saving', 'Credit',
            'Debt', 'Retirement', 'Investment', 'Insurance',
            'Financial literacy', 'Tax',
        ],
        'productivity' => [
            'Productivity', 'Time management', 'Project management',
            'Habit', 'Procrastination', 'Goal setting', 'Decision-making',
            'Note-taking', 'Learning', 'Task management',
        ],
    ],
];
