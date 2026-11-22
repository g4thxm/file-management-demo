<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProjectTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $softDelete = true;


    protected $fillable = [
        'project_id',
        'task',
        'task_stage',
        'assigned_to',
        'start_date',
        'targeted_date',
        'estimated_hours',
        'user_id',
        'status',
    ];


    //softdelete

    protected $dates=['deleted_at'];


    public static function TaskSummaryReport($filters=array()){
        $task = (new self)->getTable();
        $project = (new ProjectCreate)->getTable();
        $user = (new User())->getTable();
        $assignuser = (new User())->getTable();


        $tasksummary =ProjectTask::select("$task.*","$project.project_name as project_name","assignusers.name as assign_user_name","$user.name as user_name")
            ->join($project,"$project.id","=","$task.project_id")
            ->join($user,"$user.id","=","$task.user_id")
            ->join($assignuser." as assignusers","assignusers.id","=","$task.assigned_to");

        // $activitysummary=$activitysummary->where('printed_status',1);

        if($filters['fromdate']) $tasksummary=$tasksummary->where("$task.created_at",">=",date('Y-m-d',strtotime($filters['fromdate']))." 00:00:00");
        if($filters['todate']) $tasksummary=$tasksummary->where("$task.created_at","<=",date('Y-m-d',strtotime($filters['todate']))." 23:59:59");
        if($filters['project_id']) $tasksummary=$tasksummary->where("$task.project_id","=",$filters['project_id']);
        if($filters['user_id']) $tasksummary=$tasksummary->where("$task.assigned_to","=",$filters['user_id']);
        if($filters['status']) $tasksummary=$tasksummary->where("$task.status","=",$filters['status']);

        return $tasksummary->paginate($filters['paginate']);
    }



    public static function getProjectTaskExcel($fromdate,$todate,$project_id,$user_id,$status){





        $tasksummary =DB::table('project_tasks')
            ->join('project_creates','project_tasks.project_id','=','project_creates.id')
            ->join('users','project_tasks.assigned_to','=','users.id')
            ->join('users'." as byuser",'project_tasks.user_id','=','byuser.id')
            ->select('project_tasks.*','project_creates.project_name','users.name','byuser.name as byuser_name');



        if ($fromdate) $tasksummary = $tasksummary->where("project_tasks.created_at", ">=", date('Y-m-d', strtotime($fromdate))." 00:00:00");
        if ($todate) $tasksummary = $tasksummary->where("project_tasks.created_at", "<=", date('Y-m-d', strtotime($todate))." 23:59:59");
        if ($project_id) $tasksummary = $tasksummary->where("project_tasks.project_id", "=", $project_id);
        if ($user_id) $tasksummary = $tasksummary->where("project_tasks.assigned_to", "=", $user_id);
        if ($status) $tasksummary = $tasksummary->where("project_tasks.status", "=", $status);

        $tasksummary = $tasksummary->get()->toArray();


        if(empty($tasksummary)){

            return redirect()->route('taskreport.index')->with('error','Nothing to download');
        }

        $php_array = json_encode($tasksummary);


        $php_array = json_decode($php_array,true);




        // echo "<pre>";print_r($php_array);




        $data = array();

        foreach ($php_array as $tasksummarys) {
            $d = array();


            $d['project_name'] = $tasksummarys['project_name'];
            $d['task_name'] = $tasksummarys['task'];
            $d['task_stage'] = $tasksummarys['task_stage'];
            $d['assigned_to'] = $tasksummarys['name'];
            $d['start_date'] = date('d-m-Y',strtotime($tasksummarys['start_date']));
            $d['targeted_date'] = date('d-m-Y',strtotime($tasksummarys['targeted_date']));

            $minutes=$tasksummarys['estimated_hours'];

            $hours = intdiv($minutes, 60).':'. ($minutes % 60);

            $d['estimated_hours'] = $hours;


             $d['user_id'] = $tasksummarys['byuser_name'];

             if($tasksummarys['status']==1)
                 $status='Pending';
             else if($tasksummarys['status']==2)
                 $status='In Progress';
             else
                 $status='Completed';

             $d['status'] = $status;



            $data[] = $d;
        }

        return $data;



    }



}
