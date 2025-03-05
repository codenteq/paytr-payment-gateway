<?php

return [
    [
        'key'    => 'sales.payment_methods.paytr',
        'info'   => 'paytr::app.paytr.info',
        'name'   => 'paytr::app.paytr.name',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'paytr::app.paytr.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'paytr::app.paytr.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],[
                'name'          => 'image',
                'title'         => 'paytr::app.paytr.system.image',
                'info'          => 'admin::app.configuration.index.sales.payment-methods.logo-information',
                'type'          => 'file',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'active',
                'title'         => 'paytr::app.paytr.system.status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ]
        ]
    ]
];
