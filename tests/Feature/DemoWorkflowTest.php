<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\File;
use Database\Seeders\DemoSeeder;

class DemoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disposable test fixture only, never a shipped demo login.
        putenv('DEMO_PASSWORD=disposable-test-fixture-only');
        $_ENV['DEMO_PASSWORD'] = $_SERVER['DEMO_PASSWORD'] = 'disposable-test-fixture-only';
        $this->seed(DemoSeeder::class);
        Carbon::setTestNow('2026-11-22 10:00:00');
        $this->withoutExceptionHandling();
    }
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        putenv('DEMO_PASSWORD');
        unset($_ENV['DEMO_PASSWORD'], $_SERVER['DEMO_PASSWORD']);
        parent::tearDown();
    }
    private function createFile(array $extra = []): File
    {
        $this->actingAs(User::findOrFail(1))->post('/filecreate', array_merge([
            'rfid_code'=>'DEMO-RFID-0001','start_date'=>'2026-11-22','due_date'=>'2026-12-22',
            'official_file_number'=>'DEMO-0001','file_name'=>'Invented Test File','subject'=>'Synthetic workflow test',
            'priority'=>'normal','category_id'=>1,'sub_category_id'=>1,'branch_id'=>1,'department_id'=>1,'employee_id'=>1,
        ], $extra))->assertRedirect('/filecreate');
        return File::where('rfid_code','DEMO-RFID-0001')->firstOrFail();
    }
    public function test_login_and_main_screens_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('File Management System');
        $this->post('/login',['username'=>'demo1','password'=>'disposable-test-fixture-only'])->assertRedirect('/dashboard');
        foreach (['/','/dashboard','/company','/branch','/department','/employee','/category','/subcategory','/usermaster','/filecreate','/filemovement','/fileclosure','/filereopen','/file_report','/filehistory','/profile','/config','/permission','/changepassword','/prnupload'] as $path) {
            $this->get($path)->assertOk();
        }
    }
    public function test_empty_branding_has_no_broken_image_requests(): void
    {
        $response = $this->actingAs(User::findOrFail(1))->get('/config')->assertOk();
        $this->assertSame(4, substr_count($response->getContent(), 'Not configured'));
        $response->assertDontSee('src=\'http://localhost/dist/img\'', false);
    }
    public function test_file_lifecycle_and_reports(): void
    {
        $file=$this->createFile();
        $this->getJson('/filemovement/'.$file->id)->assertOk();
        foreach ([1,2,3] as $status) {
            Carbon::setTestNow(now()->addSecond());
            $this->postJson('/filemovement/update',['file_id'=>$file->id,'status'=>$status])->assertOk()->assertJson(['success'=>true]);
        }
        Carbon::setTestNow(now()->addSecond());
        $this->postJson('/filemovement/update',['file_id'=>$file->id,'status'=>4,'to_branch_id'=>1,'to_department_id'=>1,'to_user_id'=>2])->assertOk();
        $this->getJson('/filemovement/'.$file->id)->assertForbidden();
        $this->actingAs(User::findOrFail(2))->getJson('/filemovement/'.$file->id)->assertOk();
        Carbon::setTestNow(now()->addSecond());
        $this->postJson('/file/close',['file_id'=>$file->id,'remarks'=>'Synthetic closure'])->assertOk()->assertJson(['status'=>true]);
        $this->assertDatabaseHas('files',['id'=>$file->id,'status'=>1]);
        Carbon::setTestNow(now()->addSecond());
        $this->postJson('/filereopen/reopen',['file_id'=>$file->id,'to_branch_id'=>1,'to_department_id'=>1,'to_user_id'=>1,'remarks'=>'Synthetic reopen'])->assertOk()->assertJson(['status'=>true]);
        $this->assertDatabaseHas('files',['id'=>$file->id,'status'=>0]);
        $this->actingAs(User::findOrFail(1));
        $this->get('/file_report/view?from_date=2026-11-01&to_date=2026-12-31')->assertOk()->assertSee('DEMO-0001');
        $this->get('/file_report/view?from_date=2026-11-01&to_date=2026-12-31&action=csv')->assertOk()->assertHeader('Content-Type','text/csv; charset=UTF-8');
        $pdf=$this->get('/file_report/view?from_date=2026-11-01&to_date=2026-12-31&action=pdf')->assertOk()->assertHeader('Content-Type','application/pdf');
        if ($path=getenv('DEMO_TEST_PDF_PATH')) file_put_contents($path,$pdf->getContent());
        $this->post('/filehistory/view',['file_id'=>$file->id])->assertOk()->assertSee('DEMO-0001');
    }
    public function test_rapid_transfer_selects_latest_movement(): void
    {
        $file=$this->createFile();
        $this->postJson('/filemovement/update',['file_id'=>$file->id,'status'=>4,'to_branch_id'=>1,'to_department_id'=>1,'to_user_id'=>2])->assertOk();
        $this->getJson('/filemovement/'.$file->id)->assertForbidden();
        $this->actingAs(User::findOrFail(2))->getJson('/filemovement/'.$file->id)->assertOk();
        $this->postJson('/filemovement/search',['file_number'=>'DEMO-0001'])->assertOk()->assertJson(['file_id'=>$file->id]);
    }
    public function test_synthetic_attachment_and_label_template_path(): void
    {
        $bytes="%PDF-1.4\n% Synthetic attachment fixture only\n%%EOF";
        $upload=\Illuminate\Http\UploadedFile::fake()->createWithContent('invented.pdf',$bytes);
        $file=$this->createFile(['upload_file'=>$upload,'use_text_prn'=>'1']);
        $this->assertNotNull($file->upload_file);
        $path=storage_path('app/public/'.$file->upload_file);
        try {
            $this->assertFileExists($path);
            $this->assertSame($bytes,file_get_contents($path));
            $this->get('/file-report/pdf/'.$file->id)->assertOk()->assertHeader('Content-Type','application/pdf');
            $this->get('/file-report/pdf/'.$file->id.'/download')->assertOk();
            $this->assertStringContainsString('DEMO-0001',file_get_contents(public_path('prn/file_DEMO-0001.prn')));
        } finally {
            @unlink($path);
            @unlink(public_path('prn/file_DEMO-0001.prn'));
        }
    }

}
