
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTokenableToUuidInPersonalAccessTokens extends Migration
{
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Supprimer les anciennes colonnes tokenable_id (bigint) et tokenable_type
            $table->dropColumn(['tokenable_id', 'tokenable_type']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Ajouter les colonnes UUID compatibles avec morphs
            $table->uuidMorphs('tokenable'); // Crée tokenable_id (UUID) + tokenable_type
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['tokenable_id', 'tokenable_type']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Restaurer l’ancienne version (au cas où tu veux revenir)
            $table->morphs('tokenable'); // tokenable_id (bigint) + tokenable_type (string)
        });
    }
}
