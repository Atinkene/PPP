<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table: users
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('sexe', 10);
            $table->date('date_naissance');
            $table->string('cin')->unique();
            $table->string('lieu_naissance');
            $table->string('nationalite');
            $table->string('role')->nullable();
            $table->text('adresse_postale')->nullable();
            $table->string('numero_telephone')->unique()->nullable();
            $table->string('login')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->index(['id', 'email', 'login']);
        });

        // Table: etablissements
        Schema::create('etablissements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->text('adresse');
            $table->string('type');
            $table->string('contact')->nullable();
            $table->uuid('directeur')->nullable();
            $table->timestamps();
            $table->foreign('directeur')->references('id')->on('users')->onDelete('set null');
            $table->index(['id', 'nom']);
        });

        // Table: services_hospitaliers
        Schema::create('services_hospitaliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_etablissement');
            $table->string('nom');
            $table->uuid('chef_service')->nullable();
            $table->timestamps();
            $table->foreign('id_etablissement')->references('id')->on('etablissements')->onDelete('cascade');
            $table->foreign('chef_service')->references('id')->on('users')->onDelete('set null');
            $table->index(['id', 'nom']);
        });

        // Table: patients
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_user');
            $table->string('groupe_sanguin', 10)->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->index(['id', 'id_user']);
        });

        // Table: professionnels_sante
        Schema::create('professionnels_sante', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_user');
            $table->uuid('id_service');
            $table->string('numero_rpps', 11)->unique()->nullable();
            $table->string('type', 50);
            $table->string('specialite', 100)->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_service')->references('id')->on('services_hospitaliers')->onDelete('cascade');
            $table->index(['id', 'id_user', 'numero_rpps']);
        });

        // Table: acteurs_non_medicaux
        Schema::create('acteurs_non_medicaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_user');
            $table->uuid('id_service');
            $table->string('role', 50);
            $table->string('numero_adeli', 9)->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_service')->references('id')->on('services_hospitaliers')->onDelete('cascade');
            $table->index(['id', 'id_user', 'id_service']);
        });

        // Table: dossiers_patients
        Schema::create('dossiers_patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_patient');
            $table->uuid('id_etablissement')->nullable();
            $table->timestamps();
            $table->foreign('id_patient')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('id_etablissement')->references('id')->on('etablissements')->onDelete('set null');
            $table->index(['id', 'id_patient']);
        });

        // Table: antecedents
        Schema::create('antecedents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_soin');
            $table->timestamps();
            $table->foreign('id_dossier_soin')->references('id')->on('dossiers_soins_medicaux')->onDelete('cascade');
            $table->index(['id_dossier_soin']);
        });

        // Table: allergies
        Schema::create('allergies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_antecedent');
            $table->string('nom');
            $table->timestamps();
            $table->foreign('id_antecedent')->references('id')->on('antecedents')->onDelete('cascade');
        });

        // Table: contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('adresse_postale')->nullable();
            $table->string('numero_telephone', 20)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->uuid('id_dossier_admin');
            $table->foreign('id_dossier_admin')->references('id')->on('dossiers_administratifs')->onDelete('cascade');
            $table->index(['id']);
        });

        // Table: assurances
        Schema::create('assurances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_admin');
            $table->string('numero_securite_social', 15)->nullable();
            $table->string('organisme_assurance_sante', 50)->nullable();
            $table->decimal('prise_en_charge', 5, 2)->nullable();
            $table->foreign('id_dossier_admin')->references('id')->on('dossiers_administratifs')->onDelete('cascade');
            $table->timestamps();
            $table->index(['id', 'id_dossier_admin']);
        });

        // Table: consentements
        Schema::create('consentements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 50);
            $table->uuid('id_dossier_admin');
            $table->string('statut', 20);
            $table->timestamp('date_autorisation')->nullable();
            $table->foreign('id_dossier_admin')->references('id')->on('dossiers_administratifs')->onDelete('cascade');
            $table->timestamps();
            $table->index(['id','id_dossier_admin_consent']);
        });

        // Table: dossiers_administratifs
        Schema::create('dossiers_administratifs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->uuid('id_contact');
            $table->uuid('id_assurance');
            $table->uuid('id_consentement');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            // $table->foreign('id_contact')->references('id')->on('contacts')->onDelete('cascade');
            $table->index(['id_dossier', 'id_contact'],  'dossier_admin_idx');
        });

        // Table: dossiers_admission_sejour
        Schema::create('dossiers_admission_sejour', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: admissions
        Schema::create('admissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_admission');
            $table->text('motif')->nullable();
            $table->uuid('id_service')->nullable();
            $table->timestamp('date')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_admission')->references('id')->on('dossiers_admission_sejour')->onDelete('cascade');
            $table->foreign('id_service')->references('id')->on('services_hospitaliers')->onDelete('set null');
            $table->index(['id_dossier_admission']);
        });

        // Table: comptes_rendus_hospitalisation
        Schema::create('comptes_rendus_hospitalisation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_admission');
            $table->text('diagnostic_principal')->nullable();
            $table->text('diagnostics_associes')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_admission')->references('id')->on('dossiers_admission_sejour')->onDelete('cascade');
            $table->index(['id_dossier_admission']);
        });

        // Table: dossiers_soins_medicaux
        Schema::create('dossiers_soins_medicaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: consultations
        Schema::create('consultations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_soins');
            $table->text('compte_rendu')->nullable();
            $table->text('examen_clinique')->nullable();
            $table->text('symptomes')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('recommandations')->nullable();
            $table->uuid('id_professionnel')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_soins')->references('id')->on('dossiers_soins_medicaux')->onDelete('cascade');
            $table->foreign('id_professionnel')->references('id')->on('professionnels_sante')->onDelete('set null');
            $table->index(['id_dossier_soins', 'id_professionnel']);
        });

        // Table: traitements
        Schema::create('traitements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_soins');
            $table->string('type', 50);
            $table->timestamp('date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_soins')->references('id')->on('dossiers_soins_medicaux')->onDelete('cascade');
        });

        // Table: medicaments
        Schema::create('medicaments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_traitement');
            $table->string('nom');
            $table->string('posologie')->nullable();
            $table->string('duree')->nullable();
            $table->timestamps();
            $table->foreign('id_traitement')->references('id')->on('traitements')->onDelete('cascade');
        });

        // Table: rendez_vous
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_patient');
            $table->uuid('id_professionnel')->nullable();
            $table->uuid('id_service')->nullable();
            $table->uuid('id_etablissement');
            $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->string('type');
            $table->string('statut')->default('planifié');
            $table->timestamps();
            $table->foreign('id_dossier_patient')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->foreign('id_professionnel')->references('id')->on('professionnels_sante')->onDelete('set null');
            $table->foreign('id_service')->references('id')->on('services_hospitaliers')->onDelete('set null');
            $table->foreign('id_etablissement')->references('id')->on('etablissements')->onDelete('cascade');
            $table->index(['id', 'date', 'statut']);
        });

        // Table: dossiers_sortie_suivi
        Schema::create('dossiers_sortie_suivi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: comptes_rendus_sortie
        Schema::create('comptes_rendus_sortie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_sortie');
            $table->timestamp('date')->nullable();
            $table->text('instructions')->nullable();
            $table->text('recommandations')->nullable();
            $table->uuid('id_professionnel')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_sortie')->references('id')->on('dossiers_sortie_suivi')->onDelete('cascade');
            $table->foreign('id_professionnel')->references('id')->on('professionnels_sante')->onDelete('set null');
            $table->index(['id_dossier_sortie', 'id_professionnel']);
        });

      

        // Table: dossiers_examens_complementaires
        Schema::create('dossiers_examens_complementaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: examens_imagerie
        Schema::create('examens_imagerie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_examen');
            $table->string('type', 50);
            $table->text('resultat')->nullable();
            $table->string('dicom_instance_id')->nullable();
            $table->string('study_id')->nullable();
            $table->string('patient_id')->nullable();
            $table->string('url')->nullable();
            $table->uuid('id_professionnel')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_examen')->references('id')->on('dossiers_examens_complementaires')->onDelete('cascade');
            $table->foreign('id_professionnel')->references('id')->on('professionnels_sante')->onDelete('set null');
            $table->index(['id_dossier_examen', 'dicom_instance_id']);
        });

        // Table: images_dicom
        Schema::create('images_dicom', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_examen_imagerie');
            $table->string('orthanc_instance_id');
            $table->string('study_id');
            $table->string('patient_id');
            $table->string('url')->nullable();
            $table->string('type');
            $table->timestamps();
            $table->foreign('id_examen_imagerie')->references('id')->on('examens_imagerie')->onDelete('cascade');
            $table->index(['orthanc_instance_id', 'id', 'study_id']);
        });

        // Table: dossiers_soins_infirmiers
        Schema::create('dossiers_soins_infirmiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: observations_infirmieres
        Schema::create('observations_infirmieres', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_infirmier');
            $table->timestamp('date')->nullable();
            $table->text('etat_general')->nullable();
            $table->uuid('id_professionnel')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_infirmier')->references('id')->on('dossiers_soins_infirmiers')->onDelete('cascade');
            $table->foreign('id_professionnel')->references('id')->on('professionnels_sante')->onDelete('set null');
            $table->index(['id_dossier_infirmier', 'id_professionnel'], 'InfPof_idx');
        });

        // Table: administrations_medicaments
        Schema::create('administrations_medicaments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_medicament');
            $table->uuid('id_dossier_infirmier');
            $table->uuid('id_professionnel')->nullable();
            $table->timestamp('date_administration')->nullable();
            $table->string('dose')->nullable();
            $table->timestamps();
            $table->foreign('id_medicament')->references('id')->on('medicaments')->onDelete('cascade');
            $table->foreign('id_dossier_infirmier')->references('id')->on('dossiers_soins_infirmiers')->onDelete('cascade');
            $table->foreign('id_professionnel')->references('id')->on('professionnels_sante')->onDelete('set null');
        });

        // Table: effets_secondaires
        Schema::create('effets_secondaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_traitement');
            $table->uuid('id_medicament')->nullable();
            $table->string('nom');
            $table->timestamp('date')->nullable();
            $table->timestamps();
            $table->foreign('id_traitement')->references('id')->on('traitements')->onDelete('cascade');
            $table->foreign('id_medicament')->references('id')->on('medicaments')->onDelete('set null');
        });

        // Table: vaccinations
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_antecedent');
            $table->string('nom');
            $table->timestamp('date')->nullable();
            $table->timestamps();
            $table->foreign('id_antecedent')->references('id')->on('antecedents')->onDelete('cascade');
        });

        // Table: operations
        Schema::create('operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_antecedent');
            $table->string('nom');
            $table->timestamp('date')->nullable();
            $table->timestamps();
            $table->foreign('id_antecedent')->references('id')->on('antecedents')->onDelete('cascade');
        });

        // Table: traitements_longue_duree
        Schema::create('traitements_longue_duree', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_antecedent');
            $table->string('nom');
            $table->timestamps();
            $table->foreign('id_antecedent')->references('id')->on('antecedents')->onDelete('cascade');
        });

        // Table: maladies_chroniques
        Schema::create('maladies_chroniques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_antecedent');
            $table->string('nom');
            $table->timestamps();
            $table->foreign('id_antecedent')->references('id')->on('antecedents')->onDelete('cascade');
        });

        // Table: contacts_urgence
        Schema::create('contacts_urgence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_admin');
            $table->string('lien_parente', 50)->nullable();
            $table->string('cause')->nullable();
            $table->timestamp('date')->nullable();
            $table->boolean('est_joint')->default(false);
            $table->uuid('id_user_contact')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_admin')->references('id')->on('dossiers_administratifs')->onDelete('cascade');
            $table->foreign('id_user_contact')->references('id')->on('users')->onDelete('set null');
            $table->index(['id_dossier_admin', 'id_user_contact'],'PatCont_idx');
        });

        // Table: dossiers_chirurgie_anesthesie
        Schema::create('dossiers_chirurgie_anesthesie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: fiches_preoperatoires
        Schema::create('fiches_preoperatoires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_chirurgie');
            $table->timestamp('date')->nullable();
            $table->string('nom');
            $table->timestamps();
            $table->foreign('id_dossier_chirurgie')->references('id')->on('dossiers_chirurgie_anesthesie')->onDelete('cascade');
            $table->index(['id_dossier_chirurgie']);
        });

        // Table: dossiers_psycho_sociaux
        Schema::create('dossiers_psycho_sociaux', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier');
            $table->timestamps();
            $table->foreign('id_dossier')->references('id')->on('dossiers_patients')->onDelete('cascade');
            $table->index(['id_dossier']);
        });

        // Table: evaluations_psychologiques
        Schema::create('evaluations_psychologiques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_psycho');
            $table->timestamp('date')->nullable();
            $table->text('diagnostic')->nullable();
            $table->text('suivi')->nullable();
            $table->uuid('id_acteur')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_psycho')->references('id')->on('dossiers_psycho_sociaux')->onDelete('cascade');
            $table->foreign('id_acteur')->references('id')->on('acteurs_non_medicaux')->onDelete('set null');
            $table->index(['id_dossier_psycho', 'id_acteur'],'DosPsyAct_idx');
        });

        // Table: suivis_hospitaliers
        Schema::create('suivis_hospitaliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_dossier_admission');
            $table->text('observation_medicale')->nullable();
            $table->text('evolution_clinique')->nullable();
            $table->text('evenements_marquants')->nullable();
            $table->timestamps();
            $table->foreign('id_dossier_admission')->references('id')->on('dossiers_admission_sejour')->onDelete('cascade');
            $table->index(['id_dossier_admission']);
        });

        // Table: symptomes
        Schema::create('symptomes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_consultation');
            $table->string('nom');
            $table->timestamp('date')->nullable();
            $table->timestamps();
            $table->foreign('id_consultation')->references('id')->on('consultations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptomes');
        Schema::dropIfExists('suivis_hospitaliers');
        Schema::dropIfExists('evaluations_psychologiques');
        Schema::dropIfExists('dossiers_psycho_sociaux');
        Schema::dropIfExists('fiches_preoperatoires');
        Schema::dropIfExists('dossiers_chirurgie_anesthesie');
        Schema::dropIfExists('contacts_urgence');
        Schema::dropIfExists('maladies_chroniques');
        Schema::dropIfExists('traitements_longue_duree');
        Schema::dropIfExists('operations');
        Schema::dropIfExists('vaccinations');
        Schema::dropIfExists('effets_secondaires');
        Schema::dropIfExists('administrations_medicaments');
        Schema::dropIfExists('observations_infirmieres');
        Schema::dropIfExists('dossiers_soins_infirmiers');
        Schema::dropIfExists('images_dicom');
        Schema::dropIfExists('examens_imagerie');
        Schema::dropIfExists('dossiers_examens_complementaires');
        Schema::dropIfExists('migrations');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('comptes_rendus_sortie');
        Schema::dropIfExists('dossiers_sortie_suivi');
        Schema::dropIfExists('rendez_vous');
        Schema::dropIfExists('medicaments');
        Schema::dropIfExists('traitements');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('dossiers_soins_medicaux');
        Schema::dropIfExists('comptes_rendus_hospitalisation');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('dossiers_admission_sejour');
        Schema::dropIfExists('dossiers_administratifs');
        Schema::dropIfExists('consentements');
        Schema::dropIfExists('assurances');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('allergies');
        Schema::dropIfExists('antecedents');
        Schema::dropIfExists('dossiers_patients');
        Schema::dropIfExists('acteurs_non_medicaux');
        Schema::dropIfExists('professionnels_sante');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('services_hospitaliers');
        Schema::dropIfExists('etablissements');
        Schema::dropIfExists('users');
    }
};