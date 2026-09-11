<?php

// This are shared by every user so we create this so we dont hardcode or let the user create them bythemselves
return [

    'default_categories' => [

        'income' => [
            'Salary',
            'Business',
            // 'Freelance',
            // 'Investment',
            // 'Gift',
            // 'Other Income',
        ],

        'expense' => [
            'Food',
            'Transport',
            'Housing',
            'Utilities',
            'Shopping',
            // 'Entertainment',
            // 'Healthcare',
            'Education',
            // 'Travel',
            // 'Other Expense',
        ],

    ],
    'default_accounts' => [
        [
            'name' => 'Cash',
            'type' => 'cash',
        ],
        [
            'name' => 'Bank',
            'type' => 'bank',
        ],
        [
            'name' => 'MoMo',
            'type' => 'mobile_money',
        ],

    ],

];
