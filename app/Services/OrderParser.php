<?php

namespace App\Services;

class OrderParser
{
    /**
     * Parse product selections from message
     */
    public function parse(string $message): array
    {
        $selections = [];
        $message = trim($message);
        if ($message === '' || $message === '0') {
            return $selections;
        }

        // Find all patterns like "1, 2 = 1" or "12=2, 4"
        preg_match_all('/(\d+(?:\s*,\s*\d+)*)\s*=\s*([^=\s]+(?:\s*,\s*[^=\s]+)*)/', $message, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $left = $match[1];
            $right = $match[2];

            // Split left by comma -> product ids
            $product_strs = array_map(trim(...), explode(',', $left));
            $products = array_map(intval(...), $product_strs);

            // Split right by comma
            $right_parts = array_map(trim(...), explode(',', $right));
            $qtys = [];
            $extra_products = [];
            foreach ($right_parts as $right_part) {
                if (is_numeric($right_part)) {
                    $qtys[] = (int) $right_part;
                } else {
                    // Assume it's a product id
                    $extra_products[] = (int) $right_part;
                }
            }

            $qty = $qtys[0] ?? 1;

            // Add products from left with the qty
            foreach ($products as $product) {
                if ($product > 0) {
                    $selections[] = ['index' => $product, 'quantity' => $qty];
                }
            }

            // Add extra products with qty 1
            foreach ($extra_products as $extra_product) {
                if ($extra_product > 0) {
                    $selections[] = ['index' => $extra_product, 'quantity' => 1];
                }
            }
        }

        // Also handle single numbers without =
        $remaining = preg_replace('/(\d+(?:\s*,\s*\d+)*)\s*=\s*([^=\s]+(?:\s*,\s*[^=\s]+)*)/', '', $message);
        $tokens = preg_split('/\s+/', (string) $remaining);
        foreach ($tokens as $token) {
            $token = trim($token);
            if (preg_match('/^(\d+)$/', $token, $m)) {
                $productIndex = (int) $m[1];
                if ($productIndex > 0) {
                    $selections[] = ['index' => $productIndex, 'quantity' => 1];
                }
            }
        }

        return $selections;
    }
}
