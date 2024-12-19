<?php

namespace App\Http\Controllers;

use Exception;
use DataTables;
use App\Models\Student;
use App\Traits\AjaxResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StudentRequest;

class StudentController extends Controller
{
    use AjaxResponse;
    
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $students =  Student::select('id', 'name')->get();

            return Datatables::of($students)
                ->addIndexColumn()   
                ->addColumn('action', function ($student) {
                    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $student->id . '" data-original-title="Edit" class="student-edit-btn btn btn-primary btn-sm">Edit</a>';
                    $btn .= ' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="' . $student->id . '" data-original-title="Delete" class="student-delete-btn btn btn-danger btn-sm ">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('student.index');
    }   

    public function store(StudentRequest $request)
    {
        DB::beginTransaction();
        $update = $request->has('student_edit_id') && $request->student_edit_id != "";

        try {
            if ($update) {
                $message = 'Student Data update sucessfully';
                $student  = Student::find($request->student_edit_id);
            } else {
                $message = 'Student Data saved sucessfully';
                $student = new Student();
            }
            $student->fill($request->validated());
            $student->save();
            DB::commit();
            return $this->ajaxResponse(true, $message);
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
            return $this->ajaxResponse(false, 'Internal Server Error');
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $student  = Student::find($request->studentId);

            if ($student) {
                $student->delete();
            } else {
                return $this->ajaxResponse(false, 'Internal Server Error');
            }
            DB::commit();

            return $this->ajaxResponse(true, 'Student Data delete successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->ajaxResponse(false, 'Internal Server Error');
        }
    }


    public function edit(Request $request)
    {
        $student  = Student::select('id', 'name', )->where('id', $request->studentId)->first();
         
        if($student)
        {
            $data = [
                 'student' => $student,   
            ];
            return $this->ajaxResponse(true, 'student Data', $data);
        }
        else
        {
            return $this->ajaxResponse(false, 'Internal Server Error');
        } 
    }
}
