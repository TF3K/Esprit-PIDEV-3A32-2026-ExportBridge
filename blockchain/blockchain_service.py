from flask import Flask, request, jsonify
import hashlib
import json
import time

app = Flask(__name__)

class Blockchain:
    def __init__(self):
        self.chain = []
        # Création du bloc de genèse
        self.create_block(previous_hash='0', contract_data="Genesis Block")

    def create_block(self, previous_hash, contract_data):
        block = {
            'index': len(self.chain) + 1,
            'timestamp': time.time(),
            'data': contract_data,
            'previous_hash': previous_hash,
            'hash': ''
        }
        # Génération du hash SHA-256 du bloc
        encoded_block = json.dumps(block, sort_keys=True).encode()
        block['hash'] = hashlib.sha256(encoded_block).hexdigest()
        self.chain.append(block)
        return block

    def get_last_block(self):
        return self.chain[-1]

# Initialisation de la blockchain
export_bridge_blockchain = Blockchain()

@app.route('/api/sign-contract', methods=['POST'])
def sign_contract():
    values = request.get_json()

    # Vérification des données requises
    required = ['company_name', 'market_name', 'email']
    if not all(k in values for k in required):
        return 'Missing values', 400

    # Récupération du dernier bloc pour lier le nouveau
    last_block = export_bridge_blockchain.get_last_block()
    
    # Création du nouveau contrat (bloc)
    new_block = export_bridge_blockchain.create_block(
        previous_hash=last_block['hash'],
        contract_data=values
    )

    response = {
        'message': 'Contrat électronique créé et miné avec succès',
        'index': new_block['index'],
        'contract_hash': new_block['hash'],
        'previous_hash': new_block['previous_hash']
    }
    return jsonify(response), 201

@app.route('/api/chain', methods=['GET'])
def get_chain():
    response = {
        'chain': export_bridge_blockchain.chain,
        'length': len(export_bridge_blockchain.chain)
    }
    return jsonify(response), 200

if __name__ == '__main__':
    # On lance ce serveur sur le port 5000
    app.run(host='0.0.0.0', port=5000)