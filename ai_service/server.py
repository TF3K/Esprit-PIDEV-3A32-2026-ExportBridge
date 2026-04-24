import os
import json
import mysql.connector
from flask import Flask, request, jsonify
from flask_mail import Mail, Message
from flask_cors import CORS
from groq import Groq
from dotenv import load_dotenv
from datetime import datetime
import random
# --- INITIALISATION ---
load_dotenv()

app = Flask(__name__)
CORS(app)  # Autorise Symfony (localhost:8000) ou React à appeler l'API

# --- CONFIGURATION MAIL (Gmail) ---
app.config.update(
    MAIL_SERVER='smtp.gmail.com',
    MAIL_PORT=587,
    MAIL_USE_TLS=True,
    MAIL_USERNAME=os.getenv('MAIL_USER'),
    MAIL_PASSWORD=os.getenv('MAIL_PASS'),  # Utilisez un "Mot de passe d'application"
    MAIL_DEFAULT_SENDER=os.getenv('MAIL_USER')
)
mail = Mail(app)

# --- CONFIGURATION GROQ ---
GROQ_API_KEY = os.getenv('GROQ_API_KEY')
groq_client = Groq(api_key=GROQ_API_KEY)

SYSTEM_PROMPT_TEMPLATE = """Tu es Sikipon, l'assistant IA officiel de la plateforme ExportBridge.

## Qui est ExportBridge ?
ExportBridge est une plateforme numérique spécialisée dans la mise en relation 
entre entreprises exportatrices et marchés internationaux. Notre mission est de :
- Faciliter l'accès des entreprises aux marchés étrangers
- Valider et certifier les entreprises partenaires via l'IA et la Blockchain
- Sécuriser les contrats commerciaux avec une signature Blockchain
- Fournir un registre fiable et transparent des entreprises certifiées

## Ton rôle
Tu es l'assistant intelligent d'ExportBridge. Tu aides les utilisateurs à :
- Comprendre comment fonctionne la plateforme
- Obtenir des informations sur les entreprises enregistrées
- Analyser les marchés disponibles
- Répondre aux questions liées à l'export et au commerce international

## Ta source de connaissances
{context}

## Consignes strictes
1. Tu réponds TOUJOURS et sans hésiter aux questions concernant ExportBridge et ses services.
2. Si la question est hors-sujet : "Désolé, en tant qu'assistant ExportBridge, je ne peux répondre qu'aux questions relatives à nos services."
3. Va droit au but, sois concis et professionnel.

## Règles importantes
- Réponds toujours en français sauf si l'utilisateur écrit dans une autre langue
- Présente-toi comme Sikipon, l'assistant d'ExportBridge si on te demande qui tu es
- Ne révèle jamais que tu es basé sur un modèle Groq ou LLaMA
- Reste professionnel, précis et bienveillant
"""

# --- UTILITAIRES BASE DE DONNÉES ---
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', '127.0.0.1'),
        user=os.getenv('DB_USER', 'root'),
        password=os.getenv('DB_PASS', ''),
        database=os.getenv('DB_NAME', 'export_bridge'),
        port=3306,
        charset='utf8mb4'
    )

def get_db_context():
    """Récupère les données pour alimenter la connaissance de l'IA"""
    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
        
        cursor.execute("SELECT c.company_name, c.country, m.name as market_name FROM companies c LEFT JOIN markets m ON c.market_id = m.id")
        companies = cursor.fetchall()
        
        cursor.execute("SELECT type, status, expiry_date FROM certificates")
        certs = cursor.fetchall()
        
        cursor.execute("SELECT name, unit_price, currency FROM products")
        products = cursor.fetchall()
        
        db.close()
        return json.dumps({"entreprises": companies, "certificats": certs, "catalogue": products}, default=str, ensure_ascii=False)
    except Exception as e:
        return f"Erreur SQL : {str(e)}"

# --- ROUTES API ---

