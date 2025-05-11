import sys
sys.path.append(r"C:\Users\HP\AppData\Local\Packages\PythonSoftwareFoundation.Python.3.11_qbz5n2kfra8p0\LocalCache\local-packages\Python311\site-packages")
import numpy as np
from flask import Flask, request, jsonify, render_template
import random
from transformers import AutoTokenizer, AutoModelForCausalLM
import torch
import datetime

app = Flask(__name__, static_folder='static', template_folder='templates')

# Configuration du modèle local
model_name = "gpt2"  # Exemple simple - à remplacer par un modèle médical si disponible
tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForCausalLM.from_pretrained(model_name)

# Dictionnaire de diagnostics par maladie (fallback)
diagnostics = {
    'fièvre': [
        "Infection virale probable: Élévation de la température corporelle entre 38°C et 39.5°C. Causes principales: rhinovirus, coronavirus, virus influenza. Gravité généralement faible à modérée. Surveiller déshydratation et convulsions fébriles chez les enfants.",
        "État fébrile d'origine indéterminée: Température supérieure à 38.3°C persistant plus de 3 jours. Causes multiples possibles: infection bactérienne, virale, parasitaire ou pathologie inflammatoire. Nécessite des investigations complémentaires si persistance."
    ],
    'douleur thoracique': [
        "Douleur thoracique d'origine musculo-squelettique: Douleur exacerbée par les mouvements et la palpation. Causes: effort physique, traumatisme mineur, inflammation costochondrale. Gravité faible. Pronostic excellent avec repos et analgésiques.",
        "Douleur thoracique nécessitant évaluation cardiologique urgente: Douleur constrictive, irradiant dans le bras gauche/mâchoire. Causes possibles: syndrome coronarien aigu, péricardite, dissection aortique. Gravité potentiellement élevée. Complications: arythmie, insuffisance cardiaque, décès."
    ],
    'vertiges': [
        "Vertige positionnel paroxystique bénin: Sensation rotatoire brève déclenchée par changements de position. Cause: déplacement d'otolithes dans l'oreille interne. Gravité faible. Complications rares, essentiellement risque de chute.",
        "Syndrome vestibulaire périphérique: Sensation d'instabilité avec nausées et nystagmus. Causes possibles: névrite vestibulaire, maladie de Menière. Gravité modérée. Complications: troubles de l'équilibre persistants, anxiété secondaire."
    ],
    'toux': [
        "Toux aiguë d'origine virale: Toux sèche évoluant vers une toux productive. Causes: infections des voies respiratoires supérieures. Durée typique: 1-2 semaines. Complications rares: surinfection bactérienne.",
        "Toux chronique à investiguer: Toux persistant plus de 8 semaines. Causes possibles: asthme, RGO, syndrome d'écoulement post-nasal, BPCO. Gravité variable selon étiologie. Nécessite investigations complémentaires."
    ],
    'nausée': [
        "Gastro-entérite aiguë probable: Nausées associées à des vomissements et/ou diarrhée. Causes: infection virale (norovirus, rotavirus) ou bactérienne. Gravité généralement modérée. Complication principale: déshydratation.",
        "Nausées d'origine multifactorielle: Symptôme isolé sans contexte infectieux évident. Causes possibles: troubles digestifs fonctionnels, effets médicamenteux, troubles vestibulaires, stress. Évaluation complémentaire recommandée si persistance."
    ],
    'mal de tête': [
        "Céphalée de tension: Douleur bilatérale, constrictive, d'intensité légère à modérée. Causes: stress, posture inadéquate, fatigue. Gravité faible. Évolution généralement favorable avec analgésiques et gestion du stress.",
        "Migraine possible: Douleur pulsatile, unilatérale, d'intensité modérée à sévère, aggravée par l'activité physique. Causes: facteurs génétiques, hormonaux, environnementaux. Peut s'accompagner de nausées, photophobie et phonophobie. Durée: 4-72h sans traitement."
    ]
}

# Templates pour les diagnostics différents
diagnostic_templates = {
    'mal de tête': [
        "Céphalée {type}: {description}. Causes possibles: {causes}. Gravité: {gravite}. {recommendations}.",
        "Syndrome de {type}: Manifestation de douleurs {description}. Origine probable: {causes}. Niveau de risque: {gravite}. Approche thérapeutique recommandée: {recommendations}.",
        "Tableau clinique évocateur de {type}: Caractérisé par {description}. Étiologie: {causes}. Sévérité évaluée: {gravite}. Conduite à tenir: {recommendations}."
    ],
    'fièvre': [
        "État fébrile {type}: {description}. Origine présumée: {causes}. Niveau de gravité: {gravite}. {recommendations}.",
        "Syndrome fébrile de type {type}: Caractérisé par {description}. Facteurs étiologiques probables: {causes}. Degré de sévérité: {gravite}. Prise en charge conseillée: {recommendations}.",
        "Hyperthermie de type {type}: Tableau clinique avec {description}. Mécanismes probables: {causes}. Évaluation du risque: {gravite}. Stratégie thérapeutique: {recommendations}."
    ],
    'toux': [
        "Toux {type}: {description}. Étiologies possibles: {causes}. Gravité estimée: {gravite}. {recommendations}.",
        "Syndrome de toux {type}: Se manifestant par {description}. Mécanismes sous-jacents: {causes}. Niveau d'alerte clinique: {gravite}. Approche conseillée: {recommendations}.",
        "Tableau bronchique de type {type}: Présentant {description}. Facteurs causaux: {causes}. Évaluation de la sévérité: {gravite}. Plan de prise en charge: {recommendations}."
    ]
}

