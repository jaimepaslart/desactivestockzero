# Auto Disable Stock Zero

Module PrestaShop qui désactive automatiquement les produits lorsque leur stock atteint zéro.

## Description

Ce module surveille les changements de stock et désactive automatiquement les produits lorsque leur stock total atteint 0. Le module fonctionne aussi bien pour les produits simples que pour les produits avec combinaisons.

**Important** : Le module désactive uniquement les produits quand le stock atteint 0. Il ne les réactive PAS automatiquement lorsque du stock est ajouté - cela reste une action manuelle du marchand.

## Caractéristiques

- Désactivation automatique des produits en rupture de stock
- Support des produits simples et avec combinaisons
- Compatible avec les boutiques multiples (contexte boutique respecté)
- Logging complet des actions dans les logs PrestaShop
- Aucune configuration nécessaire (plug-and-play)
- Code optimisé et documenté

## Compatibilité

- PrestaShop : 1.7.0 à 1.7.8.x
- PHP : 7.3.x minimum
- Compatible multistore

## Installation

### Via le back-office PrestaShop (recommandé)

1. Téléchargez ou clonez ce repository
2. Compressez le dossier `autodisablestockzero` en fichier ZIP
3. Dans votre back-office PrestaShop, allez dans **Modules > Module Manager**
4. Cliquez sur **Importer un module**
5. Sélectionnez le fichier ZIP
6. Cliquez sur **Installer**

### Installation manuelle

1. Copiez le dossier `autodisablestockzero` dans le dossier `modules/` de votre PrestaShop
2. Dans le back-office, allez dans **Modules > Module Manager**
3. Recherchez "Auto Disable Stock Zero"
4. Cliquez sur **Installer**

## Utilisation

Une fois installé, le module fonctionne automatiquement sans configuration :

1. Lorsque le stock d'un produit atteint 0, le produit est automatiquement désactivé
2. Le produit n'apparaît plus sur la boutique
3. L'action est enregistrée dans les logs PrestaShop
4. Pour réactiver le produit, vous devez :
   - Ajouter du stock
   - Réactiver manuellement le produit dans le back-office

## Fonctionnement technique

Le module utilise le hook `actionUpdateQuantity` qui est déclenché à chaque modification de stock. Lorsqu'une modification survient :

1. Le module récupère l'ID du produit concerné
2. Il calcule le stock total disponible (toutes combinaisons confondues)
3. Si le stock total est ≤ 0 :
   - Le produit est désactivé (`active = 0`)
   - L'action est enregistrée dans les logs
4. Si le stock total est > 0 :
   - Aucune action (le produit reste dans son état actuel)

## Logs

Toutes les actions importantes sont enregistrées dans les logs PrestaShop :

- Installation/désinstallation du module
- Désactivation de produits
- Erreurs éventuelles

Pour consulter les logs : **Paramètres avancés > Logs**

## Désinstallation

1. Dans le back-office, allez dans **Modules > Module Manager**
2. Recherchez "Auto Disable Stock Zero"
3. Cliquez sur **Désinstaller**

Le module se désinstalle proprement sans laisser de traces dans la base de données.

## Support

Pour toute question ou problème :
- Consultez les logs PrestaShop
- Vérifiez que le module est bien installé et actif
- Assurez-vous que votre version de PrestaShop est compatible

## Licence

MIT License

## Auteur

Paul Bihr - 2025

## Logo

Pour ajouter un logo au module dans le back-office, placez un fichier `logo.png` (taille recommandée : 57x57 pixels) dans le dossier du module.
