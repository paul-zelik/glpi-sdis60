import sys
import json
import csv
from collections import defaultdict

def load_category_ids(csv_file):
    category_ids = {}
    with open(csv_file, 'r', encoding='utf-8') as f:
        reader = csv.reader(f, delimiter=';')
        next(reader)  # Skip the header row
        for row in reader:
            if len(row) < 3:
                continue
            category_name = row[0].strip()
            category_id = row[2].strip()
            category_ids[category_name] = category_id
    return category_ids

def load_training_data(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        return None, None

    word_to_category = defaultdict(lambda: defaultdict(int), {
        k: defaultdict(int, v) for k, v in data["word_to_category"].items()
    })
    category_count = defaultdict(int, data["category_count"])
    return word_to_category, category_count

def predict_category(description, word_to_category, category_count, constraint_category=None):
    words = description.lower().split()
    category_scores = defaultdict(float)

    for word in words:
        if word in word_to_category:
            for category, count in word_to_category[word].items():
                category_scores[category] += count

    for category, count in category_count.items():
        category_scores[category] += 0.1 * count

    if constraint_category:
        for category in category_scores:
            if constraint_category.lower() in category.lower():
                category_scores[category] += 1000

    sorted_categories = sorted(category_scores.items(), key=lambda x: x[1], reverse=True)

    return sorted_categories[0][0] if sorted_categories else "Unknown"

def predict_for_input(input_description, model_file, csv_file, constraint_category=None):
    word_to_category, category_count = load_training_data(model_file)

    if word_to_category is None or category_count is None:
        return "Error loading training data."

    predicted_category = predict_category(input_description, word_to_category, category_count, constraint_category)
    category_ids = load_category_ids(csv_file)
    
    return category_ids.get(predicted_category, "ID non trouvé")

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python predict.py <description> <model_file> <constraint_category>")
        sys.exit(1)

    input_description = sys.argv[1]
    model_file = sys.argv[2]
    constraint_category = sys.argv[3]
    csv_file = "categorie.csv"

    category_id = predict_for_input(input_description, model_file, csv_file, constraint_category)
    print(f"{category_id}")
