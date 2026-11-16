<?php
/**
 * KnowledgeWorld curated topic plan grouped by category. The harvester uses
 * these titles first, then expands through content/articles/sources.php.
 * Content is fetched from Wikipedia (CC BY-SA 4.0) by scripts/harvest-wikipedia.php.
 * Every article page links back to its source and credits the licence.
 */

return [
    'science' => [
        'name' => 'Science',
        'description' => 'Physics, chemistry, biology, astronomy and Earth science.',
        'icon' => 'atom',
        'subcategories' => [
            'physics' => ['label' => 'Physics', 'topics' => [
                'Physics', 'Classical mechanics', "Newton's laws of motion", 'Kinematics',
                'Dynamics (mechanics)', 'Work (physics)', 'Energy', 'Conservation of energy',
                'Momentum', 'Gravity', 'Electromagnetism', 'Electric charge', 'Electric current',
                'Voltage', 'Electrical resistance and conductance', 'Magnetism',
                'Electromagnetic radiation', 'Light', 'Optics', 'Sound', 'Wave',
                'Thermodynamics', 'Heat', 'Temperature', 'Entropy', 'Quantum mechanics',
                'Theory of relativity', 'Special relativity', 'General relativity', 'Nuclear physics',
            ]],
            'chemistry' => ['label' => 'Chemistry', 'topics' => [
                'Chemistry', 'Atom', 'Molecule', 'Chemical element', 'Periodic table',
                'Chemical bond', 'Covalent bond', 'Ionic bonding', 'Chemical reaction',
                'Acid', 'Base (chemistry)', 'pH', 'Oxidation state', 'Redox',
                'Organic chemistry', 'Inorganic chemistry', 'Biochemistry',
                'Chemical equilibrium', 'Catalysis', 'Solution (chemistry)',
            ]],
            'biology' => ['label' => 'Biology', 'topics' => [
                'Biology', 'Cell (biology)', 'DNA', 'RNA', 'Gene', 'Protein', 'Evolution',
                'Natural selection', 'Species', 'Photosynthesis', 'Cellular respiration',
                'Mitosis', 'Meiosis', 'Bacteria', 'Virus', 'Fungus', 'Plant', 'Animal',
                'Human body', 'Immune system',
            ]],
            'astronomy' => ['label' => 'Astronomy', 'topics' => [
                'Astronomy', 'Solar System', 'Sun', 'Earth', 'Moon', 'Planet', 'Star',
                'Galaxy', 'Milky Way', 'Black hole',
            ]],
            'earth-science' => ['label' => 'Earth science', 'topics' => [
                'Earth science', 'Plate tectonics', 'Rock (geology)', 'Mineral', 'Volcano',
                'Earthquake', 'Water cycle', 'Climate', 'Weather', 'Atmosphere of Earth',
            ]],
        ],
    ],

    'technology' => [
        'name' => 'Technology',
        'description' => 'Computing, artificial intelligence, the internet and modern engineering.',
        'icon' => 'cpu',
        'subcategories' => [
            'computing' => ['label' => 'Computing', 'topics' => [
                'Computer', 'Computer science', 'Central processing unit', 'Random-access memory',
                'Operating system', 'Software', 'Programming language', 'Algorithm',
                'Data structure', 'Database', 'Computer network', 'Internet protocol suite',
                'World Wide Web', 'HTML', 'CSS', 'JavaScript', 'Python (programming language)',
                'Java (programming language)', 'C (programming language)', 'Compiler',
                'Binary number', 'Encryption', 'Cryptography', 'Cloud computing', 'Virtual machine',
            ]],
            'artificial-intelligence' => ['label' => 'Artificial intelligence', 'topics' => [
                'Artificial intelligence', 'Machine learning', 'Deep learning', 'Neural network',
                'Large language model', 'Natural language processing', 'Computer vision',
                'Reinforcement learning', 'Turing test', 'Expert system',
            ]],
            'internet' => ['label' => 'Internet', 'topics' => [
                'Internet', 'Hypertext Transfer Protocol', 'Domain Name System', 'IP address',
                'Wi-Fi', 'Bluetooth', 'Search engine', 'Social media', 'Email', 'Streaming media',
            ]],
            'electronics' => ['label' => 'Electronics', 'topics' => [
                'Electronics', 'Transistor', 'Diode', 'Integrated circuit', 'Microprocessor',
                'Semiconductor', 'Printed circuit board', 'Resistor', 'Capacitor', 'Inductor',
            ]],
            'space-technology' => ['label' => 'Space technology', 'topics' => [
                'Rocket', 'Satellite', 'Space exploration', 'International Space Station',
                'Hubble Space Telescope', 'James Webb Space Telescope', 'Apollo program', 'SpaceX',
            ]],
            'energy-technology' => ['label' => 'Energy technology', 'topics' => [
                'Solar energy', 'Wind power', 'Nuclear power', 'Hydroelectricity',
                'Electric battery', 'Renewable energy', 'Fossil fuel',
            ]],
        ],
    ],

    'history' => [
        'name' => 'History',
        'description' => 'Civilisations, empires, revolutions and the modern world.',
        'icon' => 'landmark',
        'subcategories' => [
            'ancient' => ['label' => 'Ancient world', 'topics' => [
                'Ancient history', 'Mesopotamia', 'Sumer', 'Ancient Egypt',
                'Indus Valley Civilisation', 'Ancient Greece', 'Ancient Rome', 'Roman Empire',
                'Byzantine Empire', 'Neo-Assyrian Empire', 'Achaemenid Empire', 'Alexander the Great',
                'Julius Caesar', 'Ancient China', 'Qin dynasty', 'Han dynasty', 'Maurya Empire',
                'Gupta Empire', 'Maya civilization', 'Aztec Empire',
            ]],
            'medieval' => ['label' => 'Medieval world', 'topics' => [
                'Middle Ages', 'Feudalism', 'Crusades', 'Holy Roman Empire', 'Mongol Empire',
                'Genghis Khan', 'Viking Age', 'Norman Conquest', 'Magna Carta', 'Black Death',
                'Islamic Golden Age', 'Ottoman Empire', 'Song dynasty', 'Tang dynasty', 'Silk Road',
            ]],
            'modern' => ['label' => 'Modern history', 'topics' => [
                'Renaissance', 'Age of Discovery', 'Industrial Revolution', 'French Revolution',
                'American Revolution', 'Napoleon', 'Colonialism', 'Imperialism', 'World War I',
                'World War II', 'Cold War', 'Russian Revolution', 'Berlin Wall',
                'Revolutions of 1989', 'Decolonisation', 'American Civil War', 'Abraham Lincoln',
                'Mahatma Gandhi', 'Nelson Mandela', 'United Nations',
            ]],
            'world-wars' => ['label' => 'World Wars', 'topics' => [
                'Causes of World War I', 'Treaty of Versailles', 'Nazi Germany', 'Adolf Hitler',
                'The Holocaust', 'Attack on Pearl Harbor', 'Normandy landings',
                'Atomic bombings of Hiroshima and Nagasaki', 'Winston Churchill',
                'Franklin D. Roosevelt',
            ]],
            'civilizations' => ['label' => 'Civilisations', 'topics' => [
                'Civilization', 'Cradle of civilization', 'Olmec', 'Inca Empire', 'Khmer Empire',
                'Mali Empire', 'Kingdom of Kush', 'Phoenicia', 'Carthage', 'Babylon',
            ]],
        ],
    ],

    'geography' => [
        'name' => 'Geography',
        'description' => 'Countries, landscapes, cities, oceans and the physical world.',
        'icon' => 'globe',
        'subcategories' => [
            'countries' => ['label' => 'Countries', 'topics' => [
                'Country', 'France', 'Germany', 'Italy', 'Spain', 'United Kingdom', 'United States',
                'Canada', 'Brazil', 'Argentina', 'Mexico', 'China', 'India', 'Japan', 'Russia',
                'Australia', 'Egypt', 'South Africa', 'Nigeria', 'Indonesia',
            ]],
            'physical-geography' => ['label' => 'Physical geography', 'topics' => [
                'Geography', 'Continent', 'Mountain', 'Himalayas', 'Andes', 'Alps', 'River',
                'Nile', 'Amazon River', 'Desert', 'Sahara', 'Forest', 'Rainforest', 'Grassland',
                'Wetland', 'Glacier', 'Coast', 'Island', 'Peninsula', 'Plain',
            ]],
            'cities' => ['label' => 'Cities', 'topics' => [
                'City', 'Tokyo', 'Delhi', 'Shanghai', 'São Paulo', 'Mexico City', 'Cairo',
                'Mumbai', 'Beijing', 'Dhaka', 'London', 'Paris', 'New York City',
                'Los Angeles', 'Istanbul',
            ]],
            'oceans-and-seas' => ['label' => 'Oceans and seas', 'topics' => [
                'Ocean', 'Pacific Ocean', 'Atlantic Ocean', 'Indian Ocean', 'Southern Ocean',
                'Arctic Ocean', 'Mediterranean Sea', 'Red Sea', 'Caribbean Sea', 'South China Sea',
            ]],
        ],
    ],

    'mathematics' => [
        'name' => 'Mathematics',
        'description' => 'Numbers, algebra, geometry, probability and statistics.',
        'icon' => 'sigma',
        'subcategories' => [
            'foundations' => ['label' => 'Foundations', 'topics' => [
                'Mathematics', 'Number', 'Arithmetic', 'Addition', 'Subtraction',
                'Multiplication', 'Division (mathematics)', 'Fraction', 'Decimal',
                'Negative number', 'Integer', 'Rational number', 'Real number',
                'Complex number', 'Infinity',
            ]],
            'geometry' => ['label' => 'Geometry', 'topics' => [
                'Geometry', 'Point (geometry)', 'Line (geometry)', 'Angle', 'Triangle',
                'Circle', 'Square', 'Rectangle', 'Polygon', 'Pythagorean theorem', 'Area', 'Volume',
            ]],
            'algebra' => ['label' => 'Algebra', 'topics' => [
                'Algebra', 'Equation', 'Linear equation', 'Quadratic equation',
                'Function (mathematics)', 'Polynomial', 'Matrix (mathematics)', 'Vector space',
                'Logarithm', 'Exponentiation',
            ]],
            'probability-statistics' => ['label' => 'Probability and statistics', 'topics' => [
                'Probability', 'Statistics', 'Mean', 'Median', 'Standard deviation', 'Variance',
                'Normal distribution', 'Random variable', 'Combinatorics', 'Permutation',
                'Factorial', 'Correlation', 'Regression analysis',
            ]],
        ],
    ],

    'economics' => [
        'name' => 'Economics',
        'description' => 'Markets, macroeconomics, finance and international trade.',
        'icon' => 'chart-line',
        'subcategories' => [
            'microeconomics' => ['label' => 'Microeconomics', 'topics' => [
                'Economics', 'Microeconomics', 'Supply and demand', 'Price', 'Market (economics)',
                'Competition (economics)', 'Monopoly', 'Elasticity (economics)', 'Utility',
                'Consumer', 'Opportunity cost', 'Externality',
            ]],
            'macroeconomics' => ['label' => 'Macroeconomics', 'topics' => [
                'Macroeconomics', 'Gross domestic product', 'Inflation', 'Unemployment',
                'Central bank', 'Interest rate', 'Monetary policy', 'Fiscal policy',
                'Economic growth', 'Recession', 'Money', 'Tax', 'Government budget',
                'Business cycle',
            ]],
            'finance' => ['label' => 'Finance', 'topics' => [
                'Finance', 'Stock', 'Bond (finance)', 'Investment', 'Compound interest',
                'Insurance', 'Bank', 'Financial market',
            ]],
            'trade' => ['label' => 'Trade', 'topics' => [
                'International trade', 'Tariff', 'Free trade', 'Exchange rate', 'Globalization',
                'Comparative advantage',
            ]],
        ],
    ],

    'languages' => [
        'name' => 'Languages',
        'description' => 'Linguistics, English grammar and major world languages.',
        'icon' => 'languages',
        'subcategories' => [
            'linguistics' => ['label' => 'Linguistics', 'topics' => [
                'Linguistics', 'Language', 'Grammar', 'Syntax', 'Semantics', 'Phonetics',
                'Phonology', 'Morphology (linguistics)', 'Etymology', 'Writing system',
            ]],
            'english' => ['label' => 'English', 'topics' => [
                'English language', 'English grammar', 'Part of speech', 'Noun', 'Verb',
                'Adjective', 'Pronoun', 'Punctuation',
            ]],
            'world-languages' => ['label' => 'World languages', 'topics' => [
                'Spanish language', 'French language', 'German language', 'Arabic',
                'Mandarin Chinese', 'Hindi', 'Swahili',
            ]],
        ],
    ],

    'engineering' => [
        'name' => 'Engineering',
        'description' => 'Civil, mechanical, electrical, materials and chemical engineering.',
        'icon' => 'wrench',
        'subcategories' => [
            'civil' => ['label' => 'Civil engineering', 'topics' => [
                'Engineering', 'Civil engineering', 'Bridge', 'Arch', 'Dam', 'Road',
                'Skyscraper', 'Tunnel', 'Foundation (engineering)', 'Concrete',
            ]],
            'mechanical' => ['label' => 'Mechanical engineering', 'topics' => [
                'Mechanical engineering', 'Engine', 'Internal combustion engine', 'Turbine',
                'Pump', 'Gear', 'Bearing (mechanical)', 'Steam engine', 'Robot', 'Air conditioning',
            ]],
            'electrical' => ['label' => 'Electrical engineering', 'topics' => [
                'Electrical engineering', 'Electric motor', 'Electric generator', 'Transformer',
                'Power station', 'Electrical grid', 'Alternating current', 'Direct current',
                'Electrical network', 'Electric power transmission',
            ]],
            'materials' => ['label' => 'Materials science', 'topics' => [
                'Materials science', 'Steel', 'Aluminium', 'Alloy', 'Composite material',
                'Polymer', 'Glass', 'Ceramic',
            ]],
            'chemical' => ['label' => 'Chemical engineering', 'topics' => [
                'Chemical engineering', 'Distillation', 'Refinery', 'Petrochemical', 'Fertilizer',
                'Water treatment', 'Desalination',
            ]],
        ],
    ],

    'health' => [
        'name' => 'Health education',
        'description' => 'The human body, nutrition, disease and mental wellbeing.',
        'icon' => 'heart-pulse',
        'subcategories' => [
            'human-body' => ['label' => 'The human body', 'topics' => [
                'Human brain', 'Circulatory system', 'Nervous system', 'Respiratory system',
                'Digestive system', 'Skeletal muscle', 'Blood', 'Heart',
            ]],
            'nutrition' => ['label' => 'Nutrition', 'topics' => [
                'Nutrition', 'Vitamin', 'Dietary fiber', 'Carbohydrate', 'Fat',
                'Mineral (nutrient)', 'Malnutrition',
            ]],
            'diseases' => ['label' => 'Diseases', 'topics' => [
                'Disease', 'Infection', 'Cancer', 'Diabetes', 'Cardiovascular disease',
            ]],
            'mental-health' => ['label' => 'Mental health', 'topics' => [
                'Mental health', 'Depression (mood)', 'Anxiety', 'Stress (biology)', 'Sleep',
            ]],
        ],
    ],

    'life-skills' => [
        'name' => 'Life skills',
        'description' => 'Communication, personal finance and productive working habits.',
        'icon' => 'sparkles',
        'subcategories' => [
            'communication' => ['label' => 'Communication', 'topics' => [
                'Communication', 'Public speaking', 'Active listening', 'Negotiation', 'Assertiveness',
            ]],
            'personal-finance' => ['label' => 'Personal finance', 'topics' => [
                'Personal finance', 'Budget', 'Saving', 'Debt', 'Credit card',
            ]],
            'productivity' => ['label' => 'Productivity', 'topics' => [
                'Time management', 'Procrastination', 'Goal setting', 'Habit', 'Note-taking',
            ]],
        ],
    ],
];
