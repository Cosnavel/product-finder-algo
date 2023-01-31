<?php

function combinations(array $arr, int $size): array
{
    $results = [];
    $combination = [];
    generateCombinations($arr, $size, 0, $combination, $results);

    return $results;
}

function generateCombinations(array $array, int $size, int $start, array &$combination, array &$results): void
{
    if (count($combination) === $size) {
        $results[] = $combination;

        return;
    }

    for ($index = $start; $index < count($array); $index++) {
        $combination[] = $array[$index];
        generateCombinations($array, $size, $index + 1, $combination, $results);
        array_pop($combination);
    }
}

function get_npr(array $x, int $r): array
{
    $npr_combinations = combinations($x, $r);

    return $npr_combinations;
}

function get_all_binary_combinations_of_given_length(int $length_of_binary_combination): array
{
    $all_binary_combinations = [];
    for ($i = 0; $i < 2 ** $length_of_binary_combination; $i++) {
        $binary = decbin($i);
        $binary = str_pad($binary, $length_of_binary_combination, '0', STR_PAD_LEFT);
        $all_binary_combinations[] = str_split($binary);
    }

    return $all_binary_combinations;
}

function find_items_that_can_be_equally_distributed(array $data, int $max_price, int $max_variation): array
{
    $max_price_after_equally_distributed = $max_price / $max_variation;
    // Distribute 10% margin to each different items spread in overall distribution. we can easily make it a higher margin
    $max_price_after_margin = $max_price_after_equally_distributed + $max_price_after_equally_distributed * 0.1;
    $items_that_can_be_equally_distributed = [];
    foreach ($data as $item) {
        if ($item['price'] <= $max_price_after_margin) {
            $items_that_can_be_equally_distributed[] = $item;
        }
    }

    return [$max_price_after_margin, $items_that_can_be_equally_distributed];
}

function find_margin_for_each_item_after_multiplied_by_quantity(array $items_that_can_be_equally_distributed, float $max_price_after_margin): array
{
    $all_possible_items_with_quantity = [];
    foreach ($items_that_can_be_equally_distributed as $item) {
        for ($i = 1; $i < 100; $i++) {
            if ($item['price'] * $i > $max_price_after_margin) {
                $new_item_with_quantity_lesser = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $i - 1,
                    'total_price' => $item['price'] * ($i - 1),
                ];
                $new_item_with_quantity_greater = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $i - 1,
                    'total_price' => $item['price'] * ($i),
                ];

                $all_possible_items_with_quantity[] = [$new_item_with_quantity_lesser, $new_item_with_quantity_greater];
                break;
            }
        }
    }

    return $all_possible_items_with_quantity;
}

function pick_elements(array $data, int $max_price, int $max_variation): array
{
    usort($data, function ($a, $b)
    {
        return $a['price'] <=> $b['price'];
    });

    list($max_price_after_margin, $items_that_can_be_equally_distributed) = find_items_that_can_be_equally_distributed($data, $max_price, $max_variation);

    if ($max_variation === 1) {
        $best_price = 0;
        $picked_item = '';
        foreach ($items_that_can_be_equally_distributed as $item) {
            $quantity = floor($max_price / $item['price']);
            $current_price_sum = $item['price'] * $quantity;
            if ($current_price_sum > $best_price && $current_price_sum < $max_price) {
                $best_price = $current_price_sum;
                $picked_item = [
                    'id' => $item['id'],
                    'quantity' => $quantity,
                    'total_amount' => $current_price_sum,
                ];
            }
        }
        $picked_elements = [$picked_item];
    } else {
        $all_possible_items_with_quantity = find_margin_for_each_item_after_multiplied_by_quantity($items_that_can_be_equally_distributed, $max_price_after_margin);

        $x = [];
        for ($i = 0; $i < count($all_possible_items_with_quantity); $i++) {
            $x[] = $i;
        }

        $npr_combinations = get_npr($x, $max_variation);

        $all_binary_combinations = get_all_binary_combinations_of_given_length($max_variation);

        $best_price = 0;
        $best_npr = [];
        $best_combination = [];

        foreach ($npr_combinations as $npr_combination) {
            foreach ($all_binary_combinations as $binary_combination) {
                for ($binary_val_index = 0; $binary_val_index < count($binary_combination); $binary_val_index++) {
                    $current_price_sum = 0;
                    foreach ($npr_combination as $item_index) {
                        $binary_val = $binary_combination[$binary_val_index];
                        $current_price_sum += $all_possible_items_with_quantity[$item_index][$binary_val]['total_price'];
                    }
                    if ($current_price_sum > $best_price && $current_price_sum < $max_price) {
                        $best_price = $current_price_sum;
                        $best_npr = $npr_combination;
                        $best_combination = $binary_combination;
                    }
                }
            }
        }

        $picked_elements = [];

        for ($i = 0; $i < count($best_combination); $i++) {
            $binary_val = $best_combination[$i];
            $item = [
                'id' => $all_possible_items_with_quantity[$best_npr[$i]][$binary_val]['id'],
                'quantity' => $all_possible_items_with_quantity[$best_npr[$i]][$binary_val]['quantity'],
                'total_amount' => $all_possible_items_with_quantity[$best_npr[$i]][$binary_val]['total_price'],
            ];
            $picked_elements[] = $item;
        }
    }

    return [
        'best_price' => $best_price,
        'picked_elements' => $picked_elements,
    ];
}

$data = [
    ['id'=>1, 'name'=>'1g Heraeus', 'price'=>7850],
    ['id'=>2, 'name'=>'2g Kinebar', 'price'=>15850],
    ['id'=>3, 'name'=>'5g Heimerle', 'price'=>36335],
    ['id'=>4, 'name'=>'5g Vacambi', 'price'=>40485],
    ['id'=>5, 'name'=>'10g Heraeus', 'price'=>67485],
    ['id'=>6, 'name'=>'10g Valcambi', 'price'=>74105],
    ['id'=>7, 'name'=>'1/10oz Maple', 'price'=>24080],
    ['id'=>8, 'name'=>'1/10oz Krugerrand', 'price'=>23420],
    ['id'=>9, 'name'=>'1oz Maple', 'price'=>195575],
    ['id'=>10, 'name'=>'1oz Krugerrand', 'price'=>196095],
    ['id'=>11, 'name'=>'1oz Big Five Elephant', 'price'=>194765],
    ['id'=>12, 'name'=>'1oz Maple', 'price'=>3560],
    ['id'=>13, 'name'=>'1/4 Arche Noah', 'price'=>1185],
    ['id'=>14, 'name'=>'1kg Fiji Bar', 'price'=>113225],
    ['id'=>15, 'name'=>'100g Heimerle', 'price'=>21230],
];

$max_price = 5000000;
$max_variation = 3;

var_dump(pick_elements($data, $max_price, $max_variation));
