<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\BranchController;
use App\Http\Controllers\Master\EmployeeController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\Master\UsermasterController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\SubCategoryController;
use App\Http\Controllers\Reports\FileReportController;
use App\Http\Controllers\Transactions\FileMovementController;
use App\Http\Controllers\Transactions\FileController;
use App\Http\Controllers\Transactions\FileClosureController;
use App\Http\Controllers\Transactions\FileReopenController;
use App\Http\Controllers\Reports\FileHistoryReportController;


use App\Http\Controllers\Utility\ConfigurationController;


use App\Http\Controllers\Transactions\PhysicalVerificationCloseController;


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Utility\PrnUploadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ChangePasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], function () {

		Route::get('file-report/pdf/{file}', [FileReportController::class, 'viewPdf'])->name('file.report.view');
    Route::get('file-report/pdf/{file}/download', [FileReportController::class, 'downloadPdf'])->name('file.report.download');

	Route::group(['middleware' => ['isPermitted']], function () {

		 Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getData'])->name('dashboard.data');
		Route::get('/get-departments', [DashboardController::class, 'getDepartments']);
    Route::get('/get-departments/{branchId}', [DashboardController::class, 'getDepartments']);

		//Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home1');
		// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
		


		Route::resource('/profile', ProfileController::class);


		//company master
		Route::get('company/export', [CompanyController::class, 'export'])->name('company.export');
		Route::get('company/download-format', [CompanyController::class, 'downloadFormat'])->name('company.download-format');
		Route::post('company/import', [CompanyController::class, 'import'])->name('company.import');
		Route::resource('/company', CompanyController::class);

		

		//branch master
		Route::get('branch/export', [BranchController::class, 'export'])->name('branch.export');
		Route::get('branch/download-format', [BranchController::class, 'downloadFormat'])->name('branch.downloadFormat');
		Route::post('branch/import', [BranchController::class, 'import'])->name('branch.import');
		Route::resource('/branch', BranchController::class);
		// Route::resource('/branch', 'Master\BranchController');
		Route::post('/branch/search', 'App\Http\Controllers\Master\BranchController@search')->name('branch.search'); //search
		

		// Department master
		Route::post('department/import', [DepartmentController::class, 'import'])->name('department.import');
		Route::get('department/export', [DepartmentController::class, 'export'])->name('department.export');
		Route::get('department/download-format', [DepartmentController::class, 'downloadFormat'])->name('department.downloadFormat');
		Route::resource('/department', DepartmentController::class);

		// Employee master

		Route::get('departments/by-branch/{branch}',[EmployeeController::class, 'getDepartments'])->name('departments.by-branch');
		Route::get('employee/download-format',[EmployeeController::class, 'downloadFormat'])->name('employee.download-format');
		Route::post('employee/import',[EmployeeController::class, 'import'])->name('employee.import');
		Route::get('employee/export',[EmployeeController::class, 'export'])->name('employee.export');
		Route::resource('employee', EmployeeController::class);



		
		//Category Master
		Route::get('category/export', [CategoryController::class, 'export'])->name('category.export');
		Route::get('category/download-format', [CategoryController::class, 'downloadFormat'])->name('category.downloadFormat');
		Route::post('category/import', [CategoryController::class, 'import'])->name('category.import');		
		Route::resource('category', CategoryController::class);

		
		//Sub Category Master
		Route::get('subcategory/export', [SubCategoryController::class, 'export'])->name('subcategory.export');
		Route::get('subcategory/download-format', [SubCategoryController::class, 'downloadFormat'])->name('subcategory.downloadFormat');
		Route::post('subcategory/import', [SubCategoryController::class, 'import'])->name('subcategory.import');
		Route::resource('subcategory', SubCategoryController::class);

		//User master
		Route::post('usermaster/import', [UsermasterController::class, 'import'])	->name('usermaster.import');
		Route::get('usermaster/export', [UsermasterController::class, 'export'])->name('usermaster.export');
		Route::get('usermaster/download-format', [UsermasterController::class, 'downloadFormat'])->name('usermaster.downloadFormat');
		Route::resource('/usermaster', UsermasterController::class);

		//File Creation
    Route::get('/filecreate', [FileController::class, 'create']) ->name('filecreate');
		Route::post('/filecreate', [FileController::class, 'store'])->name('filestore');
		
		//Search file by RFID
		Route::get('file/search/{value}', [FileController::class, 'smartSearch'])->name('file.search.smart');


		//File Report
		Route::get('file_report', [FileReportController::class, 'form'])->name('file_report.form');
		Route::get('file_report/view', [FileReportController::class, 'view'])->name('file_report.view');
		Route::get('/pdf/view/{id}', [FileReportController::class, 'viewPdf'])->name('pdf.view');
    // Download single attached PDF file
    Route::get('/pdf/download/{id}', [FileReportController::class, 'downloadPdf'])->name('pdf.download');

		
		//File Movement
		Route::get('filemovement', [FileMovementController::class, 'index'])->name('filemovement.index');
		Route::post('filemovement/search', [FileMovementController::class, 'search'])->name('filemovement.search');
		Route::get('filemovement/{id}', [FileMovementController::class, 'fetch'])->name('filemovement.fetch');
		Route::post('filemovement/update', [FileMovementController::class, 'updateStatus'])->name('filemovement.update');
		Route::get('filemovement/departments/{branchId}', [FileMovementController::class, 'getDepartments'])->name('filemovement.departments');
		Route::get('filemovement/employees/{departmentId}', [FileMovementController::class, 'getEmployees'])->name('filemovement.employees');

		// File Closure
		Route::get('fileclosure', [FileClosureController::class, 'index'])->name('fileclosure.index');
		Route::get('fileclosure/{id}', [FileMovementController::class, 'fetch'])->name('fileclosure.fetch');
		// Route::post('fileclosure/search', [FileMovementController::class, 'searchByFileNumberForClosure'])->name('fileclosure.search');
		Route::post('file/close', [FileMovementController::class, 'closeFile'])->name('file.close');

		// File Reopen
		Route::get('filereopen', [FileReopenController::class, 'index'])->name('file.reopen.index');
		Route::get('filereopen/{file}', [FileReopenController::class, 'fetch'])->name('file.reopen.fetch');
		Route::post('filereopen/reopen', [FileReopenController::class, 'reopen'])->name('file.reopen.store');
		Route::get('employees/{department}', [FileReopenController::class, 'employees'])->name('filereopen.employees');

		// File History Report
		Route::get('filehistory', [FileHistoryReportController::class, 'index'])->name('report.file_history');
		Route::post('filehistory/view', [FileHistoryReportController::class, 'view'])->name('report.file_history.view');
		
		//Change Password
		Route::get('/changepassword', [ChangePasswordController::class, 'showChangeForm'])->name('changepassword.edit');
		Route::post('/changepassword', [ChangePasswordController::class, 'changePassword'])->name('changepassword.update');


		Route::resource('/log', LogController::class);


		Route::post('select-ajax', ['as' => 'select-ajax', 'uses' => 'HomeController@search']);

		//Physical Verification Close
		Route::resource('/physical_verification_close', PhysicalVerificationCloseController::class);

		
		/// Utility.....


		Route::resource('/prnupload', PrnUploadController::class);

		Route::resource('/config', ConfigurationController::class);


		Route::resource('permission', 'App\Http\Controllers\Utility\PermissionController');
	});




	// General Ajax requests
	Route::post('ajax/{method}', function ($method) {

		return App::call('\App\Http\Controllers\AjaxController@' . $method);
	});
});

Auth::routes();
Route::get('/', [HomeController::class, 'index'])->name('home');
	