# Variations pour les parties du diagnostic
diagnostic_variations = {
    'mal de tête': {
        'type': ['céphalée de tension', 'migraine sans aura', 'algie vasculaire de la face', 'céphalée cervicogénique', 'céphalée médicamenteuse', 'céphalée de rebond'],
        'description': ['bilatérale et constrictive', 'pulsatile et unilatérale', 'lancinante et intermittente', 'sourde et diffuse', 'frontale irradiant vers les tempes', 'occipitale irradiant vers le vertex'],
        'causes': ['stress chronique et fatigue', 'facteurs hormonaux et génétiques', 'tension musculaire cervicale', 'abus médicamenteux', 'déshydratation et jeûne prolongé', 'troubles du sommeil et anxiété'],
        'gravite': ['légère à modérée', 'modérée nécessitant traitement', 'potentiellement invalidante', 'généralement bénigne', 'variable selon les épisodes', 'modérée à sévère'],
        'recommendations': ['repos en milieu calme et hydratation', 'traitement antalgique standard et relaxation', 'consultation spécialisée si récidive fréquente', 'éviction des facteurs déclenchants identifiés', 'réévaluation si symptômes persistants au-delà de 72h', 'suivi régulier et journal des crises recommandé']
    },
    'fièvre': {
        'type': ['viral', 'bactérien', 'inflammatoire', 'médicamenteux', 'd\'origine indéterminée', 'parasitaire'],
        'description': ['température entre 38°C et 39°C', 'pics thermiques vespéraux', 'hyperthermie persistante', 'fièvre oscillante', 'température corporelle fluctuante', 'fièvre modérée avec frissons'],
        'causes': ['infection des voies respiratoires supérieures', 'foyer infectieux profond', 'réaction auto-immune', 'processus inflammatoire systémique', 'infection communautaire', 'exposition à un agent pathogène'],
        'gravite': ['faible sans signes de gravité', 'modérée nécessitant surveillance', 'potentiellement préoccupante', 'à réévaluer sous 48h', 'nécessitant investigations complémentaires', 'à évaluer en fonction de l\'évolution'],
        'recommendations': ['antipyrétiques et hydratation abondante', 'repos et surveillance de l\'évolution', 'consultation médicale si persistance au-delà de 72h', 'éviter l\'automédication prolongée', 'monitoring des paramètres vitaux', 'consultation rapide si apparition de signes de gravité']
    },
    'toux': {
        'type': ['sèche irritative', 'productive', 'spasmodique', 'chronique', 'post-infectieuse', 'allergique'],
        'description': ['non productive et irritante', 'avec expectoration muqueuse', 'quinteuse à prédominance nocturne', 'persistante depuis plus de 3 semaines', 'accompagnée de sifflements respiratoires', 'déclenchée par facteurs environnementaux'],
        'causes': ['infection virale des voies aériennes', 'bronchite aiguë', 'asthme ou hyperréactivité bronchique', 'reflux gastro-œsophagien', 'exposition à des irritants respiratoires', 'écoulement post-nasal chronique'],
        'gravite': ['bénigne et auto-résolutive', 'modérée avec impact sur le sommeil', 'nécessitant évaluation diagnostique', 'avec retentissement sur qualité de vie', 'sans signes d\'alerte', 'à surveiller si terrain fragile'],
        'recommendations': ['hydratation et antitussifs si gêne importante', 'éviction des facteurs irritants identifiés', 'consultation médicale si persistance au-delà de 10 jours', 'éviter les environnements enfumés ou pollués', 'traitement symptomatique adapté', 'exploration fonctionnelle respiratoire si récidive']
    }
}

