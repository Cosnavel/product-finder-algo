<?php

function combinations($arr, $size) {
    $results = array();
    $combination = array();
    generateCombinations($arr, $size, 0, $combination, $results);
    return $results;
}

function generateCombinations($arr, $size, $start, &$combination, &$results) {
    if (count($combination) == $size) {
        array_push($results, $combination);
        return;
    }

    for ($i = $start; $i < count($arr); $i++) {
        array_push($combination, $arr[$i]);
        generateCombinations($arr, $size, $i + 1, $combination, $results);
        array_pop($combination);
    }
}

function get_npr($x, $r)
{
    $npr_combinations = combinations($x, $r);
    return $npr_combinations;
}

function get_all_binary_combinations_of_given_length($n)
{
    $all_binary_combinations = array();
    for ($i = 0; $i < pow(2, $n); $i++) {
        $binary = decbin($i);
        $binary = str_pad($binary, $n, "0", STR_PAD_LEFT);
        $all_binary_combinations[] = str_split($binary);
    }
    return $all_binary_combinations;
}

function find_items_that_can_be_equally_distributed($data, $max_price, $max_variation)
{
    $max_price_after_equally_distributed = $max_price / $max_variation;
    $max_price_after_margin = $max_price_after_equally_distributed + $max_price_after_equally_distributed * 0.1;
    $items_that_can_be_equally_distributed = [];
    for ($i = 0; $i < count($data); $i++) {
        if ($data[$i]['price'] <= $max_price_after_margin) {
            array_push($items_that_can_be_equally_distributed, $data[$i]);
        }
    }

    return [$max_price_after_margin, $items_that_can_be_equally_distributed];
}

function find_margin_for_each_item_after_multiplied_by_quantity($items_that_can_be_equally_distributed, $max_price_after_margin)
{
    $all_possible_items_with_quantity = [];
    foreach ($items_that_can_be_equally_distributed as $item) {
        for ($i = 1; $i < 100; $i++) {
            if ($item['price'] * $i > $max_price_after_margin) {
                $new_item_with_quantity_lesser = [];
                $new_item_with_quantity_lesser['id'] = $item['id'];
                $new_item_with_quantity_lesser['name'] = $item['name'];
                $new_item_with_quantity_lesser['price'] = $item['price'];
                $new_item_with_quantity_lesser['quantity'] = $i - 1;
                $new_item_with_quantity_lesser['total_price'] = $item['price'] * ($i - 1);
                $new_item_with_quantity_greater = [];
                $new_item_with_quantity_greater['id'] = $item['id'];
                $new_item_with_quantity_greater['name'] = $item['name'];
                $new_item_with_quantity_greater['price'] = $item['price'];
                $new_item_with_quantity_greater['quantity'] = $i - 1;
                $new_item_with_quantity_greater['total_price'] = $item['price'] * ($i);

                array_push($all_possible_items_with_quantity, [$new_item_with_quantity_lesser, $new_item_with_quantity_greater]);
                break;
            }
        }
    }

    return $all_possible_items_with_quantity;
}

function pick_elements($data, $max_price, $max_variation)
{
    usort($data, function ($a, $b)
    {
        return $a['price'] <=> $b['price'];
    });

    list($max_price_after_margin, $items_that_can_be_equally_distributed) = find_items_that_can_be_equally_distributed($data, $max_price, $max_variation);

    if ($max_variation == 1) {
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
                ];
            }
        }
        echo 'best price :'.$best_price."\n";
        $picked_elements = [$picked_item];
    } else {
        $all_possible_items_with_quantity = find_margin_for_each_item_after_multiplied_by_quantity($items_that_can_be_equally_distributed, $max_price_after_margin);

        $x = [];
        for ($i = 0; $i < count($all_possible_items_with_quantity); $i++) {
            array_push($x, $i);
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

        echo 'best_price : '.$best_price."\n";
        $picked_elements = [];

        for ($i = 0; $i < count($best_combination); $i++) {
            $binary_val = $best_combination[$i];
            $item = [
                'id' => $all_possible_items_with_quantity[$best_npr[$i]][$binary_val]['id'],
                'quantity' => $all_possible_items_with_quantity[$best_npr[$i]][$binary_val]['quantity'],
            ];
            array_push($picked_elements, $item);
        }
    }

    echo "==============================\n";
    echo 'picked_elements : ';

    return $picked_elements;
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

$max_price = 100000;
$max_variation = 3;
var_dump(pick_elements($data, $max_price, $max_variation));
