import itertools

def get_npr(x,r):
    npr_combinations = list(itertools.combinations(x,r))
    return npr_combinations

def get_all_binary_combinations_of_given_length(n):
    all_binary_combinations  = [list(i) for i in itertools.product([0, 1], repeat=n)]
    return all_binary_combinations


def find_items_that_can_be_equally_distributed(data, max_price, max_variation):
    max_price_after_equally_distributed = max_price/max_variation
    max_price_after_margin = max_price_after_equally_distributed + max_price_after_equally_distributed*0.1
    items_that_can_be_equally_distributed = []
    for d in data:
        if d["price"] <= max_price_after_margin:
            items_that_can_be_equally_distributed.append(d)
    return max_price_after_margin, items_that_can_be_equally_distributed


def find_margin_for_each_item_after_multiplied_by_quantity(items_that_can_be_equally_distributed, max_price_after_margin):
    all_possible_items_with_quantity = []
    for item in items_that_can_be_equally_distributed:
        for i in range(1,100):
            if (item["price"]*i) > max_price_after_margin:
                new_item_with_quantity_lesser = {}
                new_item_with_quantity_lesser["id"] = item["id"]
                new_item_with_quantity_lesser["name"] = item["name"]
                new_item_with_quantity_lesser["price"] = item["price"]
                new_item_with_quantity_lesser["quantity"] = i-1
                new_item_with_quantity_lesser["total_price"] = item["price"]*(i-1)
                new_item_with_quantity_greater = {}
                new_item_with_quantity_greater["id"] = item["id"]
                new_item_with_quantity_greater["name"] = item["name"]
                new_item_with_quantity_greater["price"] = item["price"]
                new_item_with_quantity_greater["quantity"] = i-1
                new_item_with_quantity_greater["total_price"] = item["price"]*(i)

                all_possible_items_with_quantity.append([new_item_with_quantity_lesser, new_item_with_quantity_greater])
                break
    return all_possible_items_with_quantity

def pick_elements(data, max_price, max_variation):
    data.sort(key=lambda x: x["price"])

    max_price_after_margin, items_that_can_be_equally_distributed = find_items_that_can_be_equally_distributed(data, max_price, max_variation)

    if max_variation == 1:
        best_price = 0
        picked_item = ""
        for item in items_that_can_be_equally_distributed:
            quantity = max_price//item["price"]
            current_price_sum = item["price"] * quantity
            if current_price_sum > best_price and current_price_sum < max_price:
                best_price = current_price_sum
                picked_item = {
                    "id": item["id"],
                    "quantity": quantity
               }
        print ("best price :", best_price)
        picked_elements = [picked_item]

    else:
        all_possible_items_with_quantity = find_margin_for_each_item_after_multiplied_by_quantity(items_that_can_be_equally_distributed, max_price_after_margin)

        x = []
        for i in range(len(all_possible_items_with_quantity)):
            x.append(i)
        npr_combinations = get_npr(x,max_variation)

        all_binary_combinations = get_all_binary_combinations_of_given_length(max_variation)

        best_price = 0
        best_npr = []
        best_combination = []

        for npr_combination in npr_combinations:
            for binary_combination in all_binary_combinations:
                for binary_val_index in range(len(binary_combination)):
                    current_price_sum = 0
                    for item_index in npr_combination:
                        binary_val = binary_combination[binary_val_index]
                        current_price_sum += all_possible_items_with_quantity[item_index][binary_val]["total_price"]
                    if current_price_sum > best_price and current_price_sum < max_price:
                        best_price = current_price_sum
                        best_npr = npr_combination
                        best_combination = binary_combination

        print ("best_price :", best_price)
        picked_elements = []

        for i in range(len(best_combination)):
            binary_val = best_combination[i]
            item = {
                "id": all_possible_items_with_quantity[best_npr[i]][binary_val]["id"],
                "quantity": all_possible_items_with_quantity[best_npr[i]][binary_val]["quantity"],
                }
            picked_elements.append(item)

    print ("==============================")
    print ("picked_elements : ", end = "")
    return picked_elements


data = [{"id":1,"name":"1g Heraeus","price":7850},{"id":2,"name":"2g Kinebar","price":15850},{"id":3,"name":"5g Heimerle","price":36335},{"id":4,"name":"5g Vacambi","price":40485},
        {"id":5,"name":"10g Heraeus","price":67485},{"id":6,"name":"10g Valcambi","price":74105},{"id":7,"name":"1\/10oz Maple","price":24080},
        {"id":8,"name":"1\/10oz Krugerrand","price":23420},{"id":9,"name":"1oz Maple","price":195575},{"id":10,"name":"1oz Krugerrand","price":196095},
        {"id":11,"name":"1oz Big Five Elephant","price":194765},{"id":12,"name":"1oz Maple","price":3560},{"id":13,"name":"1\/4 Arche Noah","price":1185},
        {"id":14,"name":"1kg Fiji Bar","price":113225},{"id":15,"name":"100g Heimerle","price":21230}]

max_price = 3000000
max_variation = 3
print (pick_elements(data, max_price, max_variation))
