<?php

    return [
        'basic' => [
            'name' => 'Basic',
            'price_id' => 'price_basic', // Reemplaza con el ID real del plan en Stripe
            'price' => 19,
            'features' => [
                'Hasta 100 carritos/mes',
                'Notificaciones por email',
                'Soporte por correo',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'price_id' => 'price_pro', // Reemplaza con el ID real del plan en Stripe
            'price' => 49,
            'features' => [
                'Hasta 500 carritos/mes',
                'Notificaciones por email y WhatsApp',
                'Soporte prioritario',
            ],
        ],
    ];
