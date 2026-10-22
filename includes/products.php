<?php
/**
 * Single source of truth for the storefront product catalogue.
 *
 * The cart and checkout look products up here so a visitor cannot change
 * names, prices, or image paths by editing the POST data.
 */

function rs8_products(): array
{
    static $products = null;

    if ($products === null) {
        $products = [
            'p1' => [
                'id'          => 'p1',
                'name'        => 'RS8 Racing Line 10W-50',
                'price'       => 350.00,
                'image'       => 'images/rs8oil.webp',
                'alt'         => 'RS8 Racing Line 10W-50',
                'description' => '100% Synthetic PAO Formulated Racing Oil',
                'features'    => [
                    '100% Synthetic',
                    '10W-50 API SN',
                    '4T Racing',
                    'JASO MA',
                    'Racing Line',
                    'PAO Formulated',
                ],
            ],
            'p2' => [
                'id'          => 'p2',
                'name'        => 'RS8 Torque Drive Half Sheeve',
                'price'       => 1680.00,
                'image'       => 'images/torquedrive.webp',
                'alt'         => 'RS8 Torque Drive',
                'description' => 'Race On! Highspeed Steel Torque Drive for Mio Soul GT 125',
                'features'    => [
                    'Touring and Race Proven',
                    'Performance Guaranteed',
                    'High Speed Profile Design',
                    'Precision Machining',
                    'Easy Tuning and Installation',
                ],
            ],
            'p3' => [
                'id'          => 'p3',
                'name'        => 'RS8 Performance Clutch Bell',
                'price'       => 1380.00,
                'image'       => 'images/rs8bell.webp',
                'alt'         => 'RS8 Performance Clutch Bell',
                'description' => 'Engineered for PCX, ADV, and Click',
                'features'    => [
                    'Anti Dragging Formula',
                    'Dynamically Balanced',
                    'Touring and Race Proven',
                    'Performance Guaranteed',
                ],
            ],
            'p4' => [
                'id'          => 'p4',
                'name'        => 'RS8 Clutch Master',
                'price'       => 1680.00,
                'image'       => 'images/rs8clutchassy.webp',
                'alt'         => 'RS8 Lightweight Clutch Master',
                'description' => 'Super Lightweight Clutch Master Assembly',
                'features'    => [
                    'Anti Dragging Formula',
                    'Touring and Race Proven',
                    'Performance Guaranteed',
                ],
            ],
            'p5' => [
                'id'          => 'p5',
                'name'        => 'RS8 Magic Degreaser',
                'price'       => 150.00,
                'image'       => 'images/rs8degreaser.webp',
                'alt'         => 'RS8 Magic Degreaser',
                'description' => 'Shine, Protect, Clean and Degrease. Powerful Formula Cleans Quickly and Easily.',
                'features'    => [
                    'Magic Formulation for Engine, Plastic Parts, Chain',
                ],
            ],
            'p6' => [
                'id'          => 'p6',
                'name'        => 'RS8 Snapback Mesh Cap',
                'price'       => 680.00,
                'image'       => 'images/rs8cap.jpg',
                'alt'         => 'RS8 Snapback Mesh Cap',
                'description' => 'V1 Gray-White (Limited Edition) Team Apparel',
                'features'    => [
                    'Snapback',
                    'Flat Brim',
                    'Embroidery',
                    'High Quality',
                    'Cotton/Acrylic',
                    'Comes with String Bag',
                ],
            ],
        ];
    }

    return $products;
}

function rs8_product(string $id): ?array
{
    $products = rs8_products();

    return $products[$id] ?? null;
}

/**
 * Normalize a session cart against the catalogue.
 *
 * Unknown products and invalid quantities are dropped, and names/prices are
 * always replaced with the values defined in rs8_products().
 */
function rs8_normalize_cart(array $cart): array
{
    $normalized = [];

    foreach ($cart as $id => $item) {
        if (!is_string($id) || !is_array($item)) {
            continue;
        }

        $product = rs8_product($id);
        if ($product === null) {
            continue;
        }

        $quantity = (int) ($item['quantity'] ?? 0);
        if ($quantity < 1) {
            continue;
        }

        $normalized[$id] = [
            'name'     => $product['name'],
            'price'    => (float) $product['price'],
            'image'    => $product['image'],
            'quantity' => min(99, $quantity),
        ];
    }

    return $normalized;
}
