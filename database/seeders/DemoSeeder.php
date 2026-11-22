<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Invented local-demo records only. No company database is used.
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('DemoSeeder is restricted to local/testing environments.');
        }
        $password = env('DEMO_PASSWORD');
        if (!is_string($password) || strlen($password) < 12) {
            throw new \RuntimeException('Set a local DEMO_PASSWORD of at least 12 characters before seeding. No preset password is provided.');
        }
        if (DB::table('users')->count() || DB::table('companies')->count()) {
            throw new \RuntimeException('DemoSeeder requires an empty database; it will not overwrite existing records.');
        }
        DB::transaction(function () use ($password) {
            $now = now();
            $times = ['created_at' => $now, 'updated_at' => $now];
            DB::table('companies')->insert($times + ['id'=>1,'name'=>'Demo Organization','code'=>'DEMO']);
            DB::table('branches')->insert($times + ['id'=>1,'name'=>'Demo Office','company_id'=>1,'code'=>'DM01']);
            DB::table('departments')->insert($times + ['id'=>1,'branch_id'=>1,'name'=>'Demo Records','prefix'=>'DR','description'=>'Invented sample department']);
            foreach ([1=>'Demo Operator One', 2=>'Demo Operator Two'] as $id=>$name) {
                DB::table('employees')->insert($times + ['id'=>$id,'branch_id'=>1,'department_id'=>1,'name'=>$name,'designation'=>'Demo operator','email'=>'operator'.$id.'@example.invalid','description'=>'Invented local demo person']);
                DB::table('users')->insert($times + ['id'=>$id,'name'=>$name,'username'=>'demo'.$id,'email'=>'operator'.$id.'@example.invalid','password'=>Hash::make($password),'employee_id'=>$id,'branch_id'=>1,'user_type'=>1,'darkmode'=>0]);
            }
            DB::table('categories')->insert($times + ['id'=>1,'name'=>'Demo Records','description'=>'Invented category']);
            DB::table('sub_categories')->insert($times + ['id'=>1,'category_id'=>1,'name'=>'Demo General','description'=>'Invented subcategory']);
            DB::table('_setting')->insert($times + ['date_format'=>'d-m-Y','datetime_format'=>'H:i:s','label'=>'Demo','type'=>1]);
            DB::table('configurations')->insert($times + ['branch_id'=>1,'darkmode'=>0]);
            $groups = [1=>['Dashboard','dashboard','home'],2=>['Masters','company','database'],6=>['Transactions','filecreate','folder'],7=>['Reports','file_report','bar-chart'],5=>['Settings','config','settings']];
            foreach ($groups as $id=>$data) {
                DB::table('_menu')->insert($times + ['id'=>$id,'title'=>$data[0],'link'=>$data[1],'icon'=>$data[2],'listing_order'=>$id]);
            }
            $links = [
                [1,'Dashboard','dashboard'], [1,'Home','/'],
                [2,'Companies','company'],[2,'Branches','branch'],[2,'Departments','department'],[2,'Employees','employee'],[2,'Categories','category'],[2,'Subcategories','subcategory'],[2,'Users','usermaster'],
                [6,'File Creation','filecreate'],[6,'File Movement','filemovement'],[6,'File Closure','fileclosure'],[6,'File Reopen','filereopen'],
                [7,'File Report','file_report'],[7,'File History','filehistory'],
                [5,'Configuration','config'],[5,'Permissions','permission'],[5,'Profile','profile'],[5,'Change Password','changepassword'],[5,'Print Template','prnupload']
            ];
            foreach ($links as $i=>$data) {
                $id=$i+1;
                DB::table('_submenu')->insert($times + ['id'=>$id,'menu_id'=>$data[0],'title'=>$data[1],'link'=>$data[2],'listing_order'=>$id]);
                foreach ([1,2] as $uid) DB::table('permission')->insert($times + ['user_id'=>$uid,'submenu_id'=>$id]);
            }
        });
    }
}
