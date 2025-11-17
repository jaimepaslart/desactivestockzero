# Auto Disable Stock Zero - Module PrestaShop

Module PrestaShop 1.7 qui désactive automatiquement les produits lorsque leur stock atteint zéro.

## Vue d'ensemble

Ce repository contient un module PrestaShop prêt à l'emploi qui surveille automatiquement le stock de vos produits et les désactive lorsque leur quantité disponible atteint 0.

### Fonctionnalités principales

- ✅ **Traitement à l'installation** : Désactive tous les produits déjà en stock zéro + vidage automatique du cache
- ✅ **Surveillance continue** : Désactivation automatique des produits qui passent en rupture de stock
- ✅ Support des produits simples et avec combinaisons
- ✅ Compatible multistore (respecte le contexte de la boutique)
- ✅ Logging complet dans les logs PrestaShop
- ✅ Plug-and-play (aucune configuration nécessaire)
- ✅ Code optimisé et documenté

### Comportement

**À l'installation** : Le module scanne tous les produits actifs, désactive immédiatement ceux qui ont un stock à 0, et vide le cache pour une prise d'effet instantanée.

**Désactivation continue** : Lorsque le stock total d'un produit atteint 0, le produit est automatiquement désactivé.

**Réactivation** : Le module NE réactive PAS automatiquement les produits lorsque du stock est ajouté. Cela reste une action manuelle du marchand pour éviter toute réactivation non souhaitée.

## Structure du projet

```
desactivestockzero/
├── .claude/                          # Configuration Claude Code
│   ├── agents/
│   │   └── prestashop-developer.md  # Agent spécialisé PrestaShop
│   └── commands/
│       └── generate-tests.md        # Commande de génération de tests
├── autodisablestockzero/            # Dossier du module PrestaShop
│   ├── autodisablestockzero.php     # Fichier principal du module
│   ├── config.xml                   # Configuration du module
│   ├── index.php                    # Fichier de sécurité
│   └── readme.md                    # Documentation du module
├── .gitignore                       # Fichiers à ignorer par Git
└── README.md                        # Ce fichier
```

## Installation

### Prérequis

- PrestaShop 1.7.0 à 1.7.8.x
- PHP 7.3.x minimum
- Accès au back-office PrestaShop

### Installation via le back-office (recommandé)

1. **Créer le ZIP du module** :
   ```bash
   cd autodisablestockzero
   zip -r autodisablestockzero.zip .
   ```

2. **Installer dans PrestaShop** :
   - Connectez-vous au back-office PrestaShop
   - Allez dans **Modules > Module Manager**
   - Cliquez sur **Importer un module**
   - Sélectionnez le fichier `autodisablestockzero.zip`
   - Cliquez sur **Installer**

### Installation manuelle

1. **Copier le module** :
   ```bash
   cp -r autodisablestockzero /path/to/prestashop/modules/
   ```

2. **Installer depuis le back-office** :
   - Connectez-vous au back-office
   - Allez dans **Modules > Module Manager**
   - Recherchez "Auto Disable Stock Zero"
   - Cliquez sur **Installer**

## Utilisation

Le module fonctionne automatiquement une fois installé :

1. **Traitement initial** : À l'installation, tous les produits actifs avec stock = 0 sont désactivés
2. **Surveillance automatique** : Le module surveille tous les changements de stock
3. **Désactivation** : Quand un produit atteint un stock de 0, il est désactivé automatiquement
4. **Logs** : Toutes les actions sont enregistrées dans les logs PrestaShop
5. **Réactivation manuelle** : Pour réactiver un produit, ajoutez du stock puis activez-le manuellement

## Technique

### Hooks et méthodes

**À l'installation** :
- `processExistingProducts()` : Scanne et désactive tous les produits actifs avec stock = 0
- `clearCache()` : Vide le cache PrestaShop pour prise d'effet immédiate

**En continu** :
- `actionUpdateQuantity` : Déclenché à chaque modification de stock

### Logique de désactivation

```php
Installation :
1. Récupération de tous les produits actifs
2. Pour chaque produit, calcul du stock total
3. Si stock <= 0 : désactivation
4. Vidage automatique du cache PrestaShop

En continu :
1. Récupération de l'ID produit
2. Calcul du stock total (toutes combinaisons)
3. Si stock <= 0 :
   - Désactivation du produit (active = 0)
   - Enregistrement dans les logs
4. Si stock > 0 :
   - Aucune action
```

### Compatibilité multistore

Le module respecte le contexte de la boutique active et ne modifie que les produits de la boutique concernée.

## Logs

Consultez les logs PrestaShop pour suivre les actions du module :

**Back-office > Paramètres avancés > Logs**

Types de logs générés :
- Installation/désinstallation
- Désactivation de produits
- Erreurs éventuelles

## Développement

### Configuration du projet

Le projet inclut :
- **Agent PrestaShop** : Agent Claude Code spécialisé pour le développement PrestaShop
- **Commande generate-tests** : Génération automatique de tests

### Contribuer

1. Clonez le repository
2. Créez une branche pour votre fonctionnalité
3. Committez vos changements
4. Créez une pull request

## Support

Pour toute question ou problème :
- Vérifiez les logs PrestaShop
- Consultez la documentation du module dans `autodisablestockzero/readme.md`
- Ouvrez une issue sur GitHub

## Licence

MIT License

## Auteur

Paul Bihr - 2025

## Notes

- Le module ne nécessite aucune configuration
- Compatible avec les installations mono et multi-boutiques
- Testé sur PrestaShop 1.7.7.8 avec PHP 7.3
