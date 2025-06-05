import csv
import json
from collections import defaultdict
import random

def load_csv(file_path):
    data = []
    with open(file_path, 'r', encoding='utf-8') as f:
        reader = csv.reader(f, delimiter=';') 
        for row in reader:
            if len(row) < 7:
                print(f"Ligne ignorée : {row}")
                continue
            description = row[1] 
            category = row[6]   
            data.append((description.strip(), category.strip()))
    return data

def preprocess_data(data):
    word_to_category = defaultdict(lambda: defaultdict(int))
    category_count = defaultdict(int)

    for description, category in data:
        words = description.lower().split()
        for word in words:
            word_to_category[word][category] += 1
        category_count[category] += 1

    return word_to_category, category_count

def predict_category(description, word_to_category, category_count):
    words = description.lower().split()
    category_scores = defaultdict(float)

    for word in words:
        if word in word_to_category:
            for category, count in word_to_category[word].items():
                category_scores[category] += count

    for category, count in category_count.items():
        category_scores[category] += 0.1 * count

    return max(category_scores, key=category_scores.get) if category_scores else random.choice(list(category_count.keys()))

def train_ai(data, iterations=100):
    word_to_category, category_count = preprocess_data(data)
    training_log = []

    for _ in range(iterations):
        for description, true_category in data:
            predicted_category = predict_category(description, word_to_category, category_count)

            if predicted_category == true_category:
                for word in description.lower().split():
                    word_to_category[word][true_category] += 1
            else:
                for word in description.lower().split():
                    word_to_category[word][predicted_category] = max(0, word_to_category[word][predicted_category] - 1)

            training_log.append({
                "description": description,
                "true_category": true_category,
                "predicted_category": predicted_category,
                "success": predicted_category == true_category
            })

    return word_to_category, category_count, training_log

def save_training_data(word_to_category, category_count, training_log, output_file):
    with open(output_file, 'w') as f:
        json.dump({
            "word_to_category": word_to_category,
            "category_count": category_count,
            "training_log": training_log
        }, f, indent=4)

def load_training_data(file_path):
    with open(file_path, 'r') as f:
        data = json.load(f)
    word_to_category = defaultdict(lambda: defaultdict(int), {
        k: defaultdict(int, v) for k, v in data["word_to_category"].items()
    })
    category_count = defaultdict(int, data["category_count"])
    return word_to_category, category_count

def predict_for_input(input_description, model_file):
    word_to_category, category_count = load_training_data(model_file)
    predicted_category = predict_category(input_description, word_to_category, category_count)
    return predicted_category

def main():
    csv_file = "glpi.csv"
    output_file = "resultat.json"

    data = load_csv(csv_file)
    word_to_category, category_count, training_log = train_ai(data, iterations=100)

    save_training_data(word_to_category, category_count, training_log, output_file)
    print(f"Modèle et log d'entraînement sauvegardés dans {output_file}")

    request = ""
    request = input("Quelle est votre demande : ")
    while request != "exit":
        request = input("Quelle est votre demande : ")
        input_description = request
        predicted_category = predict_for_input(input_description, output_file)
        print(f"La catégorie prédite pour \"{input_description}\" est : {predicted_category}")

if __name__ == "__main__":
    main()
