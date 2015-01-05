<?php

class DeviseSeeder extends \Illuminate\Database\Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		$this->call('DeviseLanguagesSeeder');
		$this->call('DevisePagesSeeder');
		$this->call('DeviseGroupsSeeder');

		if (App::environment() == 'local' || App::environment() == 'testing')
		{
			// For Development
			$this->call('DeviseMenusSeeder');
			$this->call('DeviseUsersSeeder');
			$this->call('DeviseGroupUserSeeder');
			$this->call('DeviseFieldsSeeder');
			$this->call('DeviseDevPagesSeeder');
			$this->call('DeviseCollectionInstancesSeeder');
			$this->call('DeviseCollectionSetsSeeder');
			$this->call('DeviseGlobalFieldsSeeder');
		}
	}
}