# Base de données médicaments pour l'ordonnance
medicaments = {
    'mal de tête': [
        {"nom": "Paracétamol 1000mg", "posologie": "1 comprimé toutes les 6 heures, sans dépasser 4 par jour", "duree": "5 jours"},
        {"nom": "Ibuprofène 400mg", "posologie": "1 comprimé toutes les 8 heures pendant les repas", "duree": "5 jours"},
        {"nom": "Triptan 50mg", "posologie": "1 comprimé au début de la crise, à renouveler après 2h si nécessaire", "duree": ""}
    ],
    'fièvre': [
        {"nom": "Paracétamol 500mg", "posologie": "2 comprimés toutes les 6 heures", "duree": "3 jours"},
        {"nom": "Paracétamol 1000mg", "posologie": "1 comprimé toutes les 6 heures", "duree": "3 jours"},
        {"nom": "Ibuprofène 200mg", "posologie": "2 comprimés toutes les 8 heures", "duree": "3 jours"}
    ],
    'toux': [
        {"nom": "Sirop antitussif", "posologie": "1 cuillère à soupe 3 fois par jour", "duree": "5 jours"},
        {"nom": "Pastilles pour la gorge", "posologie": "1 pastille à sucer toutes les 2-3 heures", "duree": "5 jours"},
        {"nom": "Solution saline nasale", "posologie": "2 pulvérisations dans chaque narine 3 fois par jour", "duree": "7 jours"}
    ],
    'douleur thoracique': [
        {"nom": "Nitroglycérine 0.4mg", "posologie": "1 comprimé sublingual en cas de douleur", "duree": ""},
        {"nom": "Aspirine 75mg", "posologie": "1 comprimé par jour", "duree": "30 jours"},
        {"nom": "Paracétamol 1000mg", "posologie": "1 comprimé toutes les 6 heures si douleur", "duree": "5 jours"}
    ],
    'vertiges': [
        {"nom": "Bétahistine 24mg", "posologie": "1 comprimé matin et soir", "duree": "30 jours"},
        {"nom": "Dimenhydrinate 50mg", "posologie": "1 comprimé toutes les 4-6 heures si nécessaire", "duree": "7 jours"},
        {"nom": "Méclozine 25mg", "posologie": "1 comprimé matin et soir", "duree": "5 jours"}
    ],
    'nausée': [
        {"nom": "Métoclopramide 10mg", "posologie": "1 comprimé 3 fois par jour avant les repas", "duree": "5 jours"},
        {"nom": "Dompéridone 10mg", "posologie": "1 comprimé 3 fois par jour avant les repas", "duree": "7 jours"},
        {"nom": "Ondansétron 4mg", "posologie": "1 comprimé toutes les 8 heures", "duree": "3 jours"}
    ]
}

# Recommandations non médicamenteuses
recommandations_hygiene = {
    'mal de tête': [
        "Repos dans un environnement calme et peu lumineux",
        "Hydratation régulière (au moins 1.5L d'eau par jour)",
        "Application locale de froid ou de chaud selon soulagement",
        "Éviter les facteurs déclenchants identifiés (stress, alcool, certains aliments)",
        "Techniques de relaxation et gestion du stress"
    ],
    'fièvre': [
        "Repos au lit dans une pièce tempérée (18-20°C)",
        "Hydratation abondante (minimum 2L par jour)",
        "Vêtements légers et découvrir si nécessaire",
        "Surveiller la température toutes les 4 heures",
        "Consulter si persistance au-delà de 3 jours ou apparition de nouveaux symptômes"
    ],
    'toux': [
        "Maintenir une bonne hydratation (eau, tisanes, bouillons)",
        "Surélever la tête pendant le sommeil",
        "Humidifier l'air ambiant",
        "Éviter les irritants respiratoires (tabac, pollution, parfums)",
        "Pratiquer des inhalations de vapeur d'eau"
    ],
    'douleur thoracique': [
        "Repos et limitation des efforts physiques",
        "Position semi-assise si inconfort",
        "Surveiller les facteurs aggravants",
        "Consulter en urgence si aggravation, irradiation, essoufflement",
        "Éviter le stress et les excitants (café, thé, tabac)"
    ],
    'vertiges': [
        "Éviter les mouvements brusques de la tête",
        "Se lever progressivement pour éviter l'hypotension orthostatique",
        "Utiliser une canne ou un appui en cas de déséquilibre important",
        "Éviter l'alcool et limiter la caféine",
        "Pratiquer des exercices de rééducation vestibulaire si recommandés"
    ],
    'nausée': [
        "Fractionner les repas (petites quantités plusieurs fois par jour)",
        "Privilégier les aliments froids ou tièdes (moins odorants)",
        "Éviter les aliments gras, épicés ou à forte odeur",
        "S'hydrater régulièrement en petites quantités",
        "Repos en position semi-assise après les repas"
    ]
}

def generate_varied_diagnostic(symptom):
    """Génère un diagnostic varié basé sur les templates et variations"""
    if symptom in diagnostic_templates and symptom in diagnostic_variations:
        template = random.choice(diagnostic_templates[symptom])
        variations = diagnostic_variations[symptom]
        
        # Substituer les variables dans le template
        for key in variations:
            value = random.choice(variations[key])
            template = template.replace('{'+key+'}', value)
            
        return template
    return None

