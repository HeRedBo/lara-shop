<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->delete();
        for($i = 0; $i < 10; $i++ ){
            DB::table('categories')->insert(
                [
                    'name' => 'CAT'.$i,
//                    'description' => 'desc_'.$i,
                    'score' => $i,
                    'parent_id' => 0,
                    'is_show' => 1,
                ]
            );
        }
    }
}
