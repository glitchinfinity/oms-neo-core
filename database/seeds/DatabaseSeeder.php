<?php

use Illuminate\Database\Seeder;

use App\Models\SeederLog;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$seedersToRun = array(
    		'CountrySeeder',
    		'TypeAndFieldOfStudiesSeeder',
    		'ModuleSeeder',
    		'OptionsSeeder',
    		'EmailTemplateSeeder',
    		'AddSuperAdmin',
            'AddRecrutementModuleSeeder',
            'AddAnnouncementsRole'
    	);

    	$seeders = SeederLog::all();
    	$seedersArr = array();
    	foreach($seeders as $seeder) {
    		$seedersArr[] = $seeder->code;
    	}

    	$seededSomething = false;
    	foreach($seedersToRun as $seeder) {
    		if(in_array($seeder, $seedersArr)) {
    			continue;
    		}

    		eval('$this->call('.$seeder.'::class);');
    		SeederLog::create([
    			'code'	=>	$seeder
    		]);

    		$seededSomething = true;
    	}

    	if(!$seededSomething) {
    		echo "Nothing to seed!".PHP_EOL;
    	}
    }
}

class userSeeder extends Seeder {
	public function run() {
		User::create([
			'contact_email' 	=> 	'flaviu@glitch.ro',
			'first_name'		=>	'Flaviu',
			'last_name'			=>	'Porutiu',
			'date_of_birth'		=>	'1994-01-24',
			'gender'			=>	1,
			'antenna_id'		=>	$antenna->id,
			'university'		=>	'UBB Cluj',
			'studies_type_id'	=>	1,
			'studies_field_id'	=>	1,
			'password'			=>	Hash::make('1234'),
			'activated_at'		=>	date('Y-m-d H:i:S'),
			'is_superadmin'		=>	1
		]);
	}
}