def generate_ai_diagnostic(symptom):
    """Génère un diagnostic à l'aide du modèle d'IA local"""
    try:
        # Prompt pour guider le modèle
        prompt = f"En tant que médecin spécialiste, écrivez un diagnostic médical concis et professionnel pour un patient présentant comme symptôme principal: {symptom}. Incluez les causes possibles, la gravité, et les recommandations."
        
        # Tokenisation et génération
        inputs = tokenizer(prompt, return_tensors="pt")
        # Paramètres pour varier les résultats
        outputs = model.generate(
            inputs.input_ids, 
            max_length=150, 
            do_sample=True,
            temperature=0.9,
            top_p=0.92,
            top_k=50,
            no_repeat_ngram_size=2
        )
        
        # Décodage du résultat
        diagnostic = tokenizer.decode(outputs[0], skip_special_tokens=True)
        # Nettoyer la sortie pour n'avoir que la partie diagnostic (après le prompt)
        diagnostic = diagnostic.replace(prompt, "").strip()
        
        # Si le diagnostic est trop court ou ne semble pas pertinent
        if len(diagnostic) < 50 or "symptôme" not in diagnostic.lower():
            # Fallback vers la génération basée sur template
            fallback = generate_varied_diagnostic(symptom)
            if fallback:
                return fallback
        
        return diagnostic
    except Exception as e:
        print(f"Erreur lors de la génération du diagnostic par IA: {e}")
        # Fallback vers les templates
        fallback = generate_varied_diagnostic(symptom)
        if fallback:
            return fallback
        # Si tout échoue, utiliser un diagnostic statique
        return random.choice(diagnostics.get(symptom, ["Diagnostic non disponible. Une consultation médicale approfondie est recommandée."]))

def generate_prescription(symptom):
    """Génère une prescription médicale basée sur les symptômes"""
    prescription = []
    
    # Sélectionner 1 à 3 médicaments pertinents
    if symptom in medicaments:
        num_meds = random.randint(1, min(3, len(medicaments[symptom])))
        selected_meds = random.sample(medicaments[symptom], num_meds)
        prescription.extend(selected_meds)
    else:
        # Médicament générique si symptôme non répertorié
        prescription.append({
            "nom": "Traitement symptomatique adapté", 
            "posologie": "Selon prescription médicale", 
            "duree": "À évaluer par un professionnel de santé"
        })
    
    return prescription

def generate_recommendations(symptom):
    """Génère des recommandations non médicamenteuses"""
    if symptom in recommandations_hygiene:
        num_recs = random.randint(2, 4)
        return random.sample(recommandations_hygiene[symptom], num_recs)
    else:
        return [
            "Repos adapté à l'évolution des symptômes",
            "Hydratation régulière",
            "Consulter un professionnel de santé si persistance ou aggravation"
        ]

@app.route('/')
def index():
    """Page d'accueil"""
    return render_template('index.html')

@app.route('/generate_diagnostic', methods=['POST'])
def generate_diagnostic():
    """API pour générer un diagnostic"""
    data = request.json
    symptom = data.get('maladie', '')  # Changé 'symptom' à 'maladie'
    patient_info = data.get('patient_info', {})
    
    if not symptom:
        return jsonify({"error": "Aucune maladie spécifiée"}), 400
    
    # Générer le diagnostic
    diagnostic = generate_ai_diagnostic(symptom)
    
    # Date de consultation (aujourd'hui)
    date_consultation = datetime.datetime.now().strftime("%d/%m/%Y")
    
    response = {
        "diagnostic": diagnostic,
        "date_consultation": date_consultation
    }
    
    return jsonify(response)

@app.route('/generate_prescription', methods=['POST'])
def generate_prescription_api():
    """API pour générer une ordonnance"""
    data = request.json
    symptom = data.get('maladie', '')  # Utiliser 'maladie' pour être cohérent
    
    if not symptom:
        return jsonify({"error": "Aucune maladie spécifiée"}), 400
    
    # Générer la prescription
    prescription = generate_prescription(symptom)
    
    # Formater l'ordonnance comme texte
    ordonnance_text = ""
    for med in prescription:
        ordonnance_text += f"{med['nom']}, {med['posologie']}"
        if med['duree']:
            ordonnance_text += f" pendant {med['duree']}"
        ordonnance_text += ".\n"
    
    # Ajouter des recommandations non médicamenteuses
    recommendations = generate_recommendations(symptom)
    if recommendations:
        ordonnance_text += "\nRecommandations supplémentaires:\n- "
        ordonnance_text += "\n- ".join(recommendations)
    
    response = {
        "ordonnance": ordonnance_text.strip()
    }
    
    return jsonify(response)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)