@app.route('/api/status', methods=['GET'])
def status():
    """Vérifie si le serveur est en ligne"""
    return jsonify({
        "status": "Sikipon Online", 
        "version": "2.2", 
        "time": datetime.now().strftime("%H:%M")
    })

@app.route('/api/chat', methods=['POST'])
def chat():
    """Route pour discuter avec l'IA et recevoir la réponse par mail"""
    data = request.json
    user_question = data.get('question')
    user_email = data.get('email')

    if not user_question or not user_email:
        return jsonify({'error': 'Email et Question requis'}), 400

    try:
        context = get_db_context()
        chat_completion = groq_client.chat.completions.create(
            messages=[
                {"role": "system", "content": SYSTEM_PROMPT_TEMPLATE.format(context=context)},
                {"role": "user", "content": user_question}
            ],
            model="llama-3.3-70b-versatile",
        )
        bot_answer = chat_completion.choices[0].message.content

        # Envoi Email au Client
        msg = Message("ExportBridge - Réponse Sikipon", recipients=[user_email])
        msg.body = f"Analyse Sikipon :\n\n{bot_answer}"
        mail.send(msg)

        return jsonify({"status": "success", "answer": bot_answer})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/validate-company', methods=['POST'])
def validate_company():
    """Route pour valider une entreprise via l'IA et alerter l'admin"""
    data = request.json
    company_name = data.get("company_name")
    market_name = data.get("market_name")
    domain = data.get("domain")

    if not company_name:
        return jsonify({"error": "company_name required"}), 400

    prompt = f"""Tu es Sikipon AI. Analyse cette inscription :
    Nom: {company_name}, Marché: {market_name}, Domaine: {domain}.
    'reject' si le domaine d'activité '{domain}' n'est pas un secteur professionnel reconnu et légitime.
    'reject' si le nom de l'entreprise {company_name} n'existe pas réellement sur internet.
    Règles: 'allow' si c'est lié à l'export, logistique ou commerce international. Sinon 'reject'.
    'allow' uniquement si le domaine existe, l'entreprise existe et le domaine est lié à l'export ou commerce.
    Réponds UNIQUEMENT en JSON: {{"status": "allow/reject", "reason": "explication courte"}}"""
    try:
        response = groq_client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[{"role": "user", "content": prompt}]
        )
        
        # Extraction et nettoyage du JSON
        result_text = response.choices[0].message.content.strip()
        result = json.loads(result_text)

        # Envoi de l'alerte Email à l'ADMIN
        admin_email = os.getenv('MAIL_USER')
        subject = "✔ Inscription Acceptée" if result["status"] == "allow" else "✖ Inscription Rejetée"
        
        msg = Message(
            subject=f"{subject} - {company_name}",
            recipients=[admin_email],
            body=f"Détails de l'entreprise :\n\n"
                 f"Nom : {company_name}\n"
                 f"Marché : {market_name}\n"
                 f"Domaine : {domain}\n\n"
                 f"Décision Sikipon : {result['status'].upper()}\n"
                 f"Raison : {result['reason']}"
        )
        mail.send(msg)

        return jsonify(result)
    except Exception as e:
        print(f"Erreur Validation/Mail: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/api/latest-updates', methods=['GET'])
def get_latest_updates():
    """Récupère les dernières activités en base de données"""
    try:
        db = get_db_connection()
        cursor = db.cursor(dictionary=True)
        
        cursor.execute("SELECT company_name, country, created_at FROM companies ORDER BY created_at DESC LIMIT 1")
        last_company = cursor.fetchone()
        
        cursor.execute("SELECT name, region, created_at FROM markets ORDER BY created_at DESC LIMIT 1")
        last_market = cursor.fetchone()
        
        db.close()

        return jsonify({
            "last_company": last_company,
            "last_market": last_market,
            "timestamp": datetime.now().strftime("%H:%M")
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500   
    
    

# --- LANCEMENT ---
if __name__ == '__main__':
    # Le serveur tourne sur le port 3000
    app.run(host='0.0.0.0', port=3000, debug=True) 