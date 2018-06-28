<?php

use Illuminate\Database\Seeder;
use \App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

  //       DB::table('user_types')->insert([
		//     ['id' => '1', 'user_type' => 'Lab User'],
		//     ['id' => '2', 'user_type' => 'System Administrator'],
		//     ['id' => '3', 'user_type' => 'Program Officers'],
		//     ['id' => '4', 'user_type' => 'Data Clerk'],
		//     ['id' => '5', 'user_type' => 'Facility Users'],
		//     ['id' => '6', 'user_type' => 'Hub Data Uploaders'],
		//     ['id' => '7', 'user_type' => 'POC Admin'],
		// ]);

		// $old_users = DB::connection('old')->table('users')->get();

		// foreach ($old_users as $old_user) {
		// 	$user = new User;
		// 	$user->id = $old_user->ID;
		// 	$user->user_type_id = $old_user->account;
		// 	$user->lab_id = $old_user->lab;
		// 	$user->surname = $old_user->surname;
		// 	$user->oname = $old_user->oname;
		// 	$user->email = $old_user->email;

		// 	$existing = User::where('email', $old_user->email)->get()->first();
		// 	if($existing) $user->email = rand(1, 20) . $user->email;

		// 	$user->password = '12345678';
		// 	$user->save();
		// }



        $users = factory(App\User::class, 1)->create([
	        'user_type_id' => 1,
	        'surname' => 'Kithinji',
	        'oname' => 'Joel',
	        'email' => 'joelkith@gmail.com',
    	]);

        $users = factory(App\User::class, 1)->create([
	        'user_type_id' => 1,
	        'surname' => 'Bakasa',
	        'oname' => 'Joshua',
	        'email' => 'bakasa@gmail.com',
    	]);

        $users = factory(App\User::class, 1)->create([
	        'user_type_id' => 1,
	        'surname' => 'Ngugi',
	        'oname' => 'Tim',
	        'email' => 'tim@gmail.com',
    	]);

        $users = factory(App\User::class, 1)->create([
	        'user_type_id' => 1,
	        'surname' => 'Lusike',
	        'oname' => 'Judy',
	        'email' => 'judy@gmail.com',
    	]);

        $users = factory(App\User::class, 1)->create([
	        'user_type_id' => 2,
	        'surname' => 'Default',
	        'oname' => 'Admin',
	        'email' => 'admin@admin.com',
    	]);

    	$facilitys = DB::table('facilitys')->get();

    	$i=0;
    	$data= null;

    	foreach ($facilitys as $key => $facility) {
    		$fac = factory(App\User::class, 1)->create([
		        'user_type_id' => 8,
		        'surname' => '',
		        'oname' => '',
		        'facility_id' => $facility->id,
		        'email' => 'facility' . $facility->id . '@nascop-lab.com',
		        'password' => encrypt($facility->name)
	    	]);

	    	// if($key==100) break;

    		// $data[$i] = [
		    //     'user_type_id' => 5,
		    //     'surname' => '',
		    //     'oname' => '',
		    //     'facility_id' => $facility->id,
		    //     'email' => 'facility' . $facility->id . '@nascop-lab.com',
		    //     'password' => bcrypt(encrypt($facility->name)),
	    	// ];

	    	// if($i == 200){
	    	// 	DB::table('users')->insert($data);
	    	// 	$i=0;
	    	// 	$data = NULL;
	    	// }
    	}
    	// DB::table('users')->insert($data);

    }
}
