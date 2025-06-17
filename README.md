# PPP Project - Backend

Ce dépôt contient le backend du projet PPP, une API Laravel pour la gestion des dossiers patients médicaux et l’intégration avec Orthanc pour la gestion des images DICOM.

## Prérequis

- PHP >= 8.1
- Composer
- MySQL ou PostgreSQL
- Orthanc (serveur DICOM)
- [Optionnel] Redis (pour les files d’attente)

## Installation

1. Clonez le dépôt :

   ```bash
   git clone https://github.com/votre-utilisateur/ppp-back.git
   cd ppp-back
   ```

2. Installez les dépendances PHP :

   ```bash
   composer install
   ```

3. Copiez le fichier d’exemple d’environnement et configurez-le :

   ```bash
   cp .env.example .env
   ```

   Modifiez les variables suivantes dans `.env` :
   - `DB_*` pour la base de données
   - `ORTHANC_URL` pour l’URL de votre serveur Orthanc

4. Générez la clé d’application Laravel :

   ```bash
   php artisan key:generate
   ```

5. Lancez les migrations et (optionnel) les seeders :

   ```bash
   php artisan migrate
   # php artisan db:seed
   ```

## Lancement du serveur

```bash
php artisan serve
```

L’API sera accessible sur [http://localhost:8000](http://localhost:8000).

## Endpoints principaux

- `POST /api/login` : Authentification
- `GET /api/dossiers/{id}` : Récupérer un dossier patient (avec sous-dossiers)
- `POST /api/examens-imagerie` : Créer un examen d’imagerie
- `POST /api/dicom/upload` : Uploader une image DICOM (envoie à Orthanc et sauvegarde en base)
- Autres endpoints pour admissions, consultations, etc.

## Intégration Orthanc

- L’API `/api/dicom/upload` reçoit un fichier DICOM, l’envoie à Orthanc (`POST /instances`), puis sauvegarde les IDs Orthanc et les métadonnées dans la base.
- Configurez l’URL Orthanc dans `.env` (`ORTHANC_URL=http://localhost:8042`).

## Structure du projet

- `app/Models/` : Modèles Eloquent (DossierPatient, ExamenImagerie, ImageDicom, etc)
- `app/Http/Controllers/` : Contrôleurs API
- `routes/api.php` : Définition des routes API
- `database/migrations/` : Migrations de la base

## Tests

```bash
php artisan test
```

## Contribution

1. Forkez le projet
2. Créez une branche (`git checkout -b feature/ma-feature`)
3. Commitez vos modifications (`git commit -am 'Ajout de ma feature'`)
4. Pushez la branche (`git push origin feature/ma-feature`)
5. Ouvrez une Pull Request

## Licence

MIT

---

**Contact** : Atinkene