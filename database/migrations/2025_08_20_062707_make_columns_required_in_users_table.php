use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('office')->nullable(false)->change();
            $table->string('region')->nullable(false)->change();
            $table->string('province')->nullable(false)->change();
            $table->string('municipality')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('office')->nullable()->change();
            $table->string('region')->nullable()->change();
            $table->string('province')->nullable()->change();
            $table->string('municipality')->nullable()->change();
        });
    }
};
